<?php

namespace App\Model;

use App\Support\SquireModel;

class ElectionRegistration extends SquireModel
{
    protected $table = 'election_registration';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }
}