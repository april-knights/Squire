<?php

namespace App\Models;

use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Builder;

class Rank extends SquireModel
{
    protected $table = 'krank';

    /**
     * Ranks with a value <= this are considered to be officer ranks.
     */
    public const HIGHEST_OFFICER_RANK = 8;
    /**
     * Ranks with a value <= this are considered to be commander ranks.
     */
    public const HIGHEST_COMMANDER_RANK = 5;
    /**
     * Ranks with a value <= this are considered to be councilor ranks.
     */
    public const HIGHEST_COUNCILOR_RANK = 3;
    /**
     * Applicant rank value, given if the knight doesn't have a valid rank.
     */
    public const DEFAULT_KNIGHT_RANK = 99;
    /**
     * Default rank ID selected when creating a new user (initiate).
     */
    public const DEFAULT_PROFILE_RANK_ID = 13;
    /**
     * The ID of the grandmaster rank.
     */
    public const GRANDMASTER_RANK_ID = 1;

    public function scopeSecurity(Builder $query, Security $security): Builder
    {
        if ($security->isOfficer()) return $this->scopeOfficer($query);
        return match ($security->pkey) {
            Security::COMMANDER_SECURITY_ID => $this->scopeCommander($query),
            Security::COUNCILOR_SECURINTY_ID => $this->scopeCouncilor($query),
            Security::GRANDMASTER_SECURITY_ID => $query->where('id', self::GRANDMASTER_RANK_ID),
            default => $query,
        };
    }

    public function scopeOfficer(Builder $query): Builder
    {
        return $query->where('rval', '<=', self::HIGHEST_OFFICER_RANK);
    }

    public function scopeCommander(Builder $query): Builder
    {
        return $query->where('rval', '<=', self::HIGHEST_COMMANDER_RANK);
    }

    public function scopeCouncilor(Builder $query): Builder
    {
        return $query->where('rval', '<=', self::HIGHEST_COUNCILOR_RANK);
    }
}
