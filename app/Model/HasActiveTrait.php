<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasActiveTrait {
    /**
     * Limit the query to active models. Only active models are visible for non-admins.
     * @param Builder $builder
     * @return Builder
     */
    public function scopeActive(Builder $builder, bool $active = null) {
        if ($active === null) {
            //Auth::user()->checkSecurity('') - TODO: Check permission for seeing active things
            $active = true;
        }
        return $builder->where('activeflg', $active)->deleted(false);
    }

    /**
     * Limit the query to deleted models. Deleted models are not visible anywhere.
     * @param Builder $builder
     * @return Builder
     */
    public function scopeDeleted(Builder $builder, bool $deleted = true) {
        return $builder->where('delflg', $deleted);
    }
}
