<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Division extends SquireModel {
    use HasActiveTrait;

    protected $fillable = [
        'divalias'
    ];

    public function leader(): HasOne {
        return $this->hasOne(Knight::class, 'pkey', 'divlead');
    }

    public function members(): BelongsToMany {
        return $this->belongsToMany(Knight::class, 'divknight')->withPivot('delflg')->using(DivKnight::class);
    }

    public function officers(): BelongsToMany { // TODO: The same rank is being used for battalions and divisions
        return $this->members()->whereHas('rank', function (Builder $query) {
            $query->where('rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        });
    }
}
