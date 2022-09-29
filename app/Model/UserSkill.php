<?php

namespace App\Model;

use App\Support\HasDeletedTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserSkill extends Pivot {
    use HasDeletedTrait;

    protected $primaryKey = 'pkey';
}
