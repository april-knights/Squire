<?php

namespace App\Http\Controllers;

use App\Model\Knight;
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
        $this->middleware('admin')->only([
            'adminIndex',
            'adminVerify',
            'adminUnverify',
            'adminBatchVerify',
        ]);
        $this->reddit = $reddit;
    }

    // -------------------------------------------------------------------------
    // Knight — verify own oath (scan thread, no URL needed)
    // -------------------------------------------------------------------------

    public function verify(Request $request)
    {
        $knight   = auth()->user();
        $oathYear = Oath::currentOathYear();

        // Check if already verified
        $existing = Oath::where('fkeyknight', $knight->pkey)
            ->where('oath_year', $oathYear)
            ->first();

        if ($existing && $existing->verified) {
            return back()->with('info', 'Your oath is already verified for ' . $oathYear . '.');
        }

        if (! Setting::get('oath_thread_url')) {
            return back()->with('error', 'The oath thread has not been configured yet. Contact an Admin.');
        }

        // Scan the thread for this knight's comment
        $result = $this->reddit->findOathComment($knight->rname);

        if ($result['valid']) {
            // Upsert — create or update existing unverified record
            Oath::updateOrCreate(
                [
                    'fkeyknight' => $knight->pkey,
                    'oath_year'  => $oathYear,
                ],
                [
                    'comment_url'       => $result['comment_url'],
                    'reddit_comment_id' => $result['comment_id'],
                    'verified'          => true,
                    'verified_at'       => now(),
                    'crtsetid'          => $knight->pkey,
                    'lstmdby'           => $knight->pkey,
                ]
            );

            return back()->with('success', 'Your oath has been verified. Thank you, ' . $knight->kname . '!');
        }

        // Not found — create unverified record so we know they tried
        if (! $existing) {
            Oath::create([
                'fkeyknight'       => $knight->pkey,
                'oath_year'        => $oathYear,
                'comment_url'      => '',
                'reddit_comment_id'=> null,
                'verified'         => false,
                'verified_at'      => null,
                'crtsetid'         => $knight->pkey,
                'lstmdby'          => $knight->pkey,
            ]);
        }

        return back()->with('warning',
            $result['reason'] . ' '
            . '<a href="' . Setting::get('oath_thread_url') . '" target="_blank">View the oath thread</a>.'
        );
    }

    // -------------------------------------------------------------------------
    // Admin — view all oaths for current year
    // -------------------------------------------------------------------------

    public function adminIndex()
    {
        $oathYear      = Oath::currentOathYear();
        $oathThreadUrl = Setting::get('oath_thread_url');

        $oaths = Oath::where('oath_year', $oathYear)
            ->with('knight')
            ->orderBy('verified', 'desc')
            ->orderBy('crtsetdt', 'asc')
            ->get();

        return view('admin.oaths.index', compact('oaths', 'oathYear', 'oathThreadUrl'));
    }

    // -------------------------------------------------------------------------
    // Admin — batch verify all knights against oath thread
    // -------------------------------------------------------------------------

    public function adminBatchVerify(Request $request)
    {
        $oathYear = Oath::currentOathYear();

        if (! Setting::get('oath_thread_url')) {
            return back()->with('error', 'Oath thread URL not configured.');
        }

        // Fetch all commenters from the thread once
        $commenters = $this->reddit->getAllOathCommenters();

        if ($commenters === null) {
            return back()->with('error', 'Failed to fetch oath thread from Reddit. Try again later.');
        }

        // Build lookup: lowercase rname => commenter data
        $commenterMap = [];
        foreach ($commenters as $c) {
            $commenterMap[strtolower($c['author'])] = $c;
        }

        // Get all active knights
        $knights = Knight::withoutGlobalScopes()
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->get(['pkey', 'kname', 'rname']);

        $verified   = 0;
        $alreadyOk  = 0;
        $notFound   = 0;
        $noSquire   = [];

        foreach ($knights as $knight) {
            $commenter = $commenterMap[strtolower($knight->rname)] ?? null;

            $existing = Oath::where('fkeyknight', $knight->pkey)
                ->where('oath_year', $oathYear)
                ->first();

            if ($existing && $existing->verified) {
                $alreadyOk++;
                continue;
            }

            if ($commenter) {
                Oath::updateOrCreate(
                    [
                        'fkeyknight' => $knight->pkey,
                        'oath_year'  => $oathYear,
                    ],
                    [
                        'comment_url'       => $commenter['comment_url'],
                        'reddit_comment_id' => $commenter['comment_id'],
                        'verified'          => true,
                        'verified_at'       => now(),
                        'crtsetid'          => 1,
                        'lstmdby'           => auth()->user()->pkey,
                    ]
                );
                $verified++;
            } else {
                $notFound++;
            }
        }

        // Find commenters with no Squire account
        $knightRnames = $knights->map(fn($k) => strtolower($k->rname))->toArray();
        foreach ($commenterMap as $rname => $commenter) {
            if (! in_array($rname, $knightRnames)) {
                $noSquire[] = $commenter['author'];
            }
        }

        return back()->with('batch_results', [
            'verified'  => $verified,
            'alreadyOk' => $alreadyOk,
            'notFound'  => $notFound,
            'noSquire'  => $noSquire,
        ]);
    }

    // -------------------------------------------------------------------------
    // Admin — manually verify or unverify
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
}