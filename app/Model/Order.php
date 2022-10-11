<?php

namespace App\Model;

use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends SquireModel {
    protected $fillable = [
    ];

    public function author(): BelongsTo {
        return $this->belongsTo(Knight::class, 'authorid', 'pkey');
    }
}
