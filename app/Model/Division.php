<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Division extends SquireModel {
    use HasActiveTrait;

    protected static string|null $permName = 'batt';
    protected $table = 'division';

    protected $fillable = [
        'divalias',
        'name'
    ];

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'divlead');
    }

    public function members(): BelongsToMany {
        return $this->belongsToMany(Knight::class, 'divknight', 'fkeydivision', 'fkeyknight')
            ->withPivot('delflg')->using(DivKnight::class)->deleted(false);
    }

    public function officers(): BelongsToMany { // TODO: The same rank is being used for battalions and divisions
        return $this->members()->whereHas('rank', function (Builder $query) {
            $query->where('rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        });
    }
}
