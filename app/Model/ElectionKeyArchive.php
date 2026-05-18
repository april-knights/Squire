<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ElectionKeyArchive extends Model
{
    protected $table = 'election_key_archive';

    // Immutable — no updates
    public $timestamps = false;

    protected $fillable = [
        'fkeyelection',
        'archived_key',
        'archived_by',
        'archived_at',
        'note',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * Never expose the archived key in serialization.
     */
    protected $hidden = [
        'archived_key',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class, 'fkeyelection', 'pkey');
    }

    public function archiver()
    {
        return $this->belongsTo(Knight::class, 'archived_by', 'pkey');
    }
}