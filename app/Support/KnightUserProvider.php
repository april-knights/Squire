<?php

namespace App\Support;

use Illuminate\Auth\EloquentUserProvider;

class KnightUserProvider extends EloquentUserProvider {
    protected function newModelQuery($model = null) {
        // The active scope also applies to knights, so it would result in an infinite loop if not ignored
        return parent::newModelQuery($model)->withoutGlobalScope(ActiveScope::class);
    }
}
