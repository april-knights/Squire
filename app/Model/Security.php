<?php

namespace App\Model;

use App\Support\SquireModel;

class Security extends SquireModel {
    protected $fillable = [
        'secname',
        'secdescr'
    ];

    protected $table = 'security';
}
