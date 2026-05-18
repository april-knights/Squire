<?php

namespace App\Http\Controllers;

use App\Model\Oath;
use App\Model\Setting;
use App\Services\RedditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OathController extends Controller
{
    protected RedditService $reddit;

    public function __construct(RedditService $reddit)
    {
        $this->middleware('auth');
        $this->middleware('admin')->only(['adminIndex', 'adminVerify', 'adminUnverify']);
        $this->reddit = $reddit;
    }

    // -------------------------------------------------------------------------
    // Submit Oath
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $request->validate([
            'comment_url' => 'required|url|max:500',
        ]);

        $knight = auth()->user();

        $oathYear = Oath::currentOathYear();

        // Prevent duplicate oath for the same year
        $existing = Oath::where('fkeyknight', $knight->pkey)
            ->where('oath_year', $oathYear)
            ->first();

        if ($existing) {
            return back()->with('info', 'You have already submitted an oath for ' . $oathYear . '.');
        }

        $oathPostId = Setting::get('oath_post_id');

        if (! $oathPostId) {
            return back()->with('error', 'The oath thread has not been configured yet. Please contact an Admin.');
        }

        // Extract comment ID from URL before saving
        $commentId = $this->extractCommentId($request->comment_url);

        // Create oath record — unverified initially
        $oath = Oath::create([
            'fkeyknight'       => $knight->pkey,
            'oath_year'        => $oathYear,
            'comment_url'      => $request->comment_url,
            'reddit_comment_id'=> $commentId,
            'verified'         => false,
            'verified_at'      => null,
            'crtsetid'         => $knight->pkey,
            'lstmdby'          => $knight->pkey,
        ]);

        // Attempt verification immediately
        $result = $this->reddit->verifyOathComment(
            $request->comment_url,
            $knight->rname,
            $oathPostId
        );

        if ($result['valid']) {
            $oath->update([
                'verified'    => true,
                'verified_at' => now(),
                'lstmdby'     => $knight->pkey,
            ]);

            return back()->with('success', 'Your oath has been recorded and verified. Thank you, ' . $knight->kname . '.');
        }

        // Saved but not verified — surface the reason
        return back()->with('warning',
            'Your oath URL was saved but could not be verified: ' . $result['reason'] .
            ' You can re-submit once the issue is resolved.'
        );
    }

    // -------------------------------------------------------------------------
    // Re-verify an existing unverified oath
    // -------------------------------------------------------------------------

    public function reverify(Request $request)
    {
        $knight   = auth()->user();
        $oathYear = Oath::currentOathYear();

        $oath = Oath::where('fkeyknight', $knight->pkey)
            ->where('oath_year', $oathYear)
            ->first();

        if (! $oath) {
            return back()->with('error', 'No oath found for the current year. Please submit one first.');
        }

        if ($oath->verified) {
            return back()->with('info', 'Your oath is already verified.');
        }

        $oathPostId = Setting::get('oath_post_id');

        if (! $oathPostId) {
            return back()->with('error', 'The oath thread has not been configured yet. Please contact an Admin.');
        }

        $result = $this->reddit->verifyOathComment(
            $oath->comment_url,
            $knight->rname,
            $oathPostId
        );

        if ($result['valid']) {
            $oath->update([
                'verified'    => true,
                'verified_at' => now(),
                'lstmdby'     => $knight->pkey,
            ]);

            return back()->with('success', 'Your oath has been verified successfully.');
        }

        return back()->with('warning', 'Verification failed: ' . $result['reason']);
    }

    // -------------------------------------------------------------------------
    // Update oath URL (unverified oaths only)
    // -------------------------------------------------------------------------

    public function update(Request $request)
    {
        $request->validate([
            'comment_url' => 'required|url|max:500',
        ]);

        $knight   = auth()->user();
        $oathYear = Oath::currentOathYear();

        $oath = Oath::where('fkeyknight', $knight->pkey)
            ->where('oath_year', $oathYear)
            ->first();

        if (! $oath) {
            return back()->with('error', 'No oath found for the current year. Please submit one first.');
        }

        if ($oath->verified) {
            return back()->with('error', 'Your oath is already verified and cannot be changed.');
        }

        $oathPostId = Setting::get('oath_post_id');

        if (! $oathPostId) {
            return back()->with('error', 'The oath thread has not been configured yet. Please contact an Admin.');
        }

        $commentId = $this->extractCommentId($request->comment_url);

        $oath->update([
            'comment_url'       => $request->comment_url,
            'reddit_comment_id' => $commentId,
            'verified'          => false,
            'verified_at'       => null,
            'lstmdby'           => $knight->pkey,
        ]);

        // Attempt verification with new URL
        $result = $this->reddit->verifyOathComment(
            $request->comment_url,
            $knight->rname,
            $oathPostId
        );

        if ($result['valid']) {
            $oath->update([
                'verified'    => true,
                'verified_at' => now(),
                'lstmdby'     => $knight->pkey,
            ]);

            return back()->with('success', 'Oath URL updated and verified successfully.');
        }

        return back()->with('warning',
            'Oath URL updated but could not be verified: ' . $result['reason']
        );
    }

    // -------------------------------------------------------------------------
    // Admin — view all oaths for current year
    // -------------------------------------------------------------------------

    public function adminIndex()
    {
        $oathYear = Oath::currentOathYear();

        $oaths = Oath::where('oath_year', $oathYear)
            ->with('knight')
            ->orderBy('verified', 'desc')
            ->orderBy('crtsetdt', 'asc')
            ->get();

        $oathThreadUrl = Setting::get('oath_thread_url');

        return view('admin.oaths.index', compact('oaths', 'oathYear', 'oathThreadUrl'));
    }

    // -------------------------------------------------------------------------
    // Admin — manually verify or unverify an oath
    // -------------------------------------------------------------------------

    public function adminVerify(Request $request, int $pkey)
    {
        $oath = Oath::findOrFail($pkey);

        $oath->update([
            'verified'    => true,
            'verified_at' => now(),
            'lstmdby'     => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Oath manually verified.');
    }

    public function adminUnverify(Request $request, int $pkey)
    {
        $oath = Oath::findOrFail($pkey);

        $oath->update([
            'verified'    => false,
            'verified_at' => null,
            'lstmdby'     => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Oath verification removed.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function extractCommentId(string $url): ?string
    {
        $pattern = '/\/comments\/[^\/]+\/[^\/]+\/([a-z0-9]+)/i';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }
}