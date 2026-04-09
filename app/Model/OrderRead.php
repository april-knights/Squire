<?php

namespace App\Model;

use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Relations\Pivot;

class KnightBadge extends Pivot
{
    protected $table = 'knightbadge';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkeybadge', 'fkeyknight', 'bdgreason', 'featured',
        'crtsetid', 'lstmdby'
    ];

    protected $casts = [
        'featured'  => 'boolean',
        'crtsetdt'  => 'datetime',
        'lstmdts'   => 'datetime',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'fkeybadge', 'pkey');
    }

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }
}