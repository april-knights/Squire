<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait HasDeletedTrait {
    /**
     * Limit the query to deleted models. Deleted models are not visible anywhere.
     * @param Builder $builder
     * @param bool $deleted
     * @return Builder
     */
    public function scopeDeleted(Builder $builder, bool $deleted = true) {
        return $builder->where('delflg', $deleted);
    }
}
