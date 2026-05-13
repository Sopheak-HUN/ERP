<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Support\SystemPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(static function (): void {
            foreach (SystemPermissions::all() as $row) {
                Permission::query()->updateOrCreate(
                    ['name' => $row['name'], 'guard_name' => 'web'],
                    ['description' => $row['description'], 'group' => $row['group']],
                );
            }
        });

        app()['cache']->forget(config('permission.cache.key'));
    }
}
