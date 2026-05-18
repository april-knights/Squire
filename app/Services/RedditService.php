<?php

namespace App\Services;

use App\Model\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedditService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $userAgent;
    protected string $subreddit;

    public function __construct()
    {
        $this->clientId     = config('services.reddit.client_id');
        $this->clientSecret = config('services.reddit.client_secret');
        $this->userAgent    = config('services.reddit.user_agent', 'Squire/2.0 by AKSquire2');
        $this->subreddit    = config('services.reddit.subreddit', 'AprilKnights');
    }

    // -------------------------------------------------------------------------
    // Token Management
    // -------------------------------------------------------------------------

    /**
     * Get a valid access token, refreshing if necessary.
     */
    public function getAccessToken(): ?string
    {
        $expiresAt = Setting::get('reddit_token_expires_at');

        if ($expiresAt && now()->lt($expiresAt)) {
            return Setting::get('reddit_access_token');
        }

        return $this->refreshAccessToken();
    }

    /**
     * Use the stored refresh token to get a new access token.
     */
    public function refreshAccessToken(): ?string
    {
        $refreshToken = Setting::get('reddit_refresh_token');

        if (! $refreshToken) {
            Log::error('RedditService: No refresh token stored. Re-authorization required.');
            return null;
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->withHeaders(['User-Agent' => $this->userAgent])
            ->asForm()
            ->post('https://www.reddit.com/api/v1/access_token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if (! $response->successful()) {
            Log::error('RedditService: Token refresh failed.', ['response' => $response->body()]);
            return null;
        }

        $data      = $response->json();
        $expiresAt = now()->addSeconds($data['expires_in'] - 60)->toDateTimeString();

        Setting::set('reddit_access_token', $data['access_token']);
        Setting::set('reddit_token_expires_at', $expiresAt);

        // Reddit only issues a new refresh token if the old one is expiring
        if (! empty($data['refresh_token'])) {
            Setting::set('reddit_refresh_token', $data['refresh_token']);
        }

        return $data['access_token'];
    }

    /**
     * Store tokens after the initial OAuth authorization flow.
     */
    public function storeTokens(string $code, string $redirectUri): bool
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->withHeaders(['User-Agent' => $this->userAgent])
            ->asForm()
            ->post('https://www.reddit.com/api/v1/access_token', [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $redirectUri,
            ]);

        if (! $response->successful()) {
            Log::error('RedditService: Initial token exchange failed.', ['response' => $response->body()]);
            return false;
        }

        $data      = $response->json();
        $expiresAt = now()->addSeconds($data['expires_in'] - 60)->toDateTimeString();

        Setting::set('reddit_access_token', $data['access_token']);
        Setting::set('reddit_refresh_token', $data['refresh_token']);
        Setting::set('reddit_token_expires_at', $expiresAt);

        return true;
    }

    // -------------------------------------------------------------------------
    // Posting
    // -------------------------------------------------------------------------

    /**
     * Submit a self (text) post to the subreddit.
     * Returns ['post_id' => string, 'url' => string] or null on failure.
     */
    public function submitPost(string $title, string $body): ?array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => $this->userAgent])
            ->asForm()
            ->post('https://oauth.reddit.com/api/submit', [
                'sr'       => $this->subreddit,
                'kind'     => 'self',
                'title'    => $title,
                'text'     => $body,
                'nsfw'     => false,
                'spoiler'  => false,
                'resubmit' => true,
            ]);

        if (! $response->successful()) {
            Log::error('RedditService: Post submission failed.', ['response' => $response->body()]);
            return null;
        }

        $data = $response->json();

        $postId = $data['json']['data']['id']  ?? null;
        $url    = $data['json']['data']['url'] ?? null;

        if (! $postId) {
            Log::error('RedditService: Post submission returned no post ID.', ['response' => $data]);
            return null;
        }

        return ['post_id' => $postId, 'url' => $url];
    }

    /**
     * Pin (sticky) a post by its post ID.
     * Slot 1 = top sticky, slot 2 = second sticky.
     */
    public function stickyPost(string $postId, int $slot = 2): bool
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)
            ->withHeaders(['User-Agent' => $this->userAgent])
            ->asForm()
            ->post('https://oauth.reddit.com/api/set_subreddit_sticky', [
                'id'     => 't3_' . $postId,
                'state'  => true,
                'num'    => $slot,
            ]);

        if (! $response->successful()) {
            Log::error('RedditService: Sticky failed for post ' . $postId, ['response' => $response->body()]);
            return false;
        }

        return true;
    }

    /**
     * Submit a post and attempt to sticky it.
     * Returns post data regardless — sticky failure is non-fatal,
     * surfaced as a warning in the EA dashboard.
     */
    public function submitAndSticky(string $title, string $body, int $slot = 2): array
    {
        $post = $this->submitPost($title, $body);

        if (! $post) {
            return ['success' => false, 'post' => null, 'sticky' => false, 'error' => 'Post submission failed.'];
        }

        $sticky = $this->stickyPost($post['post_id'], $slot);

        return [
            'success' => true,
            'post'    => $post,
            'sticky'  => $sticky,
            'error'   => $sticky ? null : 'Post submitted but could not be pinned. Pin it manually.',
        ];
    }

    // -------------------------------------------------------------------------
    // Oath Comment Verification
    // -------------------------------------------------------------------------

    /**
     * Verify that a Reddit comment URL:
     * - Exists
     * - Is on the correct oath post
     * - Was authored by the knight's registered Reddit username
     *
     * Returns ['valid' => bool, 'reason' => string|null]
     */
    public function verifyOathComment(string $commentUrl, string $expectedAuthor, string $oathPostId): array
    {
        $token = $this->getAccessToken();

        // Extract comment ID from URL
        // URL format: https://www.reddit.com/r/sub/comments/{post_id}/title/{comment_id}/
        $commentId = $this->extractCommentId($commentUrl);

        if (! $commentId) {
            return ['valid' => false, 'reason' => 'Could not parse comment ID from URL.'];
        }

        $headers = ['User-Agent' => $this->userAgent];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = Http::withHeaders($headers)
            ->get('https://oauth.reddit.com/api/info', [
                'id' => 't1_' . $commentId,
            ]);

        if (! $response->successful()) {
            Log::error('RedditService: Comment verification request failed.', ['response' => $response->body()]);
            return ['valid' => false, 'reason' => 'Reddit API request failed. Try again later.'];
        }

        $data     = $response->json();
        $children = $data['data']['children'] ?? [];

        if (empty($children)) {
            return ['valid' => false, 'reason' => 'Comment not found on Reddit.'];
        }

        $comment = $children[0]['data'] ?? [];

        // Verify author
        $author = $comment['author'] ?? null;
        if (strtolower($author) !== strtolower($expectedAuthor)) {
            return [
                'valid'  => false,
                'reason' => 'Comment author (' . $author . ') does not match your registered Reddit username.',
            ];
        }

        // Verify it's on the correct post
        $linkId         = $comment['link_id'] ?? null; // format: t3_postid
        $expectedLinkId = 't3_' . $oathPostId;

        if ($linkId !== $expectedLinkId) {
            return [
                'valid'  => false,
                'reason' => 'Comment is not on the current oath thread.',
            ];
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Extract the comment ID from a Reddit comment URL.
     */
    protected function extractCommentId(string $url): ?string
    {
        // https://www.reddit.com/r/sub/comments/postid/title/commentid/
        $pattern = '/\/comments\/[^\/]+\/[^\/]+\/([a-z0-9]+)/i';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    // -------------------------------------------------------------------------
    // Status Check
    // -------------------------------------------------------------------------

    /**
     * Check whether AKSquire2 is currently authorized.
     * Returns true if a valid token exists or can be refreshed.
     */
    public function isAuthorized(): bool
    {
        return $this->getAccessToken() !== null;
    }
}