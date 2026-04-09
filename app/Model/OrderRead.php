<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderRead extends Model
{
    protected $table      = 'order_read';
    public    $timestamps = false;

    protected $fillable = [
        'fkeyorder',
        'fkeyknight',
    ];

    protected $dates = ['read_at'];
}
