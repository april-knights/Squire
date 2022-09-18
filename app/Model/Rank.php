<?php

namespace App\Model;

use App\Support\SquireModel;

class Rank extends SquireModel
{
    protected $table = 'krank';

    public const HIGHEST_OFFICER_RANK = 8;
    public const HIGHEST_COUNCILOR_RANK = 3;
    public const DEFAULT_KNIGHT_RANK = 99;
}
