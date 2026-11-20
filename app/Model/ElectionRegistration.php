<?php

namespace App\Model;

use App\Support\HasLiveRowsTrait;
use App\Support\SquireModel;

class ElectionRegistration extends SquireModel
{
    use HasLiveRowsTrait;

    protected $table = 'election_registration';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'registered_at',
        'crtsetid',
        'lstmdby',
        'activeflg',
        'delflg',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /** Names must resolve even if the knight is later deactivated or deleted. */
    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey')->withoutGlobalScopes();
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }
}