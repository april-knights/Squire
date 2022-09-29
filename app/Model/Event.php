<?php

namespace App\Model;

use App\Support\SquireModel;

class Event extends SquireModel {
    protected string|null $permName = 'event';
    protected $table = 'event';

    protected $fillable = [
        'title',
        'livedate',
        'enddate',
        'redown'
    ];
}
