<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionCandidate;
use App\Model\ElectionNomination;
use App\Model\ElectionRegistration;
use App\Model\ElectionVote;
use App\Model\ElectionPhaseLog;
use App\Model\Knight;
use App\Model\Setting;
use App\Services\RedditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    protected RedditService $reddit;

    public function __construct(RedditService $reddit)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->reddit = $reddit;
    }

    // -------------------------------------------------------------------------
    // Election CRUD
    // -------------------------------------------------------------------------

    public function index()
    {
        $elections = Election::withoutGlobalScopes()
            ->orderBy('election_year', 'desc')
            ->get();

        return view('admin.elections.index', compact('elections'));
    }

    public function create()
    {
        // Prevent creating a new election if one is already active
        $active = Election::active();

        if ($active) {
            return redirect()
                ->route('admin.elections.index')
                ->with('error', 'An active election already exists for ' . $active->election_year . '. Complete it before creating a new one.');
        }

        return view('admin.elections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'election_year' => 'required|digits:4|integer|min:2024',
            'notes'         => 'nullable|string',
        ]);

        $active = Election::active();
        if ($active) {
            return back()->with('error', 'An active election already exists.');
        }

        $election = Election::create([
            'election_year' => $request->election_year,
            'phase'         => 'setup',
            'notes'         => $request->notes,
            'crtsetid'      => auth()->user()->pkey,
            'lstmdby'       => auth()->user()->pkey,
        ]);

        ElectionPhaseLog::create([
            'fkeyelection'    => $election->pkey,
            'from_phase'      => null,
            'to_phase'        => 'setup',
            'transitioned_by' => auth()->user()->pkey,
            'note'            => 'Election created.',
        ]);

        return redirect()
            ->route('admin.elections.show', $election->pkey)
            ->with('success', 'Election created.');
    }

    public function show(int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $administrator = ElectionAdministrator::where('fkeyelection', $pkey)
            ->where('is_assistant', 0)
            ->with('knight')
            ->first();

        $assistant = ElectionAdministrator::where('fkeyelection', $pkey)
            ->where('is_assistant', 1)
            ->with('knight')
            ->first();

        $candidates = ElectionCandidate::where('fkeyelection', $pkey)
            ->with(['knight', 'nominations.knight'])
            ->get();

        $registrations = ElectionRegistration::where('fkeyelection', $pkey)
            ->with('knight')
            ->get();

        $phaseLog = ElectionPhaseLog::where('fkeyelection', $pkey)
            ->orderBy('crtsetdt', 'asc')
            ->get();

        $voteCount = ElectionVote::where('fkeyelection', $pkey)
            ->where('valid', 1)
            ->count();

        $eligibleKnights = $this->getEligibleKnights();

        return view('admin.elections.show', compact(
            'election',
            'administrator',
            'assistant',
            'candidates',
            'registrations',
            'phaseLog',
            'voteCount',
            'eligibleKnights'
        ));
    }

    public function update(Request $request, int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $election->update([
            'notes'   => $request->notes,
            'lstmdby' => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Election notes updated.');
    }

    // -------------------------------------------------------------------------
    // Election Administrator Management
    // -------------------------------------------------------------------------

    public function appointAdministrator(Request $request, int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $request->validate([
            'fkeyknight'   => 'required|integer|exists:knight,pkey',
            'is_assistant' => 'sometimes|boolean',
        ]);

        $isAssistant = $request->boolean('is_assistant', false);

        // EA cannot be a candidate
        $isCandidate = ElectionCandidate::where('fkeyelection', $pkey)
            ->where('fkeyknight', $request->fkeyknight)
            ->where('status', 'accepted')
            ->exists();

        if ($isCandidate) {
            return back()->with('error', 'An accepted candidate cannot be appointed as Election Administrator.');
        }

        // Only one full EA and one assistant EA per election
        $existing = ElectionAdministrator::where('fkeyelection', $pkey)
            ->where('is_assistant', $isAssistant)
            ->first();

        if ($existing) {
            $role = $isAssistant ? 'Assistant EA' : 'Election Administrator';
            return back()->with('error', 'A ' . $role . ' is already appointed for this election. Remove them first.');
        }

        ElectionAdministrator::create([
            'fkeyelection' => $pkey,
            'fkeyknight'   => $request->fkeyknight,
            'is_assistant' => $isAssistant,
            'appointed_by' => auth()->user()->pkey,
            'appointed_at' => now(),
            'crtsetid'     => auth()->user()->pkey,
            'lstmdby'      => auth()->user()->pkey,
        ]);

        $role = $isAssistant ? 'Assistant EA' : 'Election Administrator';
        return back()->with('success', $role . ' appointed successfully.');
    }

    public function removeAdministrator(Request $request, int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $request->validate([
            'ea_pkey' => 'required|integer',
        ]);

        $ea = ElectionAdministrator::where('fkeyelection', $pkey)
            ->where('pkey', $request->ea_pkey)
            ->firstOrFail();

        $ea->update([
            'activeflg' => 0,
            'delflg'    => 1,
            'lstmdby'   => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Administrator removed.');
    }

    // -------------------------------------------------------------------------
    // Candidate Management
    // -------------------------------------------------------------------------

    public function addCandidate(Request $request, int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $request->validate([
            'fkeyknight'      => 'required|integer|exists:knight,pkey',
            'status'          => 'required|in:nominated,accepted,declined,withdrawn',
            'nomination_url'  => 'nullable|url|max:500',
        ]);

        // Cannot be EA
        $isEA = ElectionAdministrator::where('fkeyelection', $pkey)
            ->where('fkeyknight', $request->fkeyknight)
            ->exists();

        if ($isEA) {
            return back()->with('error', 'The Election Administrator cannot be added as a candidate.');
        }

        // Prevent duplicate
        $exists = ElectionCandidate::where('fkeyelection', $pkey)
            ->where('fkeyknight', $request->fkeyknight)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This knight is already listed as a candidate.');
        }

        ElectionCandidate::create([
            'fkeyelection'   => $pkey,
            'fkeyknight'     => $request->fkeyknight,
            'status'         => $request->status,
            'nomination_url' => $request->nomination_url,
            'crtsetid'       => auth()->user()->pkey,
            'lstmdby'        => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Candidate added.');
    }

    public function updateCandidate(Request $request, int $pkey, int $candidatePkey)
    {
        $candidate = ElectionCandidate::where('fkeyelection', $pkey)
            ->where('pkey', $candidatePkey)
            ->firstOrFail();

        $request->validate([
            'status'         => 'required|in:nominated,accepted,declined,withdrawn',
            'nomination_url' => 'nullable|url|max:500',
        ]);

        $candidate->update([
            'status'         => $request->status,
            'nomination_url' => $request->nomination_url,
            'lstmdby'        => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Candidate updated.');
    }

    public function removeCandidate(Request $request, int $pkey, int $candidatePkey)
    {
        $candidate = ElectionCandidate::where('fkeyelection', $pkey)
            ->where('pkey', $candidatePkey)
            ->firstOrFail();

        $candidate->update([
            'activeflg' => 0,
            'delflg'    => 1,
            'lstmdby'   => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Candidate removed.');
    }

    // -------------------------------------------------------------------------
    // Nomination Tracking
    // -------------------------------------------------------------------------

    public function addNomination(Request $request, int $pkey)
    {
        $request->validate([
            'fkeycandidate'      => 'required|integer|exists:election_candidate,pkey',
            'fkeyknight'         => 'required|integer|exists:knight,pkey',
            'action'             => 'required|in:nominated,seconded',
            'reddit_comment_url' => 'nullable|url|max:500',
        ]);

        ElectionNomination::create([
            'fkeyelection'       => $pkey,
            'fkeycandidate'      => $request->fkeycandidate,
            'fkeyknight'         => $request->fkeyknight,
            'action'             => $request->action,
            'reddit_comment_url' => $request->reddit_comment_url,
            'crtsetid'           => auth()->user()->pkey,
            'lstmdby'            => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Nomination recorded.');
    }

    // -------------------------------------------------------------------------
    // Reddit OAuth (AKSquire2 Authorization)
    // -------------------------------------------------------------------------

    public function redditAuthRedirect()
    {
        $params = http_build_query([
            'client_id'     => config('services.reddit.client_id'),
            'response_type' => 'code',
            'state'         => csrf_token(),
            'redirect_uri'  => config('services.reddit.redirect'),
            'duration'      => 'permanent',
            'scope'         => 'submit modposts identity',
        ]);

        return redirect('https://www.reddit.com/api/v1/authorize?' . $params);
    }

    public function redditAuthCallback(Request $request)
    {
        if ($request->error) {
            return redirect()
                ->route('admin.elections.settings')
                ->with('error', 'Reddit authorization was denied: ' . $request->error);
        }

        if ($request->state !== csrf_token()) {
            return redirect()
                ->route('admin.elections.settings')
                ->with('error', 'Invalid state. Please try again.');
        }

        $success = $this->reddit->storeTokens(
            $request->code,
            config('services.reddit.redirect')
        );

        if (! $success) {
            return redirect()
                ->route('admin.elections.settings')
                ->with('error', 'Token exchange failed. Check logs.');
        }

        return redirect()
            ->route('admin.elections.settings')
            ->with('success', 'AKSquire2 authorized successfully.');
    }

    // -------------------------------------------------------------------------
    // Settings Page
    // -------------------------------------------------------------------------

    public function settings()
    {
        $redditAuthorized = $this->reddit->isAuthorized();
        $oathThreadUrl    = Setting::get('oath_thread_url');
        $oathPostId       = Setting::get('oath_post_id');

        return view('admin.elections.settings', compact(
            'redditAuthorized',
            'oathThreadUrl',
            'oathPostId'
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'oath_thread_url' => 'nullable|url|max:500',
            'oath_post_id'    => 'nullable|string|max:20',
        ]);

        if ($request->filled('oath_thread_url')) {
            Setting::set('oath_thread_url', $request->oath_thread_url);
        }

        if ($request->filled('oath_post_id')) {
            Setting::set('oath_post_id', $request->oath_post_id);
            Setting::set('oath_thread_crtsetdt', now()->toDateTimeString());
        }

        return back()->with('success', 'Settings updated.');
    }

    // -------------------------------------------------------------------------
    // Admin Test Mode Toggle
    // -------------------------------------------------------------------------

    public function toggleTestMode(Request $request, int $pkey)
    {
        $election = Election::withoutGlobalScopes()->findOrFail($pkey);

        $election->update([
            'admin_test_mode' => ! $election->admin_test_mode,
            'lstmdby'         => auth()->user()->pkey,
        ]);

        $state = $election->admin_test_mode ? 'enabled' : 'disabled';
        return back()->with('success', 'Admin test mode ' . $state . '.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Get all active knights eligible to be EA or candidate.
     * Excludes already-deleted knights.
     */
    protected function getEligibleKnights()
    {
        return Knight::withoutGlobalScopes()
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->orderBy('kname', 'asc')
            ->get(['pkey', 'kname', 'rname']);
    }
}