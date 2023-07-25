<?php

namespace App\Models;

use App\Support\SquireModel;

class Security extends SquireModel {
    protected $fillable = [
        'secname',
        'secdescr'
    ];

    protected $table = 'security';

    /**
     * Initiate security level set by default when creating a profile.
     */
    public const DEFAULT_PROFILE_SECURITY_ID = 9;

    public const ADMIN_SECURITY_ID = 1;
    public const GRANDMASTER_SECURITY_ID = 2;
    public const COUNCILOR_SECURINTY_ID = 3;
    public const COMMANDER_SECURITY_ID = 4;
    public function isOfficer() // TODO: Does this make sense with security levels?
    {
        return $this->id < 8; // ID less than knight security level
    }
}
