<?php

namespace App\Model;

use App\Support\SquireModel;

class Election extends SquireModel
{
    protected $table = 'election';

    protected $fillable = [
        'election_year',
        'phase',
        'phase_deadline',
        'nomination_thread_url',
        'nomination_post_id',
        'debate_thread_url',
        'debate_post_id',
        'registration_thread_url',
        'registration_post_id',
        'voting_paused',
        'admin_test_mode',
        'notes',
    ];

    /**
     * Key material is deliberately NOT fillable: it is only ever set through
     * ElectionService::startVoting() using forceFill().
     * public_key / wrapped_private_key / key_salt / key_created_at / audit_dm_verified_at
     */

    protected $hidden = [
        'wrapped_private_key',
        'key_salt',
    ];

    protected $casts = [
        'phase_deadline'       => 'datetime',
        'key_created_at'       => 'datetime',
        'audit_dm_verified_at' => 'datetime',
        'voting_paused'        => 'boolean',
        'admin_test_mode'      => 'boolean',
    ];

    const PHASES = [
        'setup',
        'nominations',
        'debate',
        'voting',
        'counting',
        'complete',
    ];

    public function administrator()
    {
        return $this->hasOne(ElectionAdministrator::class, 'fkeyelection', 'pkey')
            ->where('is_assistant', 0);
    }

    public function assistantAdministrator()
    {
        return $this->hasOne(ElectionAdministrator::class, 'fkeyelection', 'pkey')
            ->where('is_assistant', 1);
    }

    public function candidates()
    {
        return $this->hasMany(ElectionCandidate::class, 'fkeyelection', 'pkey');
    }

    public function acceptedCandidates()
    {
        return $this->hasMany(ElectionCandidate::class, 'fkeyelection', 'pkey')
            ->where('status', 'accepted');
    }

    public function nominations()
    {
        return $this->hasMany(ElectionNomination::class, 'fkeyelection', 'pkey');
    }

    public function registrations()
    {
        return $this->hasMany(ElectionRegistration::class, 'fkeyelection', 'pkey');
    }

    public function votes()
    {
        return $this->hasMany(ElectionVote::class, 'fkeyelection', 'pkey');
    }

    public function phaseLog()
    {
        return $this->hasMany(ElectionPhaseLog::class, 'fkeyelection', 'pkey');
    }

    public function keyArchive()
    {
        return $this->hasOne(ElectionKeyArchive::class, 'fkeyelection', 'pkey');
    }

    /**
     * Get the single active (non-complete) election, if one exists.
     */
    public static function active(): ?self
    {
        return static::where('phase', '!=', 'complete')->first();
    }

    /**
     * True once startVoting() has generated the election keypair.
     */
    public function hasKeys(): bool
    {
        return ! empty($this->public_key) && ! empty($this->wrapped_private_key);
    }

    /**
     * Voting is open only in the voting phase, not manually paused,
     * and with encryption keys in place.
     */
    public function isVotingOpen(): bool
    {
        return $this->phase === 'voting' && ! $this->voting_paused && $this->hasKeys();
    }

    public function isComplete(): bool
    {
        return $this->phase === 'complete';
    }
}