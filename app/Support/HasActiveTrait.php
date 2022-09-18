<?php

namespace App\Support;

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
            if(!Auth::user()->checkSecurity($this->getPermission(SquireModel::PERMISSION_MODIFY))) {
                // If we don't have permission to edit the entity, we only have permission for active entities
                $builder->where('activeflg', true);
            }
        } else {
            $builder->where('activeflg', $active);
        }
        return $builder->deleted(false);
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
