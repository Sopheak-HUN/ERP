<?php

declare(strict_types=1);

namespace App\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @phpstan-require-extends Model
 */
trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(static function (Model $model): void {
            $userId = Auth::id();
            if ($userId === null) {
                return;
            }
            if ($model->getAttribute('created_by') === null) {
                $model->setAttribute('created_by', $userId);
            }
            if ($model->getAttribute('updated_by') === null) {
                $model->setAttribute('updated_by', $userId);
            }
        });

        static::updating(static function (Model $model): void {
            $userId = Auth::id();
            if ($userId === null) {
                return;
            }
            if (! $model->isDirty('updated_by')) {
                $model->setAttribute('updated_by', $userId);
            }
        });

        static::deleted(static function (Model $model): void {
            $userId = Auth::id();
            if ($userId === null) {
                return;
            }
            if (! in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
                return;
            }
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            $model->newQueryWithoutScopes()
                ->whereKey($model->getKey())
                ->update(['deleted_by' => $userId]);

            $model->setAttribute('deleted_by', $userId);
        });
    }
}
