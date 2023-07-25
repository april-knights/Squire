<?php

namespace App\Models;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;

class Link extends SquireModel {
    use HasActiveTrait;

    public const TYPE_SUBREDDIT = 'subreddit';
    public const TYPE_EVENT = 'event';
    public const TYPE_DISCORD = 'discord';
    public const TYPE_DOCUMENT = 'document';

    protected $table = 'link';

    protected $fillable = [
    ];
}
