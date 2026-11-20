<?php

namespace App\Model;

use App\Support\HasLiveRowsTrait;
use App\Support\SquireModel;

class ElectionNomination extends SquireModel
{
    use HasLiveRowsTrait;

    protected $table = 'election_nomination';

    protected $fillable = [
        'fkeyelection',
        'fkeycandidate',
        'fkeyknight',
        'action',
        'reddit_comment_url',
        'crtsetid',
        'lstmdby',
        'activeflg',
        'delflg',
    ];

    /** Names must resolve even if the knight is later deactivated or deleted. */
    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey')->withoutGlobalScopes();
    }

    public function candidate()
    {
        return $this->belongsTo(ElectionCandidate::class, 'fkeycandidate', 'pkey')->withoutGlobalScopes();
    }
}