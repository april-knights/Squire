<?php

namespace App\Http\Controllers;

use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionCandidate;
use App\Model\ElectionKeyArchive;
use App\Model\ElectionPhaseLog;
use App\Model\ElectionRegistration;
use App\Model\ElectionVote;
use App\Model\Oath;
use App\Services\ElectionService;
use App\Services\RedditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        // Audit DM readiness: the test DM must have succeeded AFTER the current EA was appointed
        $fullEA = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('is_assistant', 0)
            ->first();

        $dmVerified = (bool) ($fullEA
            && $election->audit_dm_verified_at
            && $election->audit_dm_verified_at->gte($fullEA->appointed_at));

        $failedDmCount = $this->electionService->failedAuditDmCount($election);

        $keyArchived = ElectionKeyArchive::where('fkeyelection', $election->pkey)->exists();

        return view('election.dashboard', compact(
            'election',
            'eaRecord',
            'isAdminTest',
            'candidates',
            'registrations',
            'voteCount',
            'registeredCount',
            'phaseLog',
            'redditAuthorized',
            'dmVerified',
            'failedDmCount',
            'keyArchived'
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
        $userPkey    = auth()->user()->pkey;

        // Assistant EA and admin test mode cannot advance phases
        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'You do not have permission to advance the election phase.');
        }

        $v = Validator::make($request->all(), [
            'note' => 'nullable|string|max:1000',
        ]);
        if ($v->fails()) {
            return back()->with('error', $v->errors()->first());
        }

        $note         = $request->input('note');
        $currentPhase = $election->phase;

        // nominations -> debate
        if ($currentPhase === 'nominations') {
            $acceptedCount = ElectionCandidate::where('fkeyelection', $election->pkey)
                ->where('status', 'accepted')
                ->count();

            if ($acceptedCount < 2) {
                return back()->with('error', 'At least two accepted candidates are required before advancing past nominations.');
            }
        }

        // debate -> voting: generates the election keys from the EA passphrase
        if ($currentPhase === 'debate') {
            $debateSkipped = ! $election->debate_post_id;

            if ($debateSkipped && ! $election->admin_test_mode) {
                return back()->with('error', 'The debate thread must be posted to Reddit before opening voting.');
            }

            $v = Validator::make($request->all(), [
                'passphrase' => 'required|string|min:' . ElectionService::MIN_PASSPHRASE_LENGTH . '|confirmed',
            ]);
            if ($v->fails()) {
                return back()->with('error', $v->errors()->first());
            }

            if ($debateSkipped) {
                $note = trim('[test mode: debate thread requirement skipped] ' . $note);
            }

            $result = $this->electionService->startVoting(
                $election,
                $request->input('passphrase'),
                $userPkey,
                $note ?: null
            );

            if (! $result['ok']) {
                return back()->with('error', $result['error']);
            }

            return redirect()
                ->route('election.dashboard')
                ->with('success', 'Voting is open. Encryption keys were generated from your passphrase. Keep that passphrase safe: it cannot be recovered.');
        }

        // counting -> complete: the key should be archived first (or explicitly skipped)
        if ($currentPhase === 'counting') {
            $archived = ElectionKeyArchive::where('fkeyelection', $election->pkey)->exists();
            $skip     = $request->boolean('skip_archive');

            if (! $archived && ! $skip) {
                return back()->with('error', 'Archive the encryption key before completing the election, or tick "Complete without archiving the key".');
            }

            if (! $archived && $skip) {
                $note = trim('[Key not archived] ' . $note);
            }
        }

        $this->electionService->advancePhase($election, $userPkey, $note);

        // Auto-reset admin test mode on completion
        if ($election->phase === 'complete') {
            $election->forceFill(['admin_test_mode' => false])->save();
        }

        return redirect()
            ->route('election.dashboard')
            ->with('success', 'Election advanced to ' . ucfirst($election->phase) . ' phase.');
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
    // Audit DM readiness
    // -------------------------------------------------------------------------

    public function sendTestDm(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ! $eaRecord || $eaRecord->is_assistant) {
            abort(403, 'Only the full Election Administrator can send a test audit DM.');
        }

        $result = $this->electionService->sendTestAuditDm($election);

        if (! $result['ok']) {
            return back()->with('error', 'Test DM failed: ' . $result['error']
                . ' Check that your Discord ID is on your Squire profile and that you allow DMs from server members.');
        }

        return back()->with('success', 'Test audit DM delivered. Check your Discord DMs. Voting can now be opened.');
    }

    // -------------------------------------------------------------------------
    // Voting controls (manual pause / resume — no passphrase needed)
    // -------------------------------------------------------------------------

    public function openVoting(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can resume voting.');
        }

        if ($election->phase !== 'voting') {
            return back()->with('error', 'Election is not in the voting phase.');
        }

        if (! $election->hasKeys()) {
            return back()->with('error', 'Encryption keys have not been generated for this election.');
        }

        $election->forceFill([
            'voting_paused' => false,
            'lstmdby'       => auth()->user()->pkey,
        ])->save();

        $this->logVotingControl($election, 'Voting resumed.');

        return back()->with('success', 'Voting resumed.');
    }

    public function pauseVoting(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403, 'Only the full Election Administrator can pause voting.');
        }

        if ($election->phase !== 'voting') {
            return back()->with('error', 'Election is not in the voting phase.');
        }

        $election->forceFill([
            'voting_paused' => true,
            'lstmdby'       => auth()->user()->pkey,
        ])->save();

        $this->logVotingControl($election, 'Voting paused by the Election Administrator.');

        return back()->with('success', 'Voting paused. Knights will see a paused message until you resume.');
    }

    protected function logVotingControl(Election $election, string $note): void
    {
        ElectionPhaseLog::create([
            'fkeyelection'    => $election->pkey,
            'from_phase'      => 'voting',
            'to_phase'        => 'voting',
            'transitioned_by' => auth()->user()->pkey,
            'note'            => $note,
        ]);
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
    // Results — passphrase is entered per request and never stored
    // -------------------------------------------------------------------------

    public function results(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest) {
            abort(403, 'Results are not available in admin test mode.');
        }

        if (! in_array($election->phase, ['counting', 'complete'], true)) {
            abort(403, 'Results are not available until the counting phase.');
        }

        if (! $request->isMethod('post') || ! $request->filled('passphrase')) {
            return view('election.results-locked', compact('election'));
        }

        $passphrase = $request->input('passphrase');

        if (! $this->electionService->verifyPassphrase($election, $passphrase)) {
            return redirect()->route('election.results')->with('error', 'Passphrase incorrect.');
        }

        $results = $this->electionService->calculateResults($election, $passphrase);

        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->with('knight')
            ->get()
            ->keyBy('pkey');

        $keyArchived = ElectionKeyArchive::where('fkeyelection', $election->pkey)->exists();

        return response()
            ->view('election.results', compact(
                'election',
                'eaRecord',
                'isAdminTest',
                'results',
                'candidates',
                'keyArchived'
            ))
            ->header('Cache-Control', 'no-store, private');
    }

    // -------------------------------------------------------------------------
    // Audit — upload a CSV, or paste the raw audit DMs
    // -------------------------------------------------------------------------

    public function auditUpload(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403);
        }

        if (! in_array($election->phase, ['counting', 'complete'], true)) {
            return back()->with('error', 'The audit can only be run after voting has closed.');
        }

        $v = Validator::make($request->all(), [
            'passphrase' => 'required|string',
            'audit_csv'  => 'nullable|file|mimes:csv,txt|max:2048',
            'audit_text' => 'nullable|string|max:500000',
        ]);
        if ($v->fails()) {
            return back()->with('error', $v->errors()->first());
        }

        if (! $request->hasFile('audit_csv') && ! $request->filled('audit_text')) {
            return back()->with('error', 'Upload a CSV or paste the audit DMs.');
        }

        $passphrase = $request->input('passphrase');

        if (! $this->electionService->verifyPassphrase($election, $passphrase)) {
            return back()->with('error', 'Passphrase incorrect.');
        }

        $raw = $request->filled('audit_text')
            ? $request->input('audit_text')
            : file_get_contents($request->file('audit_csv')->getRealPath());

        $rows = $this->parseAuditRows((string) $raw);

        if (empty($rows)) {
            return back()->with('error', 'No audit records found. Paste the DMs as received, or use a CSV with the columns: knight_pkey, rankings.');
        }

        $checkedCount  = count(array_unique(array_column($rows, 'knight_pkey')));
        $discrepancies = $this->electionService->auditCompare($election, $passphrase, $rows);

        return response()
            ->view('election.audit-results', compact(
                'election',
                'eaRecord',
                'discrepancies',
                'checkedCount'
            ))
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Accepts raw pasted DMs ("Knight ID: / Rankings:") or a CSV with knight_pkey,rankings columns.
     */
    protected function parseAuditRows(string $raw): array
    {
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        if (trim($raw) === '') {
            return [];
        }

        if (stripos($raw, 'Knight ID:') !== false) {
            return $this->electionService->parseAuditText($raw);
        }

        $lines  = preg_split('/\R/', trim($raw));
        $header = array_map(fn ($h) => strtolower(trim($h)), str_getcsv(array_shift($lines)));

        $kIdx = array_search('knight_pkey', $header, true);
        $rIdx = array_search('rankings', $header, true);

        if ($kIdx === false || $rIdx === false) {
            return [];
        }

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if (! isset($cols[$kIdx], $cols[$rIdx])) {
                continue;
            }
            $rows[] = [
                'knight_pkey' => (int) $cols[$kIdx],
                'rankings'    => $cols[$rIdx],
            ];
        }

        return $rows;
    }

    // -------------------------------------------------------------------------
    // Key Archive
    // -------------------------------------------------------------------------

    public function archiveKey(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ($eaRecord && $eaRecord->is_assistant)) {
            abort(403);
        }

        if (! in_array($election->phase, ['counting', 'complete'], true)) {
            return back()->with('error', 'The encryption key can only be archived once voting has closed.');
        }

        $v = Validator::make($request->all(), [
            'passphrase' => 'required|string',
            'note'       => 'nullable|string|max:1000',
        ]);
        if ($v->fails()) {
            return back()->with('error', $v->errors()->first());
        }

        $result = $this->electionService->archiveKey(
            $election,
            $request->input('passphrase'),
            auth()->user()->pkey,
            $request->input('note')
        );

        if (! $result['ok']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Encryption key archived.');
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
            return view('election.paused', compact('election'));
        }

        $knight = auth()->user();

        if ($error = $this->voterEligibilityError($election, $knight)) {
            return redirect('/')->with('error', $error);
        }

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

        $order      = session('ballot_order_' . $election->pkey);
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

        if ($error = $this->voterEligibilityError($election, $knight)) {
            return redirect('/')->with('error', $error);
        }

        // Duplicate vote guard (the DB unique key is the final backstop)
        $alreadyVoted = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->exists();

        if ($alreadyVoted) {
            return back()->with('error', 'You have already submitted a ballot.');
        }

        $v = Validator::make($request->all(), [
            'rankings'   => 'required|array',
            'rankings.*' => 'required|integer|min:1',
        ]);
        if ($v->fails()) {
            return back()->with('error', 'Please rank every candidate before submitting.');
        }

        $acceptedCandidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->pluck('pkey')
            ->map(fn ($p) => (int) $p)
            ->all();

        // Normalise to [candidate_pkey => rank] as integers
        $rankings = [];
        foreach ($request->input('rankings') as $candidatePkey => $rank) {
            $rankings[(int) $candidatePkey] = (int) $rank;
        }

        foreach (array_keys($rankings) as $candidatePkey) {
            if (! in_array($candidatePkey, $acceptedCandidates, true)) {
                return back()->with('error', 'Invalid candidate in ballot submission.');
            }
        }

        // Ranks must be unique and run 1..N across ALL accepted candidates
        $rankValues = array_values($rankings);
        sort($rankValues);
        if ($rankValues !== range(1, count($acceptedCandidates))) {
            return back()->with('error', 'Please rank all candidates with unique values from 1 to ' . count($acceptedCandidates) . '.');
        }

        $result = $this->electionService->recordBallot($election, $knight, $rankings);

        if ($result['ok']) {
            return view('election.ballot-confirmed', compact('election'));
        }

        if ($result['code'] === 'duplicate') {
            return back()->with('error', 'You have already submitted a ballot.');
        }

        if ($result['code'] === 'audit_dm') {
            return back()->with('error',
                'Your ballot was NOT recorded. A required audit message could not be delivered to the Election Administrator, '
                . 'so the system rejected your vote rather than record it unverified. This is not a problem with your account or your Discord settings. '
                . 'Please submit your ballot again in a minute. If it keeps failing, contact the Arcaenum at AskTheArcaenum@aprilknights.org '
                . 'and describe what you see.'
            );
        }

        return back()->with('error', 'Your ballot was NOT recorded due to a system error. Please try again, or contact the Arcaenum at AskTheArcaenum@aprilknights.org if it continues.');
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

    /**
     * Returns a human-readable reason the knight cannot vote, or null if eligible.
     */
    protected function voterEligibilityError(Election $election, $knight): ?string
    {
        $registered = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->exists();

        if (! $registered) {
            return 'You are not registered to vote in this election.';
        }

        $oath = Oath::currentYearForKnight($knight->pkey);

        if (! $oath || ! $oath->verified) {
            return 'You need a verified oath for ' . Oath::currentOathYear() . ' to vote.';
        }

        return null;
    }
}