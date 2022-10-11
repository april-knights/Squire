<?php

namespace App\Model;

use App\Support\SquireModel;

class Security extends SquireModel {
    protected $fillable = [
        'secname',
        'secdescr'
    ];

    protected $table = 'security';

    /**
     * Initiate security level set by default when creating a profile.d
     */
    public const DEFAULT_PROFILE_SECURITY_ID = 9;
}
