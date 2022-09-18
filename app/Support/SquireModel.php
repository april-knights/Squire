<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

abstract class SquireModel extends Model {
    protected $primaryKey = 'pkey';
    protected string|null $permName = null;

    public const PERMISSION_VIEW = 'v', PERMISSION_MODIFY = 'm', PERMISSION_DELETE = 'd';

    /**
     * Get the given type of permission for the current model type.
     * @param string $permType The permission type (see constants in this class)
     * @return string|null The permission field's name or null if permName is not set
     */
    public function getPermission(string $permType) {
        if ($this->permName) {
            return 'c' . $permType . $this->permName;
        } else {
            return null;
        }
    }
}
