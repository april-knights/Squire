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
    // Oath Thread Scanning
    // -------------------------------------------------------------------------

    /**
     * Scan the oath thread for a comment by a specific author.
     * Returns ['valid' => bool, 'comment_url' => string|null, 'reason' => string|null]
     */
    public function findOathComment(string $rname): array
    {
        $oathThreadUrl = Setting::get('oath_thread_url');

        if (! $oathThreadUrl) {
            return ['valid' => false, 'comment_url' => null, 'reason' => 'Oath thread not configured. Contact an Admin.'];
        }

        $postId = $this->extractPostId($oathThreadUrl);

        if (! $postId) {
            return ['valid' => false, 'comment_url' => null, 'reason' => 'Could not parse oath thread URL. Contact an Admin.'];
        }

        $comments = $this->fetchAllComments($postId);

        if ($comments === null) {
            return ['valid' => false, 'comment_url' => null, 'reason' => 'Reddit API request failed. Try again later.'];
        }

        foreach ($comments as $comment) {
            if (strtolower($comment['author'] ?? '') === strtolower($rname)) {
                // Build comment URL from post ID and comment ID
                $commentUrl = 'https://www.reddit.com/r/' . $this->subreddit
                    . '/comments/' . $postId
                    . '/_/' . $comment['id'] . '/';

                return [
                    'valid'       => true,
                    'comment_url' => $commentUrl,
                    'comment_id'  => $comment['id'],
                    'reason'      => null,
                ];
            }
        }

        return [
            'valid'       => false,
            'comment_url' => null,
            'reason'      => 'No comment found on the oath thread for /u/' . $rname . '. Make sure you have commented on the thread.',
        ];
    }

    /**
     * Fetch all top-level comments from a Reddit post, handling pagination.
     * Returns flat array of comment data arrays, or null on API failure.
     */
    protected function fetchAllComments(string $postId): ?array
    {
        $token   = $this->getAccessToken();
        $headers = ['User-Agent' => $this->userAgent];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $baseUrl  = 'https://oauth.reddit.com/r/' . $this->subreddit . '/comments/' . $postId;
        $comments = [];
        $after    = null;

        // Reddit comment trees can have 'more' objects — we follow them
        // For oath threads (flat top-level replies) one request is almost always enough
        do {
            $params = ['limit' => 500, 'depth' => 1];
            if ($after) {
                $params['after'] = $after;
            }

            $response = Http::withHeaders($headers)
                ->get($baseUrl, $params);

            if (! $response->successful()) {
                Log::error('RedditService: Failed to fetch oath thread comments.', [
                    'post_id'  => $postId,
                    'response' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            // Reddit returns [post_listing, comments_listing]
            $commentListing = $data[1]['data']['children'] ?? [];

            foreach ($commentListing as $child) {
                if (($child['kind'] ?? '') === 't1') {
                    $comments[] = $child['data'];
                }
            }

            // Check for 'more' object for pagination
            $last = end($commentListing);
            if ($last && ($last['kind'] ?? '') === 'more' && ! empty($last['data']['children'])) {
                // For oath threads we won't typically hit this
                // If we do, fetch remaining via /api/morechildren
                $moreIds  = array_slice($last['data']['children'], 0, 100);
                $moreData = $this->fetchMoreComments($postId, $moreIds, $token);
                if ($moreData) {
                    $comments = array_merge($comments, $moreData);
                }
                $after = null; // stop pagination loop — morechildren handled above
            } else {
                $after = null;
            }

        } while ($after);

        return $comments;
    }

    /**
     * Fetch additional comments via /api/morechildren for paginated threads.
     */
    protected function fetchMoreComments(string $postId, array $childIds, ?string $token): array
    {
        $headers = ['User-Agent' => $this->userAgent];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = Http::withHeaders($headers)
            ->get('https://oauth.reddit.com/api/morechildren', [
                'link_id'  => 't3_' . $postId,
                'children' => implode(',', $childIds),
                'api_type' => 'json',
            ]);

        if (! $response->successful()) {
            return [];
        }

        $things = $response->json()['json']['data']['things'] ?? [];
        $result = [];

        foreach ($things as $thing) {
            if (($thing['kind'] ?? '') === 't1') {
                $result[] = $thing['data'];
            }
        }

        return $result;
    }

    /**
     * Scan the entire oath thread and return all comment authors.
     * Used for batch verification and non-Squire commenter report.
     * Returns array of ['author' => string, 'comment_id' => string, 'comment_url' => string]
     */
    public function getAllOathCommenters(): ?array
    {
        $oathThreadUrl = Setting::get('oath_thread_url');

        if (! $oathThreadUrl) {
            return null;
        }

        $postId = $this->extractPostId($oathThreadUrl);

        if (! $postId) {
            return null;
        }

        $comments = $this->fetchAllComments($postId);

        if ($comments === null) {
            return null;
        }

        $result = [];
        foreach ($comments as $comment) {
            $author = $comment['author'] ?? null;
            if (! $author || $author === '[deleted]' || $author === 'AutoModerator') {
                continue;
            }
            $result[] = [
                'author'      => $author,
                'comment_id'  => $comment['id'],
                'comment_url' => 'https://www.reddit.com/r/' . $this->subreddit
                    . '/comments/' . $postId
                    . '/_/' . $comment['id'] . '/',
            ];
        }

        return $result;
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
    protected function extractPostId(string $url): ?string
    {
        preg_match('/\/comments\/([a-z0-9]+)/i', $url, $matches);
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