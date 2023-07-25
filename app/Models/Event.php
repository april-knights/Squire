<?php

namespace App\Models;

use App\Support\SquireModel;

class Event extends SquireModel {
    protected static string|null $permName = 'event';
    protected $table = 'event';

    protected $fillable = [
        'title',
        'livedate',
        'enddate',
        'redown'
    ];
}
