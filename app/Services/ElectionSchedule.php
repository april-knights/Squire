<?php

namespace App\Services;

use App\Model\Election;
use App\Model\ElectionPhaseLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ElectionSchedule
{
    /** security.pkey values allowed to manage the schedule: Admin, Grandmaster. */
    const MANAGER_SECURITY_PKEYS = [1, 2];

    /** krank.pkey values allowed to manage the schedule: Grandmaster, Archmage. */
    const MANAGER_RANK_PKEYS = [1, 6];

    /** Statute: the EA may extend voting up to this many days past the scheduled close. */
    const MAX_EXTENSION_DAYS = 3;

    /** Statutory dates in timeline order. Stored as UTC. */
    const FIELDS = [
        'ea_appoint_by'        => 'Election Administrator appointed by',
        'nominations_open_at'  => 'Nominations open',
        'nominations_close_at' => 'Nominations close',
        'voting_opens_at'      => 'Voting opens',
        'voting_closes_at'     => 'Voting closes',
    ];

    // -------------------------------------------------------------------------
    // Access
    // -------------------------------------------------------------------------

    public static function canManage($knight): bool
    {
        if (! $knight) {
            return false;
        }

        return in_array((int) $knight->security, self::MANAGER_SECURITY_PKEYS, true)
            || in_array((int) ($knight->rank->pkey ?? 0), self::MANAGER_RANK_PKEYS, true);
    }

    /** The active election, otherwise the most recent one. */
    public static function currentElection(): ?Election
    {
        return Election::active()
            ?: Election::orderByDesc('election_year')->orderByDesc('pkey')->first();
    }

    // -------------------------------------------------------------------------
    // Reading
    // -------------------------------------------------------------------------

    public static function forElection(Election $election): array
    {
        $items = [];
        foreach (self::FIELDS as $key => $label) {
            $items[] = ['key' => $key, 'label' => $label, 'at' => self::parse($election->{$key})];
        }

        $close    = self::parse($election->voting_closes_at);
        $extended = self::parse($election->voting_extended_until);

        return [
            'items'           => $items,
            'extended_until'  => $extended,
            'effective_close' => $extended ?: $close,
            'max_extension'   => $close ? $close->copy()->addDays(self::MAX_EXTENSION_DAYS) : null,
            'any_set'         => collect($items)->contains(fn ($i) => $i['at'] !== null),
        ];
    }

    /**
     * Suggested defaults from the spec's calculation: nominations Jan 7-15,
     * voting Jan 22 - Feb 5, all at 05:00 UTC (midnight Eastern in winter).
     * These pre-fill the form only; nothing is saved until an editor saves.
     */
    public static function suggest(Election $election): array
    {
        $open = Carbon::create((int) $election->election_year, 1, 7, 5, 0, 0, 'UTC');
        $f    = 'Y-m-d\TH:i';

        return [
            'ea_appoint_by'        => $open->format($f),
            'nominations_open_at'  => $open->format($f),
            'nominations_close_at' => $open->copy()->addDays(8)->format($f),
            'voting_opens_at'      => $open->copy()->addDays(15)->format($f),
            'voting_closes_at'     => $open->copy()->addDays(29)->format($f),
        ];
    }

    // -------------------------------------------------------------------------
    // Saving the statutory dates (Admin / Grandmaster / Archmage)
    // -------------------------------------------------------------------------

    public static function save(Election $election, array $input, int $by): array
    {
        $new = [];

        foreach (self::FIELDS as $key => $label) {
            $raw = trim((string) ($input[$key] ?? ''));

            if ($raw === '') {
                $new[$key] = null;
                continue;
            }

            $parsed = self::parseInput($raw);

            if (! $parsed) {
                return ['ok' => false, 'error' => $label . ' is not a valid date and time.', 'changed' => 0];
            }

            $new[$key] = $parsed;
        }

        $rules = [
            ['ea_appoint_by',        'nominations_open_at',  'lte', 'The Election Administrator must be appointed by the time nominations open.'],
            ['nominations_open_at',  'nominations_close_at', 'lt',  'Nominations must close after they open.'],
            ['nominations_close_at', 'voting_opens_at',      'lte', 'Voting cannot open before nominations close.'],
            ['voting_opens_at',      'voting_closes_at',     'lt',  'Voting must close after it opens.'],
        ];

        foreach ($rules as [$a, $b, $op, $message]) {
            if ($new[$a] && $new[$b] && ! $new[$a]->{$op}($new[$b])) {
                return ['ok' => false, 'error' => $message, 'changed' => 0];
            }
        }

        // Once the EA has extended voting, the scheduled close is fixed.
        $currentClose = self::parse($election->voting_closes_at);
        $extended     = self::parse($election->voting_extended_until);

        if ($extended && self::stamp($new['voting_closes_at']) !== self::stamp($currentClose)) {
            return [
                'ok'      => false,
                'error'   => 'The scheduled voting close cannot change after the Election Administrator has extended voting.',
                'changed' => 0,
            ];
        }

        $logs    = [];
        $changed = 0;

        foreach (self::FIELDS as $key => $label) {
            $old = self::parse($election->{$key});

            if (self::stamp($old) === self::stamp($new[$key])) {
                continue;
            }

            $election->forceFill([$key => self::stamp($new[$key])]);
            $logs[] = 'Schedule: ' . $label . ' '
                . ($new[$key] ? 'set to ' . self::fmt($new[$key]) : 'cleared')
                . ' (was ' . self::fmt($old) . ').';
            $changed++;
        }

        if ($changed) {
            DB::transaction(function () use ($election, $by, $logs) {
                $election->forceFill(['lstmdby' => $by])->save();
                foreach ($logs as $note) {
                    self::log($election, $by, $note);
                }
            });
        }

        return ['ok' => true, 'error' => null, 'changed' => $changed];
    }

    // -------------------------------------------------------------------------
    // Registration thread link
    // -------------------------------------------------------------------------

    public static function saveRegistrationThread(Election $election, ?string $url, int $by): array
    {
        $url = trim((string) $url);
        $old = $election->registration_thread_url;

        if ($url === '') {
            if (! $old) {
                return ['ok' => true, 'error' => null, 'message' => 'No change.'];
            }

            DB::transaction(function () use ($election, $by) {
                $election->forceFill([
                    'registration_thread_url' => null,
                    'registration_post_id'    => null,
                    'lstmdby'                 => $by,
                ])->save();
                self::log($election, $by, 'Registration thread link cleared.');
            });

            return ['ok' => true, 'error' => null, 'message' => 'Registration thread link cleared.'];
        }

        if (strlen($url) > 500
            || ! preg_match('#^https?://(www\.|old\.|new\.)?reddit\.com/r/[A-Za-z0-9_]+/comments/([a-z0-9]+)#i', $url, $m)) {
            return ['ok' => false, 'error' => 'That does not look like a Reddit post link (reddit.com/r/.../comments/...).'];
        }

        if ($url === $old) {
            return ['ok' => true, 'error' => null, 'message' => 'No change.'];
        }

        DB::transaction(function () use ($election, $by, $url, $m) {
            $election->forceFill([
                'registration_thread_url' => $url,
                'registration_post_id'    => strtolower($m[2]),
                'lstmdby'                 => $by,
            ])->save();
            self::log($election, $by, 'Registration thread link set.');
        });

        return ['ok' => true, 'error' => null, 'message' => 'Registration thread link saved.'];
    }

    // -------------------------------------------------------------------------
    // Voting extension (Election Administrator, capped by statute)
    // -------------------------------------------------------------------------

    public static function extendVoting(Election $election, string $raw, ?string $note, int $by): array
    {
        if ($election->phase !== 'voting') {
            return ['ok' => false, 'error' => 'Voting can only be extended while it is open.'];
        }

        $close = self::parse($election->voting_closes_at);

        if (! $close) {
            return ['ok' => false, 'error' => 'No scheduled voting close is set. Ask an Admin, the Grandmaster or an Archmage to set the schedule first.'];
        }

        $until = self::parseInput(trim($raw));

        if (! $until) {
            return ['ok' => false, 'error' => 'The new close is not a valid date and time.'];
        }

        $effective = self::parse($election->voting_extended_until) ?: $close;
        $max       = $close->copy()->addDays(self::MAX_EXTENSION_DAYS);

        if ($until->lte($effective)) {
            return ['ok' => false, 'error' => 'The new close must be later than the current close (' . self::fmt($effective) . ').'];
        }

        if ($until->gt($max)) {
            return [
                'ok'    => false,
                'error' => 'The statute allows at most ' . self::MAX_EXTENSION_DAYS
                    . ' days past the scheduled close (latest: ' . self::fmt($max) . ').',
            ];
        }

        $note = trim((string) $note);
        $text = 'Voting close extended to ' . self::fmt($until) . ' (scheduled close was ' . self::fmt($close) . ').'
            . ($note !== '' ? ' Reason: ' . mb_substr($note, 0, 500) : '');

        DB::transaction(function () use ($election, $by, $until, $text) {
            $election->forceFill([
                'voting_extended_until' => self::stamp($until),
                'lstmdby'               => $by,
            ])->save();
            self::log($election, $by, $text);
        });

        return ['ok' => true, 'error' => null];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected static function parse($raw): ?Carbon
    {
        return $raw ? Carbon::parse($raw, 'UTC') : null;
    }

    /** Parses datetime-local style input as UTC and rejects overflow dates like Feb 30. */
    protected static function parseInput(string $raw): ?Carbon
    {
        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $raw, 'UTC');
            } catch (\Throwable $e) {
                continue;
            }

            if ($dt && $dt->format($format) === $raw) {
                return $dt->setTimezone('UTC');
            }
        }

        return null;
    }

    protected static function stamp(?Carbon $c): ?string
    {
        return $c ? $c->format('Y-m-d H:i:s') : null;
    }

    protected static function fmt(?Carbon $c): string
    {
        return $c ? $c->format('Y-m-d H:i') . ' UTC' : 'not set';
    }

    protected static function log(Election $election, int $by, string $note): void
    {
        ElectionPhaseLog::create([
            'fkeyelection'    => $election->pkey,
            'from_phase'      => $election->phase,
            'to_phase'        => $election->phase,
            'transitioned_by' => $by,
            'note'            => $note,
        ]);
    }
}