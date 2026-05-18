<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ElectionVoteAudit extends Model
{
    protected $table = 'election_vote_audit';

    // Immutable — no updates
    public $timestamps = false;

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'action',
        'performed_by',
        'note',
    ];

    protected $casts = [
        'crtsetdt' => 'datetime',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    public function performer()
    {
        return $this->belongsTo(Knight::class, 'performed_by', 'pkey');
    }
}