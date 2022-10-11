<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

abstract class SquireModel extends Model {
    protected $primaryKey = 'pkey';
    protected static string|null $permName = null;
    const UPDATED_AT = 'lstmdts';
    const CREATED_AT = 'crtsetdt';

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

    /**
     * To be used in relations with pivot tables.
     * @param BelongsToMany $relation The relation to filter
     * @param bool|null $deleted Filter to deleted or existing relations
     * @return BelongsToMany The relation
     */
    protected function checkDeletedPivot(BelongsToMany $relation, ?bool $deleted) {
        if ($deleted === null) return $relation;
        else return $relation->wherePivot('delflg', $deleted);
    }
}
