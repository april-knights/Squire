<?php

namespace App\Support;

use App\Model\Knight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

trait HasActiveTrait {
    use HasDeletedTrait;

    public static function bootHasActiveTrait() {
        static::addGlobalScope(new ActiveScope());
    }

    /**
     * Limit the query to active models. By default, inactive models are only visible to those who can edit the entity.
     * @param Builder $builder
     * @return Builder
     */
    public function scopeActive(Builder $builder, bool $active = null) {
        if ($active === null) {
            $user = Auth::user();
            if (!$user || !$user->checkSecurity($this->getPermission(SquireModel::PERMISSION_MODIFY))) {
                // If we don't have permission to edit the entity, we only have permission for active entities
                $builder->where('activeflg', true);
            }
        } else {
            $builder->where($this->table.'.activeflg', $active);
        }
        return $builder->deleted(false);
    }
}

class ActiveScope implements Scope {

    public function apply(Builder $builder, Model $model) {
        $builder->active();
    }
}
