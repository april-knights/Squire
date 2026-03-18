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
    ];

    public function author(): BelongsTo {
        return $this->belongsTo(Knight::class, 'authorid', 'pkey');
    }

    public function battalion(): BelongsTo {
        return $this->belongsTo(Battalion::class, 'fkeybattalion', 'pkey');
    }
}<?php

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
