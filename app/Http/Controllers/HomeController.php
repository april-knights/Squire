<?php

namespace App\Http\Controllers;

use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionRegistration;
use App\Model\ElectionVote;
use App\Model\ElectionCandidate;
use App\Model\Oath;
use App\Model\Knight;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
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
        $registered      = false;
        $hasVoted        = false;
        $isEA            = false;
        $isAssistantEA   = false;
        $eaRecord        = null;
        $isCandidate     = false;
        $battalionStats  = null;

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

            // Battalion stats for Commanders, FOs, and accepted candidates
            $showBattalionStats = $knight->krank->rval <= 5 || $isCandidate;

            if ($showBattalionStats && $knight->fkeybattalion) {
                $battalionStats = $this->getBattalionStats(
                    $election,
                    $knight->fkeybattalion,
                    $eaRecord
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

    protected function getBattalionStats(Election $election, int $battalionPkey, $eaRecord): array
    {
        // All active knights in this battalion
        $battalionKnights = Knight::withoutGlobalScopes()
            ->where('fkeybattalion', $battalionPkey)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->get(['pkey', 'kname', 'rname']);

        $battalionPkeys = $battalionKnights->pluck('pkey')->toArray();
        $total          = count($battalionPkeys);

        // Oathed this year
        $oathYear  = Oath::currentOathYear();
        $oathedPkeys = Oath::whereIn('fkeyknight', $battalionPkeys)
            ->where('oath_year', $oathYear)
            ->where('verified', 1)
            ->pluck('fkeyknight')
            ->toArray();

        // Registered
        $registeredPkeys = ElectionRegistration::where('fkeyelection', $election->pkey)
            ->whereIn('fkeyknight', $battalionPkeys)
            ->where('activeflg', 1)
            ->where('delflg', 0)
            ->pluck('fkeyknight')
            ->toArray();

        // Voted
        $votedPkeys = ElectionVote::where('fkeyelection', $election->pkey)
            ->whereIn('fkeyknight', $battalionPkeys)
            ->where('valid', 1)
            ->pluck('fkeyknight')
            ->toArray();

        // Show names toggle — EA setting or default on for Commander/FO
        $showNames = $eaRecord
            ? $eaRecord->show_voter_names_secondary_officers
            : true;

        // Build named voter list if names are shown
        $namedList = null;
        if ($showNames && $election->phase === 'voting') {
            $namedList = $battalionKnights->map(function ($k) use ($registeredPkeys, $votedPkeys) {
                return [
                    'pkey'       => $k->pkey,
                    'kname'      => $k->kname,
                    'rname'      => $k->rname,
                    'registered' => in_array($k->pkey, $registeredPkeys),
                    'voted'      => in_array($k->pkey, $votedPkeys),
                ];
            });
        }

        return [
            'total'            => $total,
            'oathed'           => count($oathedPkeys),
            'registered'       => count($registeredPkeys),
            'voted'            => count($votedPkeys),
            'show_names'       => $showNames,
            'named_list'       => $namedList,
            'phase'            => $election->phase,
        ];
    }
}