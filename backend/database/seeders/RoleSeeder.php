<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use App\Modules\Rbac\Support\SystemPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Base roles created for every environment. The super-admin is granted
     * every permission via `Gate::before` (see RbacServiceProvider), so we do
     * not need to sync the full permission list onto it. The other roles get
     * an explicit subset.
     *
     * @return array<string, array{description: string, permissions: list<string>}>
     */
    private function definitions(): array
    {
        return [
            'super-admin' => [
                'description' => 'Full unrestricted access. Reserved for system owners.',
                'permissions' => SystemPermissions::names(),
            ],
            'admin' => [
                'description' => 'Administers users and roles.',
                'permissions' => [
                    'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign-permissions',
                    'permissions.view',
                    'users.view', 'users.create', 'users.update', 'users.delete', 'users.assign-roles',
                ],
            ],
            'manager' => [
                'description' => 'Reads users and roles; no write access on RBAC.',
                'permissions' => [
                    'roles.view',
                    'permissions.view',
                    'users.view',
                ],
            ],
            'employee' => [
                'description' => 'Default role for end-users.',
                'permissions' => [],
            ],
        ];
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->definitions() as $name => $def) {
                /** @var Role $role */
                $role = Role::query()->updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['description' => $def['description'], 'is_system' => true],
                );

                $permissions = Permission::query()
                    ->whereIn('name', $def['permissions'])
                    ->where('guard_name', 'web')
                    ->get();

                $role->syncPermissions($permissions);
            }
        });

        app()['cache']->forget(config('permission.cache.key'));
    }
}
