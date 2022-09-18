<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Battalion extends SquireModel {
    use HasActiveTrait;

    protected $fillable = [
        'battalias',
        'name',
        'rname'
    ];

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battlead')->active();
    }

    public function officers(): HasMany {
        return $this->hasMany(Knight::class, 'batt', 'pkey')->whereHas('rank', function (Builder $query) {
            $query->where('rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        });
    }

    public function members(): HasMany {
        return $this->hasMany(Knight::class, 'batt', 'pkey');
    }
}
