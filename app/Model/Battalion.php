<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Battalion extends Model
{
    protected $fillable = [
        'battalias',
        'name',
        'rname'
    ];

    protected $table = 'battalion';

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battlead');
    }
}
