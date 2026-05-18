<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ElectionAuditLog extends Model
{
    protected $table = 'election_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'fkeyelection',
        'fkeyknight',
        'delivered',
        'delivered_at',
    ];

    protected $casts = [
        'delivered'    => 'boolean',
        'delivered_at' => 'datetime',
        'crtsetdt'     => 'datetime',
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