<?php

namespace app\Model;

class ElectionNomination extends SquireModel
{
    protected $table = 'election_nomination';

    protected $fillable = [
        'fkeyelection',
        'fkeycandidate',
        'fkeyknight',
        'action',
        'reddit_comment_url',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    public function candidate()
    {
        return $this->belongsTo(ElectionCandidate::class, 'fkeycandidate', 'pkey');
    }
}