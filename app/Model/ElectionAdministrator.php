<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;

class ElectionAdministrator extends SquireModel
{
    use HasActiveTrait;

    protected $table = 'election_administrator';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'is_assistant',
        'silent_audit_dms',
        'show_voter_names_secondary_officers',
        'appointed_at',
        'appointed_by',
        'crtsetid',
        'lstmdby',
        'activeflg',
        'delflg',
    ];

    protected $casts = [
        'is_assistant'                        => 'boolean',
        'silent_audit_dms'                    => 'boolean',
        'show_voter_names_secondary_officers' => 'boolean',
        'appointed_at'                        => 'datetime',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }

    public function isFullEA(): bool
    {
        return ! $this->is_assistant;
    }
}