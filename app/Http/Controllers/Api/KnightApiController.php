<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Badge;
use App\Model\Knight;
use App\Model\KnightBadge;
use App\Model\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KnightApiController extends Controller
{
    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Find a knight by discordid or return a 404 JSON response.
     */
    private function findByDiscord(string $discordid): Knight
    {
        return Knight::withoutGlobalScopes()
            ->where('discordid', $discordid)
            ->firstOrFail();
    }

    /**
     * Safe knight data for API responses — never expose sensitive fields.
     * Omits: email, onote, rlimpact, bio, security, banned.
     */
    private function publicProfile(Knight $knight): array
    {
        return [
            'pkey'      => $knight->pkey,
            'knum'      => $knight->knum,
            'rname'     => $knight->rname,
            'dname'     => $knight->dname,
            'discordid' => $knight->discordid,
            'batt'      => $knight->batt,
            'batt_name' => $knight->battalion ? $knight->battalion->name : 'Unknown',
            'rnk'       => $knight->rnk,
            'rank_name' => $knight->getRankName(),
            'rank_val'  => $knight->getRankVal(),
        ];
    }

    // ---------------------------------------------------------------
    // POST /api/knight
    // Create a new knight from the Discord bot /newknight command.
    // ---------------------------------------------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'discordid'  => 'required|string|max:30|unique:knight,discordid',
            'rname'      => 'required|string|max:255|unique:knight,rname',
            'dname'      => 'required|string|max:255',
            'batt'       => 'required|integer|exists:battalion,pkey',
            'firstevent' => 'required|integer|exists:event,pkey',
        ]);

        $knight = DB::transaction(function () use ($validated) {
            $nextKnum = (Knight::withoutGlobalScopes()->max('knum') ?? 100000) + 1;

            $knight = new Knight([
                'knum'       => $nextKnum,
                'rname'      => $validated['rname'],
                'dname'      => $validated['dname'],
                'discordid'  => $validated['discordid'],
                'batt'       => $validated['batt'],
                'firstevent' => $validated['firstevent'],
                'rnk'        => Rank::DEFAULT_PROFILE_RANK_ID,  // Initiate = 13
                'security'   => 9,                               // Initiate security
                'crtsetid'   => 1,
                'lstmdby'    => 1,
            ]);

            $knight->save();
            return $knight;
        });

        return response()->json([
            'ok'     => true,
            'knight' => $this->publicProfile($knight),
        ], 201);
    }

    // ---------------------------------------------------------------
    // GET /api/knight/{discordid}
    // Full knight lookup by Discord ID.
    // ---------------------------------------------------------------

    public function show(string $discordid)
    {
        $knight = $this->findByDiscord($discordid);

        return response()->json([
            'knight' => $this->publicProfile($knight),
        ]);
    }

    // ---------------------------------------------------------------
    // GET /api/knight/{discordid}/profile
    // Returning member profile with badges that have Discord role IDs.
    // ---------------------------------------------------------------

    public function profile(string $discordid)
    {
        $knight = $this->findByDiscord($discordid);

        $badges = DB::table('knightbadge')
            ->join('badge', 'badge.pkey', '=', 'knightbadge.fkeybadge')
            ->where('knightbadge.fkeyknight', $knight->pkey)
            ->where('knightbadge.delflg', 0)
            ->where('badge.delflg', 0)
            ->whereNotNull('badge.roleid')
            ->select('badge.pkey', 'badge.bdg_title', 'badge.typcd', 'badge.roleid')
            ->get();

        return response()->json([
            'knight' => $this->publicProfile($knight),
            'badges' => $badges,
        ]);
    }

    // ---------------------------------------------------------------
    // PUT /api/knight/{discordid}/roles
    // Discord → Squire role sync.
    // Bot sends array of Discord role IDs currently on the user.
    // Resolves to badge pkeys, reports unknowns for Arcaenum review.
    // ---------------------------------------------------------------

    public function syncRoles(Request $request, string $discordid)
    {
        $validated = $request->validate([
            'role_ids'   => 'required|array',
            'role_ids.*' => 'required|string',
        ]);

        $knight      = $this->findByDiscord($discordid);
        $incomingIds = $validated['role_ids'];

        // withoutGlobalScopes to avoid HasActiveTrait ambiguous column on join
        $knownBadges = Badge::withoutGlobalScopes()
            ->whereIn('roleid', $incomingIds)
            ->where('delflg', 0)
            ->get(['pkey', 'roleid', 'bdg_title']);

        $knownRoleIds   = $knownBadges->pluck('roleid')->map(fn($id) => (string)$id)->all();
        $unknownRoleIds = array_values(array_diff(
            array_map('strval', $incomingIds),
            $knownRoleIds
        ));

        $now = now();
        foreach ($knownBadges as $badge) {
            $exists = DB::table('knightbadge')
                ->where('fkeybadge', $badge->pkey)
                ->where('fkeyknight', $knight->pkey)
                ->where('delflg', 0)
                ->exists();

            if (!$exists) {
                DB::table('knightbadge')->insert([
                    'fkeybadge'  => $badge->pkey,
                    'fkeyknight' => $knight->pkey,
                    'crtsetid'   => 1,
                    'lstmdby'    => 1,
                    'crtsetdt'   => $now,
                    'lstmdts'    => $now,
                ]);
            }
        }

        if (!empty($unknownRoleIds)) {
            Log::warning('Discord role sync: unknown role IDs not mapped in badge table', [
                'discordid'        => $discordid,
                'knight_pkey'      => $knight->pkey,
                'unknown_role_ids' => $unknownRoleIds,
            ]);
        }

        return response()->json([
            'ok'               => true,
            'synced_badges'    => $knownBadges->count(),
            'unknown_role_ids' => $unknownRoleIds,
        ]);
    }

    // ---------------------------------------------------------------
    // POST /api/knight/{discordid}/restore
    // Squire → Discord: return all role IDs the bot should re-apply.
    // Skips badges with no roleid. Flags deleted Discord roles for review.
    // ---------------------------------------------------------------

    public function restore(Request $request, string $discordid)
    {
        $knight = $this->findByDiscord($discordid);

        $deletedOnDiscord = array_map('strval', $request->input('deleted_role_ids', []));

        $knightBadges = DB::table('knightbadge')
            ->join('badge', 'badge.pkey', '=', 'knightbadge.fkeybadge')
            ->where('knightbadge.fkeyknight', $knight->pkey)
            ->where('knightbadge.delflg', 0)
            ->where('badge.delflg', 0)
            ->whereNotNull('badge.roleid')
            ->select(
                'badge.pkey as badge_pkey',
                'badge.bdg_title',
                'badge.typcd',
                'badge.roleid'
            )
            ->get();

        $restorable   = [];
        $ticketNeeded = [];

        foreach ($knightBadges as $kb) {
            $roleId = (string) $kb->roleid;

            if (in_array($roleId, $deletedOnDiscord, true)) {
                $ticketNeeded[] = [
                    'badge_pkey' => $kb->badge_pkey,
                    'bdg_title'  => $kb->bdg_title,
                    'roleid'     => $roleId,
                    'reason'     => 'Role ID not found on Discord server. File a ticket with the Arcaenum to review.',
                ];
                Log::warning('Restore: Discord role ID missing from server', [
                    'discordid'  => $discordid,
                    'badge_pkey' => $kb->badge_pkey,
                    'roleid'     => $roleId,
                ]);
            } else {
                $restorable[] = [
                    'badge_pkey' => $kb->badge_pkey,
                    'bdg_title'  => $kb->bdg_title,
                    'typcd'      => $kb->typcd,
                    'roleid'     => $roleId,
                ];
            }
        }

        return response()->json([
            'ok'            => true,
            'knight'        => $this->publicProfile($knight),
            'roles'         => $restorable,
            'ticket_needed' => $ticketNeeded,
        ]);
    }

    /**
     * POST /api/knight/{discordid}/reactivate
     * Reactivates an inactive knight if not deleted.
     * Returns error if knight is deleted — requires Commander intervention.
     */
    public function reactivate(string $discordid)
{
        $knight = Knight::withoutGlobalScopes()
            ->where('discordid', $discordid)
            ->firstOrFail();

        // Deleted knights cannot self-reactivate
        if ($knight->delflg) {
            return response()->json([
                'ok'      => false,
                'deleted' => true,
                'message' => 'This knight record has been deleted. Please contact your Commander to review and restore your record.',
            ], 403);
        }

        // Already active — nothing to do
        if ($knight->activeflg) {
            return response()->json([
                'ok'     => true,
                'active' => true,
            ]);
        }

        // Inactive but not deleted — reactivate
        $knight->activeflg = 1;
        $knight->save();

        Log::info('Knight reactivated via Discord /restore', [
            'discordid'   => $discordid,
            'knight_pkey' => $knight->pkey,
    ]);

        return response()->json([
            'ok'          => true,
            'reactivated' => true,
            'knight'      => $this->publicProfile($knight),
        ]);
    }
}