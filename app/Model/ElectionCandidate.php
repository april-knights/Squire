<?php

namespace App\Model;

use App\Support\SquireModel;

class ElectionCandidate extends SquireModel
{
    protected $table = 'election_candidate';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'status',
        'nomination_url',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
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