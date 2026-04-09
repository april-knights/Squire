<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends SquireModel {
    use HasActiveTrait;

    protected $table = 'orders';

    protected $fillable = [
        'title',
        'body',
        'level',
        'fkeybattalion',
        'authorid',
        'crtsetid',
        'lstmdby',
        'sort_order',
    ];

    protected $casts = [
        'crtsetdt' => 'datetime',
        'lstmdts'  => 'datetime',
    ];

    public function author(): BelongsTo {
        return $this->belongsTo(Knight::class, 'authorid', 'pkey');
    }

    public function battalion(): BelongsTo {
        return $this->belongsTo(Battalion::class, 'fkeybattalion', 'pkey');
    }
    /**
     * Knights who have read this order.
     * No HasActiveTrait — order_read is a plain table with no activeflg/delflg.
     */
    public function reads()
    {
        return $this->hasMany(\App\Model\OrderRead::class, 'fkeyorder', 'pkey');
    }

    /**
     * Whether the given knight has read this order.
     */
    public function isReadBy(int $knightId): bool
    {
        return $this->reads->contains('fkeyknight', $knightId);
    }
}
