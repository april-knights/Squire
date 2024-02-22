<?php

namespace App\Models;

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

    public function members(?bool $deleted = false): BelongsToMany {
        return $this->checkDeletedPivot($this->belongsToMany(Knight::class, 'divknight', 'fkeydivision', 'fkeyknight')
            ->withPivot('delflg')->using(DivKnight::class), $deleted);
    }

    public function officers(?bool $deleted = false): BelongsToMany { // TODO: The same rank is being used for battalions and divisions
        return $this->checkDeletedPivot($this->members()->whereHas('rank', function (Builder $query) {
            $query->where('rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        }), $deleted);
    }
}
