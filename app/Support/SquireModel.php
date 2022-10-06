<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

abstract class SquireModel extends Model {
    protected $primaryKey = 'pkey';
    protected static string|null $permName = null;

    /**
     * Raw permission value. Use with ModelClass::getPermission().
     */
    public const PERMISSION_VIEW = 'v', PERMISSION_MODIFY = 'm', PERMISSION_DELETE = 'd';

    /**
     * Get the given type of permission for the current model type.
     * @param string $permType The permission type (see constants in this class)
     * @return string|null The permission field's name or null if permName is not set
     */
    public static function getPermission(string $permType) {
        if (static::$permName) {
            return 'c' . $permType . static::$permName;
        } else {
            return null;
        }
    }
}
