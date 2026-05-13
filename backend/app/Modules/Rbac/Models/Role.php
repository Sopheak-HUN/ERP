<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Models;

use App\Core\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Role as SpatieRole;

#[Fillable(['name', 'guard_name', 'description', 'is_system'])]
final class Role extends SpatieRole
{
    use HasAuditColumns;
    use LogsActivity;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_by' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name', 'description', 'is_system'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
