<?php

namespace App\Model;
use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Battalion extends SquireModel {
    use HasActiveTrait;

    /**
     * Unaffiliated
     */
    const DEFAULT_BATTALION = 99;

    protected static string|null $permName = 'batt';
    protected $table = 'battalion';

    protected $fillable = [
        'battalias',
        'name',
        'rname'
    ];

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battlead');
    }

    public function officers(): HasMany {
        return $this->members()->whereHas('rank', function (Builder $query) {
            $query->where('rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        });
    }

    public function members(): HasMany {
        return $this->hasMany(Knight::class, 'batt', 'pkey');
    }
}
