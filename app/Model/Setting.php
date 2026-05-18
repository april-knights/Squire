<?php

namespace App\Model;

use App\Support\SquireModel;

class Setting extends SquireModel
{
    protected $table = 'setting';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}