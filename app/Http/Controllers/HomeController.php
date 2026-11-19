<?php

namespace App\Http\Controllers;

use App\Model\Battalion;
use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionCandidate;
use App\Model\ElectionRegistration;
use App\Model\ElectionVote;
use App\Model\Knight;
use App\Model\Oath;

class HomeController extends Controller
{
    /** rval at or below which a knight counts as a secondary officer (Captain, Lieutenant, Sergeant, etc.): names only if the EA allows. */
    const OFFICER_MAX_RVAL = 8;

    /** Rank value used when a knight has no rank assigned. */
    const DEFAULT_RVAL = 99;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $knight   = auth()->user();
        $election = Election::active();

        // --- Oath status ---
        $oath     = Oath::currentYearForKnight($knight->pkey);
        $oathYear = Oath::currentOathYear();

        // --- Election status ---
        $registered     = false;
        $hasVoted       = false;
        $isEA           = false;
        $isAssistantEA  = false;
        $eaRecord       = null;
        $isCandidate    = false;
        $battalionStats = null;

        if ($election) {
            $registered = ElectionRegistration::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knight->pkey)
                ->where('activeflg', 1)
                ->where('delflg', 0)
                ->exists();

            $hasVoted = ElectionVote::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knight->pkey)
                ->exists();

            $eaRecord = ElectionAdministrator::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knight->pkey)
                ->first();

            $isEA          = $eaRecord && ! $eaRecord->is_assistant;
            $isAssistantEA = $eaRecord && $eaRecord->is_assistant;

            $isCandidate = ElectionCandidate::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knight->pkey)
                ->where('status', 'accepted')
                ->exists();

            // The names toggle belongs to the ELECTION's Election Administrator, not the viewer.
            $electionEA = ElectionAdministrator::where('fkeyelection', $election->pkey)
                ->where('is_assistant', 0)
                ->first();

            $officersSeeNames = (bool) ($electionEA->show_voter_names_secondary_officers ?? false);

            $rval          = (int) ($knight->rank->rval ?? self::DEFAULT_RVAL);
            $battalionPkey = $knight->batt;

            if ($battalionPkey && $election->phase !== 'complete') {
                $battalionStats = $this->getBattalionStats(
                    $election,
                    (int) $battalionPkey,
                    $knight,
                    $rval,
                    $isCandidate,
                    $officersSeeNames
                );
            }
        }

        return view('home', compact(
            'knight',
            'election',
            'oath',
            'oathYear',
            'registered',
            'hasVoted',
            'isEA',
            'isAssistantEA',
            'eaRecord',
            'isCandidate',
            'battalionStats'
        ));
    }

    /**
     * True if this knight is the leader, sec1 or sec2 of the given battalion.
     * The relationship methods are called explicitly, so this works whether or not
     * the battalion table also has same-named columns.
     */
    protected function isBattalionLeadership(int $battalionPkey, int $knightPkey): bool
    {
        $battalion = Battalion::find($battalionPkey);

        if (! $battalion) {
            return false;
        }

        $leadershipPkeys = array_filter([
            optional($battalion->leader()->first())->pkey,
            optional($battalion->sec1()->first())->pkey,
            optional($battalion->sec2()->first())->pkey,
        ]);

        return in_array($knightPkey, $leadershipPkeys);
    }

    /**
     * Battalion-scoped election stats for the viewing knight's OWN battalion only.
     *
     * level 'full'   : battalion leader/sec1/sec2, accepted candidates, and officers
     *                  (rval <= 8) when the EA allows it.
     *                  Sees oathed/registered/voted counts, plus a named voter list during voting.
     * level 'counts' : everyone else. Sees only the voted count, once voting is under way.
     */
    protected function getBattalionStats(
        Election $election,
        int $battalionPkey,
        Knight $viewer,
        int $rval,
        bool $isCandidate,
        bool $officersSeeNames
    ): ?array {
        $phase = $election->phase;

        $isLeader = $isCandidate
            || $this->isBattalionLeadership($battalionPkey, (int) $viewer->pkey)
            || ($rval <= self::OFFICER_MAX_RVAL && $officersSeeNames);

        if (! $isLeader && ! in_array($phase, ['voting', 'counting'], true)) {
            return null;
        }

        $members = Knight::withoutGlobalScopes()
            ->where('batt', $battalionPkey)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->get(['pkey', 'rname']);

        $battalionPkeys = $members->pluck('pkey')->all();
        $total          = count($battalionPkeys);

        if ($total === 0) {
            return null;
        }

        $oathYear = Oath::currentOathYear();

        $oathedPkeys = Oath::whereIn('fkeyknight', $battalionPkeys)
            ->where('oath_year', $oathYear)
            ->where('verified', 1)
            ->pluck('fkeyknight')
            ->all();

        $registeredPkeys = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->whereIn('fkeyknight', $battalionPkeys)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->pluck('fkeyknight')
            ->all();

        $votedPkeys = ElectionVote::where('fkeyelection', $election->pkey)
            ->whereIn('fkeyknight', $battalionPkeys)
            ->where('valid', 1)
            ->pluck('fkeyknight')
            ->all();

        // Eligible = verified oath AND registered. Only eligible members are listed by name.
        $eligiblePkeys = array_values(array_intersect($oathedPkeys, $registeredPkeys));

        $namedList = null;
        if ($isLeader && $phase === 'voting') {
            $namedList = $members
                ->filter(fn ($k) => in_array($k->pkey, $eligiblePkeys))
                ->map(fn ($k) => [
                    'rname' => $k->rname,
                    'voted' => in_array($k->pkey, $votedPkeys),
                ])
                ->sort(fn ($a, $b) => [$a['voted'], strtolower($a['rname'])] <=> [$b['voted'], strtolower($b['rname'])])
                ->values();
        }

        return [
            'level'      => $isLeader ? 'full' : 'counts',
            'phase'      => $phase,
            'total'      => $total,
            'oathed'     => count($oathedPkeys),
            'registered' => count($registeredPkeys),
            'eligible'   => count($eligiblePkeys),
            'voted'      => count($votedPkeys),
            'ineligible' => $total - count($eligiblePkeys),
            'named_list' => $namedList,
        ];
    }
}