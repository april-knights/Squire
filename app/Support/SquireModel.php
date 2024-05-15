<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Syncs the relation while soft-deleting pivot records and storing who made the change (assuming current user).
     *
     * Also assuming that the relation method takes a 'deleted' flag (for now).
     *
     * @param string $relation The name of the relation to use
     * @param array $ids The IDs to attach and keep
     * @return void
     */
    public function syncRelation(string $relation, array $ids) {
        /** @var BelongsToMany $pivot */
        $pivot = $this->{$relation}(null); // TODO: withTrashed()
        $deleted = $pivot->whereNotIn($pivot->getRelatedPivotKeyName(), $ids)->get();
        $deleted->each(function (Model $model) {
            $model->pivot->delflg = true;
            $model->pivot->lstmdby = Auth::id(); // TODO: Move to a model event
            $model->pivot->save();
        });

        /** @var BelongsToMany $pivot */
        $pivot = $this->{$relation}(null);
        $updated = $pivot->whereIn($pivot->getRelatedPivotKeyName(), $ids)->get();
        $updated->each(function (Model $model) {
            $model->pivot->delflg = false;
            $model->pivot->lstmdby = Auth::id();
            $model->pivot->save();
        });

        /** @var BelongsToMany $pivot */
        $pivot = $this->{$relation}(null);
        $createdIds = array_diff(
            $ids,
            $updated->pluck($pivot->getRelatedPivotKeyName())->all(),
            $deleted->pluck($pivot->getRelatedPivotKeyName())->all()
        );
        $pivot->syncWithPivotValues($createdIds, [
            'crtsetid' => Auth::id(),
            'lstmdby' => Auth::id()
        ], false);
    }
}
