<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Knight extends Authenticatable
{
    use Notifiable;

    protected $table = 'knight';
    protected $primaryKey = 'pkey';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rname', 'dname', 'bio', 'firstevent', 'frenemy', 'rlimpact', 'delflg',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'crtsetdt' => 'datetime',
        'lstmdts' => 'datetime',
    ];
}
