<?php

namespace App\Services;

use App\Model\Election;
use App\Model\ElectionAdministrator;
use App\Model\ElectionAuditLog;
use App\Model\ElectionCandidate;
use App\Model\ElectionKeyArchive;
use App\Model\ElectionPhaseLog;
use App\Model\ElectionVote;
use App\Model\ElectionVoteAudit;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElectionService
{
    /** Minimum EA passphrase length (enforced by the controller). */
    const MIN_PASSPHRASE_LENGTH = 16;

    // -------------------------------------------------------------------------
    // Key management (sealed-box keypair; private key wrapped by EA passphrase)
    // -------------------------------------------------------------------------

    /**
     * Generate a new keypair. The public key is stored in the clear; the
     * private key is wrapped with a key derived from the passphrase (Argon2id).
     * Returns base64 strings ready to store on the election row.
     */
    public function generateKeys(string $passphrase): array
    {
        $keypair = sodium_crypto_box_keypair();
        $public  = sodium_crypto_box_publickey($keypair);
        $secret  = sodium_crypto_box_secretkey($keypair);

        $salt    = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
        $wrapKey = $this->deriveWrapKey($passphrase, $salt);
        $nonce   = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $wrapped = $nonce . sodium_crypto_secretbox($secret, $nonce, $wrapKey);

        $result = [
            'public_key'          => base64_encode($public),
            'wrapped_private_key' => base64_encode($wrapped),
            'key_salt'            => base64_encode($salt),
        ];

        sodium_memzero($secret);
        sodium_memzero($wrapKey);
        sodium_memzero($keypair);

        return $result;
    }

    protected function deriveWrapKey(string $passphrase, string $salt): string
    {
        return sodium_crypto_pwhash(
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
            $passphrase,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );
    }

    /**
     * Unwrap the election keypair with the EA passphrase.
     * Returns the raw keypair (secret+public) or null if the passphrase is
     * wrong or keys were never generated. Call ONCE per request, not per ballot.
     */
    public function unwrapKeypair(Election $election, string $passphrase): ?string
    {
        if (! $election->public_key || ! $election->wrapped_private_key || ! $election->key_salt) {
            return null;
        }

        try {
            $salt    = base64_decode($election->key_salt, true);
            $wrapped = base64_decode($election->wrapped_private_key, true);
            $public  = base64_decode($election->public_key, true);

            if ($salt === false || $wrapped === false || $public === false) {
                return null;
            }

            $nonce = substr($wrapped, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $box   = substr($wrapped, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

            $wrapKey = $this->deriveWrapKey($passphrase, $salt);
            $secret  = sodium_crypto_secretbox_open($box, $nonce, $wrapKey);
            sodium_memzero($wrapKey);

            if ($secret === false) {
                return null;
            }

            $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($secret, $public);
            sodium_memzero($secret);

            return $keypair;
        } catch (\Throwable $e) {
            Log::error('unwrapKeypair failed for election ' . $election->pkey . ': ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPassphrase(Election $election, string $passphrase): bool
    {
        $keypair = $this->unwrapKeypair($election, $passphrase);
        if ($keypair === null) {
            return false;
        }
        sodium_memzero($keypair);
        return true;
    }

    // -------------------------------------------------------------------------
    // Ballot encryption (needs only the PUBLIC key — no passphrase at vote time)
    // -------------------------------------------------------------------------

    /**
     * Seal a ballot to the election public key. The election and knight IDs are
     * bound inside the payload so a row moved or swapped in the DB fails to
     * validate at decrypt time.
     */
    public function encryptBallot(array $rankings, Election $election, int $knightPkey): string
    {
        $public = base64_decode((string) $election->public_key, true);

        if ($public === false || strlen($public) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
            throw new \RuntimeException('Election encryption keys are not initialised.');
        }

        $clean = [];
        foreach ($rankings as $candidatePkey => $rank) {
            $clean[(int) $candidatePkey] = (int) $rank;
        }

        $plain = json_encode([
            'v'        => 1,
            'e'        => (int) $election->pkey,
            'k'        => $knightPkey,
            'rankings' => $clean,
        ], JSON_FORCE_OBJECT);

        return base64_encode(sodium_crypto_box_seal($plain, $public));
    }

    /**
     * Open one ballot with an already-unwrapped keypair.
     * Returns [candidate_pkey => rank] or null on any failure or binding mismatch.
     */
    public function decryptBallot(string $encrypted, string $keypair, int $electionPkey, int $knightPkey): ?array
    {
        try {
            $sealed = base64_decode($encrypted, true);
            if ($sealed === false) {
                return null;
            }

            $plain = sodium_crypto_box_seal_open($sealed, $keypair);
            if ($plain === false) {
                return null;
            }

            $data = json_decode($plain, true);

            if (! is_array($data)
                || ($data['e'] ?? null) !== $electionPkey
                || ($data['k'] ?? null) !== $knightPkey
                || ! is_array($data['rankings'] ?? null)) {
                Log::warning('Ballot binding mismatch for knight ' . $knightPkey . ' in election ' . $electionPkey);
                return null;
            }

            $out = [];
            foreach ($data['rankings'] as $candidatePkey => $rank) {
                $out[(int) $candidatePkey] = (int) $rank;
            }
            return $out;
        } catch (\Throwable $e) {
            Log::error('Ballot decryption failed for knight ' . $knightPkey . ': ' . $e->getMessage());
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Opening voting (keys + phase change are one transaction)
    // -------------------------------------------------------------------------

    /**
     * Generate election keys and advance debate -> voting.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function startVoting(Election $election, string $passphrase, int $by, ?string $note = null): array
    {
        if ($election->phase !== 'debate') {
            return ['ok' => false, 'error' => 'Voting can only be opened from the debate phase.'];
        }

        if ($election->public_key) {
            return ['ok' => false, 'error' => 'Encryption keys already exist for this election.'];
        }

        if (strlen($passphrase) < self::MIN_PASSPHRASE_LENGTH) {
            return ['ok' => false, 'error' => 'Passphrase must be at least ' . self::MIN_PASSPHRASE_LENGTH . ' characters.'];
        }

        $accepted = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->count();

        if ($accepted < 2) {
            return ['ok' => false, 'error' => 'At least two accepted candidates are required.'];
        }

        $ea = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('is_assistant', 0)
            ->first();

        if (! $ea) {
            return ['ok' => false, 'error' => 'No Election Administrator is appointed.'];
        }

        // The audit DM path must have been proven for the CURRENT EA.
        if (! $election->audit_dm_verified_at
            || Carbon::parse($election->audit_dm_verified_at)->lt($ea->appointed_at)) {
            return ['ok' => false, 'error' => 'Send and confirm a test audit DM before opening voting.'];
        }

        $keys = $this->generateKeys($passphrase);

        DB::transaction(function () use ($election, $keys, $by, $note) {
            $election->forceFill($keys + [
                'key_created_at' => now(),
                'voting_paused'  => false,
                'lstmdby'        => $by,
            ])->save();

            $this->advancePhase($election, $by, $note ?: 'Voting opened; encryption keys generated.');
        });

        return ['ok' => true, 'error' => null];
    }

    // -------------------------------------------------------------------------
    // Recording a ballot — FAIL-CLOSED on audit DM failure
    // -------------------------------------------------------------------------

    /**
     * Encrypt and store a ballot and deliver the audit DM in one transaction.
     * If the DM cannot be delivered the whole ballot is rolled back.
     *
     * Returns ['ok' => bool, 'code' => 'recorded'|'duplicate'|'audit_dm'|'error', 'error' => string|null]
     */
    public function recordBallot(Election $election, $knight, array $rankings): array
    {
        $clean = [];
        foreach ($rankings as $candidatePkey => $rank) {
            $clean[(int) $candidatePkey] = (int) $rank;
        }

        try {
            $encrypted = $this->encryptBallot($clean, $election, (int) $knight->pkey);
        } catch (\Throwable $e) {
            Log::error('recordBallot: encryption failed: ' . $e->getMessage());
            return ['ok' => false, 'code' => 'error', 'error' => 'Ballot could not be encrypted.'];
        }

        $dmError = null;

        try {
            DB::transaction(function () use ($election, $knight, $clean, $encrypted, &$dmError) {
                ElectionVote::create([
                    'fkeyelection'     => $election->pkey,
                    'fkeyknight'       => $knight->pkey,
                    'encrypted_ballot' => $encrypted,
                    'submitted_at'     => now(),
                    'valid'            => true,
                ]);

                $this->logVoteAudit($election->pkey, $knight->pkey, 'submitted', $knight->pkey);

                $dmError = $this->sendAuditDm($election, $knight, $clean);

                if ($dmError !== null) {
                    throw new \RuntimeException('audit-dm-failed');
                }

                ElectionAuditLog::create([
                    'fkeyelection' => $election->pkey,
                    'fkeyknight'   => $knight->pkey,
                    'delivered'    => true,
                    'delivered_at' => now(),
                ]);
            });
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return ['ok' => false, 'code' => 'duplicate', 'error' => 'You have already submitted a ballot.'];
            }
            Log::error('recordBallot: DB error for knight ' . $knight->pkey . ': ' . $e->getMessage());
            return ['ok' => false, 'code' => 'error', 'error' => 'Ballot could not be recorded.'];
        } catch (\Throwable $e) {
            if ($dmError !== null) {
                // Ballot was rolled back. Record the failed attempt OUTSIDE the transaction.
                Log::warning('recordBallot: audit DM failed for knight ' . $knight->pkey . ': ' . $dmError);
                try {
                    ElectionAuditLog::create([
                        'fkeyelection' => $election->pkey,
                        'fkeyknight'   => $knight->pkey,
                        'delivered'    => false,
                        'delivered_at' => null,
                    ]);
                } catch (\Throwable $inner) {
                    Log::error('recordBallot: could not log failed DM: ' . $inner->getMessage());
                }
                return ['ok' => false, 'code' => 'audit_dm', 'error' => 'Audit DM could not be delivered.'];
            }

            Log::error('recordBallot: unexpected error for knight ' . $knight->pkey . ': ' . $e->getMessage());
            return ['ok' => false, 'code' => 'error', 'error' => 'Ballot could not be recorded.'];
        }

        return ['ok' => true, 'code' => 'recorded', 'error' => null];
    }

    // -------------------------------------------------------------------------
    // Audit DM delivery (via Squire Bot webhook)
    // -------------------------------------------------------------------------

    /**
     * Send the audit DM for a ballot. Returns null on success, or an error string.
     */
    protected function sendAuditDm(Election $election, $knight, array $rankings): ?string
    {
        $ea = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('is_assistant', 0)
            ->with('knight')
            ->first();

        if (! $ea || ! $ea->knight) {
            return 'No Election Administrator found.';
        }

        asort($rankings);
        $rankingString = implode(',', array_keys($rankings));

        $payload = implode("\n", [
            '**April Knights — Vote Audit**',
            'Election: ' . $election->election_year,
            'Knight ID: ' . $knight->pkey,
            'Rankings: ' . $rankingString,
            'Submitted: ' . now()->utc()->format('Y-m-d H:i:s') . ' UTC',
        ]);

        return $this->postToBot((string) $ea->knight->discordid, $payload, (bool) $ea->silent_audit_dms);
    }

    /**
     * Send a test DM to the full EA. On success, stamps audit_dm_verified_at.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function sendTestAuditDm(Election $election): array
    {
        $ea = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('is_assistant', 0)
            ->with('knight')
            ->first();

        if (! $ea || ! $ea->knight) {
            return ['ok' => false, 'error' => 'No Election Administrator is appointed.'];
        }

        $payload = implode("\n", [
            '**April Knights — Audit DM Test**',
            'Election: ' . $election->election_year,
            'If you can read this, audit DMs can reach you. No action needed.',
        ]);

        $error = $this->postToBot((string) $ea->knight->discordid, $payload, false);

        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }

        $election->forceFill(['audit_dm_verified_at' => now()])->save();

        return ['ok' => true, 'error' => null];
    }

    /**
     * POST to the bot webhook with one retry. Returns null on success, or an error string.
     */
    protected function postToBot(string $discordId, string $payload, bool $silent): ?string
    {
        $url    = config('services.squire_bot_webhook_url');
        $secret = config('services.squire_bot_webhook_secret');

        if (! $url || ! $secret) {
            return 'Bot webhook is not configured.';
        }

        if ($discordId === '' || $discordId === '0') {
            return 'The Election Administrator has no Discord ID on file.';
        }

        $last = 'unknown error';

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::timeout(6)
                    ->asJson()
                    ->post(rtrim($url, '/') . '/webhook/election-audit', [
                        'discordid' => $discordId,
                        'payload'   => $payload,
                        'silent'    => $silent,
                        'token'     => $secret,
                    ]);

                if ($response->successful() && $response->json('ok') === true) {
                    return null;
                }

                $last = 'bot returned HTTP ' . $response->status();
            } catch (\Throwable $e) {
                $last = $e->getMessage();
            }

            usleep(300000);
        }

        return $last;
    }

    public function failedAuditDmCount(Election $election): int
    {
        return ElectionAuditLog::where('fkeyelection', $election->pkey)
            ->where('delivered', 0)
            ->count();
    }

    // -------------------------------------------------------------------------
    // Ranked choice calculation
    // -------------------------------------------------------------------------

    /**
     * Returns:
     * [
     *   'rounds' => [ ['round'=>1,'counts'=>[candidate_pkey=>n],'eliminated'=>[pkeys]|null,'winner'=>pkey|null,'total'=>int], ... ],
     *   'winner' => candidate_pkey|null,
     *   'error'  => string|null,
     *   'fail_count' => int,
     * ]
     */
    public function calculateResults(Election $election, string $passphrase): array
    {
        $keypair = $this->unwrapKeypair($election, $passphrase);

        if ($keypair === null) {
            return [
                'rounds' => [], 'winner' => null, 'fail_count' => 0,
                'error'  => 'Passphrase incorrect, or encryption keys were never generated for this election.',
            ];
        }

        $votes = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->get();

        if ($votes->isEmpty()) {
            sodium_memzero($keypair);
            return ['rounds' => [], 'winner' => null, 'fail_count' => 0, 'error' => 'No valid votes found.'];
        }

        // Decrypt every ballot into an ordered preference list: [1st_pkey, 2nd_pkey, ...]
        $ballots   = [];
        $failCount = 0;

        foreach ($votes as $vote) {
            $rankings = $this->decryptBallot(
                $vote->encrypted_ballot,
                $keypair,
                (int) $election->pkey,
                (int) $vote->fkeyknight
            );

            if ($rankings === null) {
                $failCount++;
                continue;
            }

            asort($rankings);
            $ballots[] = array_keys($rankings);
        }

        sodium_memzero($keypair);

        if (empty($ballots)) {
            return [
                'rounds' => [], 'winner' => null, 'fail_count' => $failCount,
                'error'  => 'All ballots failed decryption.',
            ];
        }

        $candidatePkeys = ElectionCandidate::where('fkeyelection', $election->pkey)
            ->where('status', 'accepted')
            ->pluck('pkey')
            ->map(fn ($p) => (int) $p)
            ->all();

        $eliminated = [];
        $rounds     = [];
        $roundNum   = 1;

        while (true) {
            $counts = [];
            foreach ($candidatePkeys as $pk) {
                if (! in_array($pk, $eliminated, true)) {
                    $counts[$pk] = 0;
                }
            }

            foreach ($ballots as $ballot) {
                foreach ($ballot as $pk) {
                    if (isset($counts[$pk])) {
                        $counts[$pk]++;
                        break;
                    }
                }
            }

            $total = array_sum($counts);

            if ($total === 0) {
                $rounds[] = ['round' => $roundNum, 'counts' => $counts, 'eliminated' => null, 'winner' => null, 'total' => 0];
                return [
                    'rounds' => $rounds, 'winner' => null, 'fail_count' => $failCount,
                    'error'  => 'No votes could be allocated. Possible exhausted ballots.',
                ];
            }

            // NOTE: strictly greater than 50% of this round's continuing ballots.
            foreach ($counts as $pk => $count) {
                if ($count / $total > 0.5) {
                    $rounds[] = ['round' => $roundNum, 'counts' => $counts, 'eliminated' => null, 'winner' => $pk, 'total' => $total];
                    return ['rounds' => $rounds, 'winner' => $pk, 'error' => null, 'fail_count' => $failCount];
                }
            }

            // No winner — eliminate the lowest. A tie for last eliminates all tied candidates.
            $min                 = min($counts);
            $eliminatedThisRound = array_keys(array_filter($counts, fn ($c) => $c === $min));

            $rounds[] = [
                'round' => $roundNum, 'counts' => $counts,
                'eliminated' => $eliminatedThisRound, 'winner' => null, 'total' => $total,
            ];

            foreach ($eliminatedThisRound as $e) {
                $eliminated[] = $e;
            }

            if (count($eliminated) >= count($candidatePkeys)) {
                return [
                    'rounds' => $rounds, 'winner' => null, 'fail_count' => $failCount,
                    'error'  => 'All candidates eliminated without a winner. Manual review required.',
                ];
            }

            $roundNum++;
        }
    }

    // -------------------------------------------------------------------------
    // Phase advancement
    // -------------------------------------------------------------------------

    public function advancePhase(Election $election, int $transitionedBy, ?string $note = null): void
    {
        $phases  = Election::PHASES;
        $current = array_search($election->phase, $phases);

        if ($current === false || $current >= count($phases) - 1) {
            return;
        }

        $fromPhase = $election->phase;
        $toPhase   = $phases[$current + 1];

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

    // -------------------------------------------------------------------------
    // Key archive (post-election, server-side decryption of past cycles)
    // -------------------------------------------------------------------------

    /**
     * Store the passphrase encrypted with the APP key. After this, the app
     * key + database can decrypt this election's ballots. Verifies the
     * passphrase first. Returns ['ok' => bool, 'error' => string|null].
     */
    public function archiveKey(Election $election, string $passphrase, int $archivedBy, ?string $note = null): array
    {
        if (ElectionKeyArchive::where('fkeyelection', $election->pkey)->exists()) {
            return ['ok' => false, 'error' => 'A key is already archived for this election.'];
        }

        if (! $this->verifyPassphrase($election, $passphrase)) {
            return ['ok' => false, 'error' => 'Passphrase incorrect.'];
        }

        ElectionKeyArchive::create([
            'fkeyelection' => $election->pkey,
            'archived_key' => encrypt($passphrase),
            'archived_by'  => $archivedBy,
            'archived_at'  => now(),
            'note'         => $note,
        ]);

        return ['ok' => true, 'error' => null];
    }

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

    // -------------------------------------------------------------------------
    // Audit
    // -------------------------------------------------------------------------

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

    protected function normalizeRankings(string $rankings): string
    {
        return preg_replace('/\s+/', '', trim($rankings));
    }

    /**
     * Parse raw pasted audit DM text into rows for auditCompare().
     * Matches "Knight ID: N" followed by "Rankings: a,b,c".
     */
    public function parseAuditText(string $text): array
    {
        preg_match_all(
            '/Knight ID:[ \t]*(\d+)[ \t]*\R+[ \t]*Rankings:[ \t]*([0-9][0-9, \t]*)/i',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        $rows = [];
        foreach ($matches as $m) {
            $rows[] = [
                'knight_pkey' => (int) $m[1],
                'rankings'    => $this->normalizeRankings($m[2]),
            ];
        }

        return $rows;
    }

    /**
     * Compare audit rows (from CSV or parsed DM text) against stored ballots.
     * Rows are ['knight_pkey' => int, 'rankings' => 'a,b,c']. If a knight appears
     * more than once (retry after a failed submission), the LAST row wins.
     * Returns a list of discrepancies; an empty list means the audit passed.
     */
    public function auditCompare(Election $election, string $passphrase, array $rows): array
    {
        $keypair = $this->unwrapKeypair($election, $passphrase);

        if ($keypair === null) {
            return [[
                'knight_pkey' => 0,
                'issue'       => 'Passphrase incorrect — the audit could not run.',
                'csv'         => null,
                'db'          => null,
            ]];
        }

        $byKnight = [];
        foreach ($rows as $row) {
            $k = (int) ($row['knight_pkey'] ?? 0);
            if ($k > 0) {
                $byKnight[$k] = $this->normalizeRankings((string) ($row['rankings'] ?? ''));
            }
        }

        $discrepancies = [];

        foreach ($byKnight as $knightPkey => $dmRankings) {
            $vote = ElectionVote::where('fkeyelection', $election->pkey)
                ->where('fkeyknight', $knightPkey)
                ->first();

            if (! $vote) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Audit DM exists but there is no ballot in the database.',
                    'csv'         => $dmRankings,
                    'db'          => null,
                ];
                continue;
            }

            $dbRankings = $this->decryptBallot(
                $vote->encrypted_ballot,
                $keypair,
                (int) $election->pkey,
                $knightPkey
            );

            if ($dbRankings === null) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Database ballot failed decryption or binding check.',
                    'csv'         => $dmRankings,
                    'db'          => null,
                ];
                continue;
            }

            asort($dbRankings);
            $dbString = implode(',', array_keys($dbRankings));

            if ($dmRankings !== $dbString) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Ballot mismatch between audit DM and database.',
                    'csv'         => $dmRankings,
                    'db'          => $dbString,
                ];
            } elseif (! $vote->valid) {
                $discrepancies[] = [
                    'knight_pkey' => $knightPkey,
                    'issue'       => 'Ballot matches the DM but is marked invalid in the database.',
                    'csv'         => $dmRankings,
                    'db'          => $dbString,
                ];
            }
        }

        // Ballots in the database with no audit DM
        $dbVotes = ElectionVote::where('fkeyelection', $election->pkey)
            ->where('valid', 1)
            ->get();

        foreach ($dbVotes as $vote) {
            if (! isset($byKnight[(int) $vote->fkeyknight])) {
                $discrepancies[] = [
                    'knight_pkey' => $vote->fkeyknight,
                    'issue'       => 'Ballot exists in the database with no matching audit DM.',
                    'csv'         => null,
                    'db'          => 'present',
                ];
            }
        }

        sodium_memzero($keypair);

        return $discrepancies;
    }
}