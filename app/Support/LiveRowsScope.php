<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LiveRowsScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $builder->where($table . '.activeflg', 1)
            ->where($table . '.delflg', 0);
    }
}