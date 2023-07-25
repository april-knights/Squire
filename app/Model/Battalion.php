<?php

namespace App\Model;
use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Battalion extends SquireModel {
    use HasActiveTrait;
    use HasFactory;

    /**
     * Unaffiliated
     */
    const DEFAULT_BATTALION = 99;

    protected static string|null $permName = 'batt';
    protected $table = 'battalion';

    protected $fillable = [
        'battalias',
        'name',
        'battdescr',
        'color',
        'motto'
    ];

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battlead');
    }

    public function sec1(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battsec1');
    }

    public function sec2(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'battsec2');
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
