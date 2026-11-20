<?php

namespace App\Support;

trait HasLiveRowsTrait
{
    public static function bootHasLiveRowsTrait()
    {
        static::addGlobalScope(new LiveRowsScope());
    }
}