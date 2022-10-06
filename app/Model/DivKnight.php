<?php

namespace App\Model;

use App\Support\HasDeletedTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DivKnight extends Pivot {
    use HasDeletedTrait;

    protected $primaryKey = 'pkey';
    const UPDATED_AT = 'lstmdts';
    const CREATED_AT = 'crtsetdt';
}
