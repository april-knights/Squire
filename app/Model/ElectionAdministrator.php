<?php

namespace App\Model;

use App\Support\HasLiveRowsTrait;
use App\Support\SquireModel;

class ElectionAdministrator extends SquireModel
{
    use HasLiveRowsTrait;

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

    /** Names must resolve even if the knight is later deactivated or deleted. */
    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey')->withoutGlobalScopes();
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