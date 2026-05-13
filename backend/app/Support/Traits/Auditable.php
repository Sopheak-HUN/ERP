<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

trait Auditable
{
    use LogsActivity;
    use SoftDeletes;

    protected static function bootAuditable(): void
    {
        static::creating(static function (Model $model): void {
            if (auth()->check()) {
                if (empty($model->created_by)) {
                    $model->created_by = auth()->id();
                }
                if (empty($model->updated_by)) {
                    $model->updated_by = auth()->id();
                }
            }
        });

        static::updating(static function (Model $model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(static function (Model $model): void {
            if (auth()->check() && in_array('deleted_by', $model->getFillable(), true)) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
