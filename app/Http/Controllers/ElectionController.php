<?php

namespace App\Http\Controllers;

use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionCandidate;
use App\Model\ElectionNomination;
use App\Model\ElectionRegistration;
use App\Model\ElectionVote;
use App\Model\ElectionVoteAudit;
use App\Model\ElectionPhaseLog;
use App\Model\ElectionAuditLog;
use App\Model\Oath;
use App\Services\ElectionService;
use App\Services\RedditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ElectionController extends Controller
{
    protected ElectionService $electionService;
    protected RedditService $reddit;

    public function __construct(ElectionService $electionService, RedditService $reddit)
    {
        $this->middleware('auth');
        $this->middleware('election.admin')->except([
            'register',
            'unregister',
            'ballot',
            'submitBallot',
            'paused',
        ]);
        $this->electionService = $electionService;
        $this->reddit          = $reddit;
    }

    // -------------------------------------------------------------------------
    // EA Dashboard
    // -------------------------------------------------------------------------

    public function dashboard(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->with(['knight', 'nominations.knight'])
            ->get();

        $registrations = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->with('knight')
            ->get();

        $voteCount = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->count();

        $registeredCount = $registrations->count();

        $phaseLog = ElectionPhaseLog::where('fkeyelection', $election->pkey)
            ->orderBy('crtsetdt', 'asc')
            ->get();

        $redditAuthorized = $this->reddit->isAuthorized();

        return view('election.dashboard', compact(
            'election',
            'eaRecord',
            'isAdminTest',
            'candidates',
            'registrations',
            'voteCount',
            'registeredCount',
            'phaseLog',
            'redditAuthorized'
        ));
    }

    // -------------------------------------------------------------------------
    // Phase Advancement
    // -------------------------------------------------------------------------

    public function advancePhase(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        // Assistant EA and admin test mode cannot advance phases
        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'You do not have permission to advance the election phase.');
        }

        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        // Gate checks per phase
        $currentPhase = $election->phase;

        if ($currentPhase === 'nominations') {
            $acceptedCount = ElectionCandidate::where('fkeyelection', $election->pkey)
                ->where('status', 'accepted')
                ->count();

            if ($acceptedCount < 2) {
                return back()->with('error', 'At least two accepted candidates are required before advancing past nominations.');
            }
        }

        if ($currentPhase === 'debate') {
            if (! $election->debate_post_id) {
                return back()->with('error', 'The debate thread must be posted to Reddit before advancing to voting.');
            }
        }

        if ($currentPhase === 'voting') {
            // Require passphrase to close voting
            $request->validate(['passphrase' => 'required|string']);

            // Verify passphrase works by attempting a test decrypt
            $testVote = ElectionVote::where('fkeyelection', $election->pkey)
                ->where('valid', 1)
                ->first();

            if ($testVote) {
                $result = $this->electionService->decryptBallot(
                    $testVote->encrypted_ballot,
                    $request->passphrase,
                    $testVote->fkeyknight
                );

                if ($result === null) {
                    return back()->with('error', 'Passphrase verification failed. Check your passphrase and try again.');
                }
            }

            // Store passphrase in session for counting phase
            session(['election_passphrase' => $request->passphrase]);
            $election->voting_paused = false;
        }

        $this->electionService->advancePhase(
            $election,
            auth()->user()->pkey,
            $request->note
        );

        // Auto-reset admin test mode on completion
        if ($election->phase === 'complete') {
            $election->update(['admin_test_mode' => false]);
        }

        return redirect()
            ->route('election.dashboard')
            ->with('success', 'Election advanced to ' . $election->fresh()->phase . ' phase.');
    }

    // -------------------------------------------------------------------------
    // Reddit Thread Posting
    // -------------------------------------------------------------------------

    public function postNominationThread(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can post Reddit threads.');
        }

        $request->validate([
            'title' => 'required|string|max:300',
            'body'  => 'required|string',
        ]);

        $result = $this->reddit->submitAndSticky($request->title, $request->body);

        if (! $result['success']) {
            return back()->with('error', $result['error']);
        }

        $election->update([
            'nomination_thread_url' => $result['post']['url'],
            'nomination_post_id'    => $result['post']['post_id'],
            'lstmdby'               => auth()->user()->pkey,
        ]);

        $message = 'Nomination thread posted successfully.';
        if (! $result['sticky']) {
            $message .= ' Warning: ' . $result['error'];
        }

        return back()->with('success', $message);
    }

    public function postDebateThread(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can post Reddit threads.');
        }

        $request->validate([
            'title' => 'required|string|max:300',
            'body'  => 'required|string',
        ]);

        // Build candidate mention list for the body
        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->with('knight')
            ->get();

        $mentions = $candidates->map(fn($c) => 'u/' . $c->knight->rname)->implode(', ');
        $body     = $request->body . "\n\n---\n\nCandidates: " . $mentions;

        $result = $this->reddit->submitAndSticky($request->title, $body);

        if (! $result['success']) {
            return back()->with('error', $result['error']);
        }

        $election->update([
            'debate_thread_url' => $result['post']['url'],
            'debate_post_id'    => $result['post']['post_id'],
            'lstmdby'           => auth()->user()->pkey,
        ]);

        $message = 'Debate thread posted successfully.';
        if (! $result['sticky']) {
            $message .= ' Warning: ' . $result['error'];
        }

        return back()->with('success', $message);
    }

    // -------------------------------------------------------------------------
    // Voting — EA Passphrase Management
    // -------------------------------------------------------------------------

    public function openVoting(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can open voting.');
        }

        if ($election->phase !== 'voting') {
            return back()->with('error', 'Election is not in the voting phase.');
        }

        $request->validate([
            'passphrase' => 'required|string|min:12',
        ]);

        session(['election_passphrase' => $request->passphrase]);

        $election->update([
            'voting_paused' => false,
            'lstmdby'       => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Voting is now open. Keep this session active while voting is open.');
    }

    public function pauseVoting(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can pause voting.');
        }

        session()->forget('election_passphrase');

        $election->update([
            'voting_paused' => true,
            'lstmdby'       => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Voting paused. Knights will see a paused message until you re-authenticate.');
    }

    // -------------------------------------------------------------------------
    // EA Dashboard Toggles
    // -------------------------------------------------------------------------

    public function updateToggles(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ! $eaRecord || $eaRecord->is_assistant) {
            abort(403, 'Only the full Election Administrator can update dashboard settings.');
        }

        $eaRecord->update([
            'silent_audit_dms'                    => $request->boolean('silent_audit_dms'),
            'show_voter_names_secondary_officers' => $request->boolean('show_voter_names_secondary_officers'),
            'lstmdby'                             => auth()->user()->pkey,
        ]);

        return back()->with('success', 'Settings updated.');
    }

    // -------------------------------------------------------------------------
    // Voter List
    // -------------------------------------------------------------------------

    public function voterList(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        $registrations = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->with('knight')
            ->get();

        $votedPkeys = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->pluck('fkeyknight')
            ->toArray();

        return view('election.voter-list', compact(
            'election',
            'eaRecord',
            'isAdminTest',
            'registrations',
            'votedPkeys'
        ));
    }

    // -------------------------------------------------------------------------
    // Results
    // -------------------------------------------------------------------------

    public function results(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($election->phase !== 'counting' && $election->phase !== 'complete') {
            abort(403, 'Results are not available until the counting phase.');
        }

        $passphrase = session('election_passphrase');

        if (! $passphrase) {
            return view('election.results-locked', compact('election'));
        }

        $results = $this->electionService->calculateResults($election, $passphrase);

        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->with('knight')
            ->get()
            ->keyBy('pkey');

        return view('election.results', compact(
            'election',
            'eaRecord',
            'isAdminTest',
            'results',
            'candidates'
        ));
    }

    // -------------------------------------------------------------------------
    // Audit CSV Upload
    // -------------------------------------------------------------------------

    public function auditUpload(Request $request)
    {
        $election = $request->attributes->get('active_election');
        $eaRecord = $request->attributes->get('ea_record');

        if ($eaRecord && $eaRecord->is_assistant) {
            abort(403);
        }

        $request->validate([
            'audit_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $passphrase = session('election_passphrase');

        if (! $passphrase) {
            return back()->with('error', 'You must authenticate with your passphrase before running an audit.');
        }

        $path = $request->file('audit_csv')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        // Expect columns: knight_pkey, rankings
        if (! in_array('knight_pkey', $header) || ! in_array('rankings', $header)) {
            return back()->with('error', 'CSV must have columns: knight_pkey, rankings');
        }

        $mapped = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $rows);

        $discrepancies = $this->electionService->auditCompare($election, $passphrase, $mapped);

        return view('election.audit-results', compact(
            'election',
            'eaRecord',
            'discrepancies'
        ));
    }

    // -------------------------------------------------------------------------
    // Key Archive
    // -------------------------------------------------------------------------

    public function archiveKey(Request $request)
    {
        $election = $request->attributes->get('active_election');
        $eaRecord = $request->attributes->get('ea_record');

        if ($eaRecord && $eaRecord->is_assistant) {
            abort(403);
        }

        if ($election->phase !== 'complete') {
            return back()->with('error', 'The encryption key can only be archived after the election is complete.');
        }

        $request->validate([
            'passphrase' => 'required|string',
            'note'       => 'nullable|string|max:1000',
        ]);

        $this->electionService->archiveKey(
            $election,
            $request->passphrase,
            auth()->user()->pkey,
            $request->note
        );

        return back()->with('success', 'Encryption key archived securely.');
    }

    // -------------------------------------------------------------------------
    // Knight-Facing: Registration
    // -------------------------------------------------------------------------

    public function register(Request $request)
    {
        $election = Election::active();

        if (! $election) {
            return back()->with('error', 'There is no active election to register for.');
        }

        $knight = auth()->user();

        // Must be active
        if (! $knight->activeflg || $knight->delflg) {
            return back()->with('error', 'Your account is not in good standing.');
        }

        // Prevent duplicate registration
        $exists = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->exists();

        if ($exists) {
            return back()->with('info', 'You are already registered for this election.');
        }

        ElectionRegistration::create([
            'fkeyelection'  => $election->pkey,
            'fkeyknight'    => $knight->pkey,
            'registered_at' => now(),
            'crtsetid'      => $knight->pkey,
            'lstmdby'       => $knight->pkey,
        ]);

        return back()->with('success', 'You are now registered to vote in this election.');
    }

    public function unregister(Request $request)
    {
        $election = Election::active();

        if (! $election || $election->phase === 'voting' || $election->phase === 'counting') {
            return back()->with('error', 'Registration cannot be withdrawn at this stage.');
        }

        $knight = auth()->user();

        $registration = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->first();

        if (! $registration) {
            return back()->with('info', 'You are not registered for this election.');
        }

        $registration->update([
            'activeflg' => 0,
            'delflg'    => 1,
            'lstmdby'   => $knight->pkey,
        ]);

        return back()->with('success', 'Your registration has been withdrawn.');
    }

    // -------------------------------------------------------------------------
    // Knight-Facing: Ballot
    // -------------------------------------------------------------------------

    public function ballot(Request $request)
    {
        $election = Election::active();

        if (! $election || ! $election->isVotingOpen()) {
            return view('election.paused');
        }

        $knight = auth()->user();

        // Check eligibility
        $this->checkVoterEligibility($election, $knight);

        // Already voted?
        $alreadyVoted = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->exists();

        if ($alreadyVoted) {
            return view('election.already-voted', compact('election'));
        }

        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->with('knight')
            ->get();

        // Randomise candidate order — consistent per session
        if (! session()->has('ballot_order_' . $election->pkey)) {
            $shuffled = $candidates->shuffle()->pluck('pkey')->toArray();
            session(['ballot_order_' . $election->pkey => $shuffled]);
        }

        $order = session('ballot_order_' . $election->pkey);
        $candidates = $candidates->sortBy(fn($c) => array_search($c->pkey, $order))->values();

        return view('election.ballot', compact('election', 'candidates'));
    }

    public function submitBallot(Request $request)
    {
        $election = Election::active();

        if (! $election || ! $election->isVotingOpen()) {
            return back()->with('error', 'Voting is not currently open.');
        }

        $knight = auth()->user();

        $this->checkVoterEligibility($election, $knight);

        // Duplicate vote guard
        $alreadyVoted = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->exists();

        if ($alreadyVoted) {
            return back()->with('error', 'You have already submitted a ballot.');
        }

        // Validate rankings
        $acceptedCandidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->pluck('pkey')
            ->toArray();

        $request->validate([
            'rankings'   => 'required|array',
            'rankings.*' => 'required|integer|min:1',
        ]);

        $rankings = $request->rankings; // [candidate_pkey => rank]

        // Ensure all submitted candidate pkeys are valid
        foreach (array_keys($rankings) as $candidatePkey) {
            if (! in_array((int) $candidatePkey, $acceptedCandidates)) {
                return back()->with('error', 'Invalid candidate in ballot submission.');
            }
        }

        // Ensure ranks are unique and sequential
        $rankValues = array_values($rankings);
        sort($rankValues);
        if ($rankValues !== range(1, count($acceptedCandidates))) {
            return back()->with('error', 'Please rank all candidates with unique values from 1 to ' . count($acceptedCandidates) . '.');
        }

        // Get passphrase from session
        $passphrase = session('election_passphrase');

        if (! $passphrase) {
            return back()->with('error', 'Voting is temporarily paused. Please try again shortly.');
        }

        $encrypted = $this->electionService->encryptBallot(
            $rankings,
            $passphrase,
            $knight->pkey
        );

        $vote = ElectionVote::create([
            'fkeyelection'     => $election->pkey,
            'fkeyknight'       => $knight->pkey,
            'encrypted_ballot' => $encrypted,
            'submitted_at'     => now(),
            'valid'            => true,
            'crtsetid'         => $knight->pkey,
            'lstmdby'          => $knight->pkey,
        ]);

        // Log the audit event
        $this->electionService->logVoteAudit(
            $election->pkey,
            $knight->pkey,
            'submitted',
            $knight->pkey
        );

        // Send Discord DM audit to EA
        $this->sendAuditDm($election, $knight, $rankings);

        return view('election.ballot-confirmed', compact('election'));
    }

    // -------------------------------------------------------------------------
    // Voting Paused Screen
    // -------------------------------------------------------------------------

    public function paused()
    {
        $election = Election::active();
        return view('election.paused', compact('election'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function checkVoterEligibility(Election $election, $knight): void
    {
        // Must be registered
        $registered = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->exists();

        if (! $registered) {
            abort(403, 'You are not registered to vote in this election.');
        }

        // Must have a current-year oath
        $oath = Oath::currentYearForKnight($knight->pkey);

        if (! $oath || ! $oath->verified) {
            abort(403, 'You must have a verified oath for the current year to vote.');
        }
    }

    protected function sendAuditDm(Election $election, $knight, array $rankings): void
    {
        try {
            $eaRecord = ElectionAdministrator::where('fkeyelection', $election->pkey)
                ->where('is_assistant', 0)
                ->with('knight')
                ->first();

            if (! $eaRecord || ! $eaRecord->knight) {
                Log::warning('sendAuditDm: No EA found for election ' . $election->pkey);
                return;
            }

            // Build ranking string: candidate pkeys in rank order
            asort($rankings);
            $rankingString = implode(',', array_keys($rankings));

            // Build payload — rankings are included here because the webhook
            // delivers directly to EA Discord DMs and is stored nowhere in between
            $payload = implode("\n", [
                '**April Knights — Vote Audit**',
                'Election: ' . $election->election_year,
                'Knight ID: ' . $knight->pkey,
                'Rankings: ' . $rankingString,
            ]);

            $silent = $eaRecord->silent_audit_dms;

            // Append Discord silent flag instruction for the bot
            // Bot applies MessageFlags.SuppressNotifications when silent = true
            $response = Http::timeout(5)
                ->post(config('services.squire_bot_webhook_url') . '/webhook/election-audit', [
                    'discordid' => (string) $eaRecord->knight->discordid,
                    'payload'   => $payload,
                    'silent'    => $silent,
                    'token'     => config('services.squire_bot_webhook_secret'),
                ]);

            // Log delivery confirmation — no content stored
            ElectionAuditLog::create([
                'fkeyelection' => $election->pkey,
                'fkeyknight'   => $knight->pkey,
                'delivered'    => $response->successful(),
                'delivered_at' => $response->successful() ? now() : null,
            ]);

            if (! $response->successful()) {
                Log::warning('sendAuditDm: Webhook delivery failed for knight ' . $knight->pkey, [
                    'status' => $response->status(),
                ]);
            }

        } catch (\Throwable $e) {
            // DM failure must never prevent a ballot from being recorded
            Log::error('sendAuditDm: Exception for knight ' . $knight->pkey . ': ' . $e->getMessage());

            // Still log the delivery attempt as failed
            ElectionAuditLog::create([
                'fkeyelection' => $election->pkey,
                'fkeyknight'   => $knight->pkey,
                'delivered'    => false,
                'delivered_at' => null,
            ]);
        }
    }
}