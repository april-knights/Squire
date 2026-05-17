<?php

namespace app\Model;

use Illuminate\Database\Eloquent\Model;

class ElectionPhaseLog extends Model
{
    protected $table = 'election_phase_log';

    // Immutable — no updates
    public $timestamps = false;

    protected $fillable = [
        'fkeyelection',
        'from_phase',
        'to_phase',
        'transitioned_by',
        'note',
    ];

    protected $casts = [
        'crtsetdt' => 'datetime',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }

    public function transitioner()
    {
        return $this->belongsTo(Knight::class, 'transitioned_by', 'pkey');
    }
}