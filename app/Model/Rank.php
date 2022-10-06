<?php

namespace App\Model;

use App\Support\SquireModel;

class Rank extends SquireModel
{
    protected $table = 'krank';

    public const HIGHEST_OFFICER_RANK = 8;
    public const HIGHEST_COUNCILOR_RANK = 3;
    /**
     * Applicant rank value, given if the knight doesn't have a valid rank.
     */
    public const DEFAULT_KNIGHT_RANK = 99;
    /**
     * Default rank ID selected when creating a new user (initiate).
     */
    public const DEFAULT_PROFILE_RANK_ID = 13;
}
