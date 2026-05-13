<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Models\Permission as SpatiePermission;

#[Fillable(['name', 'guard_name', 'description', 'group'])]
final class Permission extends SpatiePermission
{
}
