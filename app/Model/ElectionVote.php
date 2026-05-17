<?php

namespace app\Model;

class ElectionVote extends SquireModel
{
    protected $table = 'election_vote';

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'encrypted_ballot',
        'submitted_at',
        'valid',
        'invalid_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'valid'        => 'boolean',
    ];

    /**
     * Never expose the encrypted ballot in serialization.
     */
    protected $hidden = [
        'encrypted_ballot',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }
}