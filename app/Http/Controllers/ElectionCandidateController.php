<?php

namespace App\Http\Controllers;

use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionCandidate;
use App\Model\ElectionNomination;
use App\Model\ElectionPhaseLog;
use App\Model\Knight;
use App\Model\Oath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectionCandidateController extends Controller
{
    /** Phases in which the candidate roster and nomination records may be edited. */
    const EDITABLE_PHASES = ['setup', 'nominations', 'debate'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('election.admin');
    }

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = (bool) $request->attributes->get('is_admin_test', false);

        $isAssistant = (bool) ($eaRecord && $eaRecord->is_assistant);
        $isFullEA    = ! $isAdminTest && $eaRecord && ! $eaRecord->is_assistant;
        $phaseLocked = ! in_array($election->phase, self::EDITABLE_PHASES, true);
        $canEdit     = $isFullEA && ! $phaseLocked;

        $candidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->with(['knight', 'nominations.knight'])
            ->get();

        $oathYear    = Oath::currentOathYear();
        $oathedPkeys = Oath::where('oath_year', $oathYear)
            ->where('verified', 1)
            ->pluck('fkeyknight')
            ->all();

        $knights          = collect();
        $candidateKnights = collect();

        if ($canEdit) {
            $eaPkeys    = ElectionAdministrator::where('fkeyelection', $election->pkey)->pluck('fkeyknight')->all();
            $takenPkeys = $candidates->pluck('fkeyknight')->all();

            $knights = Knight::withoutGlobalScopes()
                ->where('activeflg', 1)
                ->where('delflg', 0)
                ->orderBy('rname')
                ->get(['pkey', 'rname']);

            $candidateKnights = $knights
                ->reject(fn ($k) => in_array($k->pkey, $eaPkeys) || in_array($k->pkey, $takenPkeys))
                ->values();
        }

        return view('election.candidates', compact(
            'election',
            'candidates',
            'canEdit',
            'phaseLocked',
            'isAssistant',
            'isAdminTest',
            'oathYear',
            'oathedPkeys',
            'knights',
            'candidateKnights'
        ));
    }

    // -------------------------------------------------------------------------
    // Candidates
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        if ($denied = $this->denyIfLocked($request)) {
            return $denied;
        }

        $election = $request->attributes->get('active_election');

        $v = Validator::make($request->all(), [
            'fkeyknight'     => 'required|integer',
            'status'         => 'required|in:nominated,accepted,declined,withdrawn',
            'nomination_url' => 'nullable|url|max:500',
        ]);
        if ($v->fails()) {
            return $this->back($v->errors()->first(), 'error');
        }

        $knight = $this->findActiveKnight((int) $request->fkeyknight);
        if (! $knight) {
            return $this->back('Knight not found or not active.', 'error');
        }

        if ($this->isElectionAdministrator($election, (int) $knight->pkey)) {
            return $this->back('The Election Administrator and Assistant cannot be candidates.', 'error');
        }

        if ($request->status === 'accepted' && ($err = $this->oathError($knight))) {
            return $this->back($err, 'error');
        }

        $alreadyListed = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->first();

        if ($alreadyListed) {
            return $this->back('/u/' . $knight->rname . ' is already on the candidate list.', 'error');
        }

        $userPkey = auth()->user()->pkey;

        // A removed candidate keeps their row (unique key on election + knight): restore it.
        $removed = ElectionCandidate::withoutGlobalScopes()
            ->where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->first();

        if ($removed) {
            $removed->forceFill([
                'status'         => $request->status,
                'nomination_url' => $request->nomination_url,
                'activeflg'      => 1,
                'delflg'         => 0,
                'lstmdby'        => $userPkey,
            ])->save();
        } else {
            $candidate = new ElectionCandidate();
            $candidate->forceFill([
                'fkeyelection'   => $election->pkey,
                'fkeyknight'     => $knight->pkey,
                'status'         => $request->status,
                'nomination_url' => $request->nomination_url,
                'crtsetid'       => $userPkey,
                'lstmdby'        => $userPkey,
            ])->save();
        }

        $this->logChange($election, 'Candidate added: /u/' . $knight->rname . ' (' . $request->status . ').');

        return $this->back('/u/' . $knight->rname . ' added as ' . $request->status . '.', 'success');
    }

    public function update(Request $request, int $candidatePkey)
    {
        if ($denied = $this->denyIfLocked($request)) {
            return $denied;
        }

        $election = $request->attributes->get('active_election');

        $v = Validator::make($request->all(), [
            'status' => 'required|in:nominated,accepted,declined,withdrawn',
        ]);
        if ($v->fails()) {
            return $this->back($v->errors()->first(), 'error');
        }

        $candidate = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('pkey', $candidatePkey)
            ->with('knight')
            ->first();

        if (! $candidate || ! $candidate->knight) {
            return $this->back('Candidate not found.', 'error');
        }

        $from = $candidate->status;
        $to   = $request->status;

        if ($from === $to) {
            return $this->back('No change.', 'info');
        }

        if ($to === 'accepted' && ($err = $this->oathError($candidate->knight))) {
            return $this->back($err, 'error');
        }

        $candidate->forceFill([
            'status'  => $to,
            'lstmdby' => auth()->user()->pkey,
        ])->save();

        $this->logChange($election, 'Candidate /u/' . $candidate->knight->rname . ': ' . $from . ' → ' . $to . '.');

        return $this->back('/u/' . $candidate->knight->rname . ' is now ' . $to . '.', 'success');
    }

    public function remove(Request $request, int $candidatePkey)
    {
        if ($denied = $this->denyIfLocked($request)) {
            return $denied;
        }

        $election = $request->attributes->get('active_election');

        $candidate = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('pkey', $candidatePkey)
            ->with('knight')
            ->first();

        if (! $candidate) {
            return $this->back('Candidate not found.', 'error');
        }

        $name = $candidate->knight->rname ?? ('#' . $candidate->fkeyknight);

        $candidate->forceFill([
            'activeflg' => 0,
            'delflg'    => 1,
            'lstmdby'   => auth()->user()->pkey,
        ])->save();

        $this->logChange($election, 'Candidate removed: /u/' . $name . ' (was ' . $candidate->status . ').');

        return $this->back('/u/' . $name . ' removed from the candidate list.', 'success');
    }

    // -------------------------------------------------------------------------
    // Nomination / seconding records (clerical — the process itself happens on Reddit)
    // -------------------------------------------------------------------------

    public function storeNomination(Request $request)
    {
        if ($denied = $this->denyIfLocked($request)) {
            return $denied;
        }

        $election = $request->attributes->get('active_election');

        $v = Validator::make($request->all(), [
            'fkeycandidate'      => 'required|integer',
            'fkeyknight'         => 'required|integer',
            'action'             => 'required|in:nominated,seconded',
            'reddit_comment_url' => ['nullable', 'url', 'max:500', 'regex:/^https?:\/\/(www\.|old\.|new\.)?(reddit\.com|redd\.it)\//i'],
        ], [
            'reddit_comment_url.regex' => 'The comment link must be a Reddit URL.',
        ]);
        if ($v->fails()) {
            return $this->back($v->errors()->first(), 'error');
        }

        $candidate = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('pkey', (int) $request->fkeycandidate)
            ->with('knight')
            ->first();

        if (! $candidate) {
            return $this->back('Candidate not found.', 'error');
        }

        $actor = $this->findActiveKnight((int) $request->fkeyknight);
        if (! $actor) {
            return $this->back('Knight not found or not active.', 'error');
        }

        if ($request->action === 'nominated'
            && ElectionNomination::where('fkeycandidate', $candidate->pkey)->where('action', 'nominated')->exists()) {
            return $this->back('This candidate already has a nominator. Record additional supporters as seconds.', 'error');
        }

        if (ElectionNomination::where('fkeycandidate', $candidate->pkey)->where('fkeyknight', $actor->pkey)->exists()) {
            return $this->back('/u/' . $actor->rname . ' is already recorded for this candidate.', 'error');
        }

        $userPkey = auth()->user()->pkey;

        $record = new ElectionNomination();
        $record->forceFill([
            'fkeyelection'       => $election->pkey,
            'fkeycandidate'      => $candidate->pkey,
            'fkeyknight'         => $actor->pkey,
            'action'             => $request->action,
            'reddit_comment_url' => $request->reddit_comment_url,
            'crtsetid'           => $userPkey,
            'lstmdby'            => $userPkey,
        ])->save();

        $candName = $candidate->knight->rname ?? ('#' . $candidate->fkeyknight);
        $this->logChange($election, 'Recorded: /u/' . $actor->rname . ' ' . $request->action . ' /u/' . $candName . '.');

        return $this->back('Recorded: /u/' . $actor->rname . ' ' . $request->action . ' /u/' . $candName . '.', 'success');
    }

    public function removeNomination(Request $request, int $nominationPkey)
    {
        if ($denied = $this->denyIfLocked($request)) {
            return $denied;
        }

        $election = $request->attributes->get('active_election');

        $record = ElectionNomination::where('fkeyelection', $election->pkey)
            ->where('pkey', $nominationPkey)
            ->with(['knight', 'candidate.knight'])
            ->first();

        if (! $record) {
            return $this->back('Record not found.', 'error');
        }

        $actorName = $record->knight->rname ?? ('#' . $record->fkeyknight);
        $candName  = $record->candidate->knight->rname ?? ('#' . $record->fkeycandidate);

        $record->forceFill([
            'activeflg' => 0,
            'delflg'    => 1,
            'lstmdby'   => auth()->user()->pkey,
        ])->save();

        $this->logChange($election, 'Record removed: /u/' . $actorName . ' ' . $record->action . ' /u/' . $candName . '.');

        return $this->back('Record removed.', 'success');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Only the full EA may edit, and only while the election is in an editable phase.
     * Returns a redirect when locked, or null when editing is allowed.
     */
    protected function denyIfLocked(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ! $eaRecord || $eaRecord->is_assistant) {
            abort(403, 'Only the full Election Administrator can change candidates and nominations.');
        }

        if (! in_array($election->phase, self::EDITABLE_PHASES, true)) {
            return $this->back('Candidates and nominations are locked once voting opens.', 'error');
        }

        return null;
    }

    protected function back(string $message, string $type)
    {
        return redirect()->route('election.candidates')->with($type, $message);
    }

    protected function findActiveKnight(int $pkey): ?Knight
    {
        return Knight::withoutGlobalScopes()
            ->where('pkey', $pkey)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->first();
    }

    protected function isElectionAdministrator(Election $election, int $knightPkey): bool
    {
        return ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knightPkey)
            ->exists();
    }

    protected function oathError(Knight $knight): ?string
    {
        $oath = Oath::currentYearForKnight((int) $knight->pkey);

        if (! $oath || ! $oath->verified) {
            return '/u/' . $knight->rname . ' needs a verified ' . Oath::currentOathYear()
                . ' oath before being accepted as a candidate.';
        }

        return null;
    }

    /** Roster changes go in the phase log (from = to = current phase), like pause/resume. */
    protected function logChange(Election $election, string $note): void
    {
        ElectionPhaseLog::create([
            'fkeyelection'    => $election->pkey,
            'from_phase'      => $election->phase,
            'to_phase'        => $election->phase,
            'transitioned_by' => auth()->user()->pkey,
            'note'            => $note,
        ]);
    }
}