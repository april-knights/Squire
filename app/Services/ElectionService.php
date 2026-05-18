<?php

namespace App\Services;

use App\Model\Election;
use App\Model\ElectionVote;
use App\Model\ElectionCandidate;
use App\Model\ElectionPhaseLog;
use App\Model\ElectionVoteAudit;
use App\Model\ElectionKeyArchive;
use Illuminate\Support\Facades\Log;

class ElectionService
{
    /**
     * Derive an encryption key from the EA passphrase and voter knight pkey.
     * The knight pkey is salted in so identical ballots produce different ciphertext.
     */
    public function deriveKey(string $passphrase, int $knightPkey): string
    {
        return hash('sha256', $passphrase . '::' . $knightPkey);
    }

    /**
     * Encrypt a ballot array using the derived key.
     * Returns base64-encoded ciphertext.
     */
    public function encryptBallot(array $rankings, string $passphrase, int $knightPkey): string
    {
        $key    = $this->deriveKey($passphrase, $knightPkey);
        $iv     = random_bytes(16);
        $plain  = json_encode(['rankings' => $rankings]);

        $cipher = openssl_encrypt(
            $plain,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Prepend IV to ciphertext so we can recover it on decryption
        return base64_encode($iv . $cipher);
    }

    /**
     * Decrypt a single ballot.
     * Returns the rankings array or null if decryption fails.
     */
    public function decryptBallot(string $encryptedBallot, string $passphrase, int $knightPkey): ?array
    {
        try {
            $key     = $this->deriveKey($passphrase, $knightPkey);
            $decoded = base64_decode($encryptedBallot);
            $iv      = substr($decoded, 0, 16);
            $cipher  = substr($decoded, 16);

            $plain = openssl_decrypt(
                $cipher,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($plain === false) {
                return null;
            }

            $data = json_decode($plain, true);
            return $data['rankings'] ?? null;

        } catch (\Throwable $e) {
            Log::error('Ballot decryption failed for knight ' . $knightPkey . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Run the full ranked choice calculation for an election.
     * Returns a round-by-round breakdown and the winner if one exists.
     *
     * $passphrase — EA's passphrase for this election cycle
     *
     * Returns:
     * [
     *   'rounds' => [
     *     [
     *       'round' => 1,
     *       'counts' => [candidate_pkey => vote_count, ...],
     *       'eliminated' => candidate_pkey|null,
     *       'winner' => candidate_pkey|null,
     *       'total' => int,
     *     ],
     *     ...
     *   ],
     *   'winner' => candidate_pkey|null,
     *   'error'  => string|null,
     * ]
     */
    public function calculateResults(Election $election, string $passphrase): array
    {
        $votes = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->get();

        if ($votes->isEmpty()) {
            return ['rounds' => [], 'winner' => null, 'error' => 'No valid votes found.'];
        }

        // Decrypt all ballots into memory
        // Structure: [ [candidate_pkey => rank, ...], ... ]
        $ballots = [];
        $failCount = 0;

        foreach ($votes as $vote) {
            $rankings = $this->decryptBallot(
                $vote->encrypted_ballot,
                $passphrase,
                $vote->fkeyknight
            );

            if ($rankings === null) {
                $failCount++;
                continue;
            }

            // Convert to ordered preference list: [1st_choice_pkey, 2nd_choice_pkey, ...]
            asort($rankings); // sort by rank value ascending
            $ballots[] = array_keys($rankings);
        }

        if (empty($ballots)) {
            return [
                'rounds' => [],
                'winner' => null,
                'error'  => 'All ballots failed decryption. Verify passphrase.',
            ];
        }

        // Get active candidate pkeys for this election
        $activeCandidates = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->pluck('pkey')
            ->flip()
            ->map(fn() => true)
            ->toArray();

        $eliminated = [];
        $rounds     = [];
        $roundNum   = 1;

        while (true) {
            // Count first-choice votes for non-eliminated candidates
            $counts = [];
            foreach (array_keys($activeCandidates) as $candidatePkey) {
                if (! in_array($candidatePkey, $eliminated)) {
                    $counts[$candidatePkey] = 0;
                }
            }

            foreach ($ballots as $ballot) {
                // Find the first non-eliminated choice on this ballot
                foreach ($ballot as $candidatePkey) {
                    if (! in_array($candidatePkey, $eliminated) && isset($counts[$candidatePkey])) {
                        $counts[$candidatePkey]++;
                        break;
                    }
                }
            }

            $total = array_sum($counts);

            if ($total === 0) {
                $rounds[] = [
                    'round'      => $roundNum,
                    'counts'     => $counts,
                    'eliminated' => null,
                    'winner'     => null,
                    'total'      => 0,
                ];
                return [
                    'rounds'      => $rounds,
                    'winner'      => null,
                    'error'       => 'No votes could be allocated. Possible exhausted ballots.',
                    'fail_count'  => $failCount,
                ];
            }

            // Check for a winner
            foreach ($counts as $candidatePkey => $count) {
                if ($count / $total > 0.5) {
                    $rounds[] = [
                        'round'      => $roundNum,
                        'counts'     => $counts,
                        'eliminated' => null,
                        'winner'     => $candidatePkey,
                        'total'      => $total,
                    ];
                    return [
                        'rounds'     => $rounds,
                        'winner'     => $candidatePkey,
                        'error'      => null,
                        'fail_count' => $failCount,
                    ];
                }
            }

            // No winner — eliminate the candidate with the fewest votes
            $minVotes    = min($counts);
            $minCandidates = array_keys(array_filter($counts, fn($c) => $c === $minVotes));

            // Tie in last place — flag it, eliminate all tied (rare edge case)
            $eliminatedThisRound = count($minCandidates) > 1
                ? $minCandidates
                : [$minCandidates[0]];

            $rounds[] = [
                'round'      => $roundNum,
                'counts'     => $counts,
                'eliminated' => $eliminatedThisRound,
                'winner'     => null,
                'total'      => $total,
            ];

            foreach ($eliminatedThisRound as $e) {
                $eliminated[] = $e;
            }

            // Safety valve — should never hit this in practice
            if (count($eliminated) >= count($activeCandidates)) {
                return [
                    'rounds'     => $rounds,
                    'winner'     => null,
                    'error'      => 'All candidates eliminated without a winner. Manual review required.',
                    'fail_count' => $failCount,
                ];
            }

            $roundNum++;
        }
    }

    /**
     * Advance the election to the next phase and log the transition.
     */
    public function advancePhase(Election $election, int $transitionedBy, ?string $note = null): void
    {
        $phases  = Election::PHASES;
        $current = array_search($election->phase, $phases);

        if ($current === false || $current >= count($phases) - 1) {
            return;
        }

        $fromPhase = $election->phase;
        $toPhase   = $phases[$current + 1];

        // If leaving voting phase, pause voting and clear session key
        if ($fromPhase === 'voting') {
            $election->voting_paused = true;
        }

        $election->phase = $toPhase;
        $election->save();

        ElectionPhaseLog::create([
            'fkeyelection'    => $election->pkey,
            'from_phase'      => $fromPhase,
            'to_phase'        => $toPhase,
            'transitioned_by' => $transitionedBy,
            'note'            => $note,
        ]);
    }

    /**
     * Archive the EA's encryption key post-election.
     * The key is encrypted with the application key before storage.
     */
    public function archiveKey(Election $election, string $passphrase, int $archivedBy, ?string $note = null): void
    {
        ElectionKeyArchive::create([
            'fkeyelection' => $election->pkey,
            'archived_key' => encrypt($passphrase),
            'archived_by'  => $archivedBy,
            'archived_at'  => now(),
            'note'         => $note,
        ]);
    }

    /**
     * Retrieve and decrypt an archived key for a past election.
     * Only callable by Admin.
     */
    public function retrieveArchivedKey(Election $election): ?string
    {
        $archive = ElectionKeyArchive::where('fkeyelection', $election->pkey)->first();

        if (! $archive) {
            return null;
        }

        try {
            return decrypt($archive->archived_key);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve archived key for election ' . $election->pkey . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a vote audit action.
     */
    public function logVoteAudit(int $electionPkey, int $knightPkey, string $action, int $performedBy, ?string $note = null): void
    {
        ElectionVoteAudit::create([
            'fkeyelection' => $electionPkey,
            'fkeyknight'   => $knightPkey,
            'action'       => $action,
            'performed_by' => $performedBy,
            'note'         => $note,
        ]);
    }

    /**
     * Compare uploaded audit CSV rows against stored vote records.
     * CSV rows expected as array of ['knight_pkey' => int, 'rankings' => string]
     * Returns array of discrepancies.
     */
    public function auditCompare(Election $election, string $passphrase, array $csvRows): array
    {
        $discrepancies = [];

        foreach ($csvRows as $row) {
            $knightPkey = (int) $row['knight_pkey'];
            $csvRankings = $row['rankings']; // comma-separated candidate pkeys in rank order

            $vote = ElectionVote::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knightPkey)
                ->first();

            if (! $vote) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Vote exists in audit DMs but not in database.',
                    'csv'         => $csvRankings,
                    'db'          => null,
                ];
                continue;
            }

            $dbRankings = $this->decryptBallot(
                $vote->encrypted_ballot,
                $passphrase,
                $knightPkey
            );

            if ($dbRankings === null) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Database ballot failed decryption.',
                    'csv'         => $csvRankings,
                    'db'          => null,
                ];
                continue;
            }

            // Convert db rankings to ordered string for comparison
            asort($dbRankings);
            $dbRankingString = implode(',', array_keys($dbRankings));

            if (trim($csvRankings) !== trim($dbRankingString)) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Ballot mismatch between audit DM and database.',
                    'csv'         => $csvRankings,
                    'db'          => $dbRankingString,
                ];
            }
        }

        // Check for votes in DB with no matching DM entry
        $csvKnightPkeys = array_column($csvRows, 'knight_pkey');
        $dbVotes = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->get();

        foreach ($dbVotes as $vote) {
            if (! in_array($vote->fkeyknight, $csvKnightPkeys)) {
                $discrepancies[] = [
                    'knight_pkey' => $vote->fkeyknight,
                    'issue'       => 'Vote exists in database but has no matching audit DM entry.',
                    'csv'         => null,
                    'db'          => 'present',
                ];
            }
        }

        return $discrepancies;
    }
}