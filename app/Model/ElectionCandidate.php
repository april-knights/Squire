<?php

namespace App\Model;

use App\Support\HasLiveRowsTrait;
use App\Support\SquireModel;

class ElectionCandidate extends SquireModel
{
    use HasLiveRowsTrait;

    protected $table = 'election_candidate';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'status',
        'nomination_url',
        'crtsetid',
        'lstmdby',
        'activeflg',
        'delflg',
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

    public function nominations()
    {
        return $this->hasMany(ElectionNomination::class, 'fkeycandidate', 'pkey');
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}