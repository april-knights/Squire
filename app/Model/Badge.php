<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;

class Badge extends SquireModel
{
    protected $table = 'badge';

    use HasActiveTrait;

    protected $fillable = [
        'typcd', 'bdg_title', 'bdgdesc', 'orderid', 'roleid', 'imgurl',
        'crtsetid', 'lstmdby'
    ];

    protected $casts = [
        'crtsetdt' => 'datetime',
        'lstmdts'  => 'datetime',
    ];
}
