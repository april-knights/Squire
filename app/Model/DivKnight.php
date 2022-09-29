<?php

namespace App\Model;

use App\Support\HasDeletedTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DivKnight extends Pivot {
    use HasDeletedTrait;

    protected $primaryKey = 'pkey';
}
