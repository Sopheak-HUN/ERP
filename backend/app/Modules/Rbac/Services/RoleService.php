<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Services;

use App\Modules\Rbac\Exceptions\SystemRoleProtectedException;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use App\Modules\Rbac\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;

final class RoleService
{
    public function __construct(
        private readonly RoleRepository $roles,
    ) {}

    /**
     * @param  array{name: string, description?: ?string}  $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            return Role::query()->create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'description' => $data['description'] ?? null,
                'is_system' => false,
            ]);
        });
    }

    /**
     * @param  array{name?: string, description?: ?string}  $data
     */
    public function update(Role $role, array $data): Role
    {
        if ($role->is_system && array_key_exists('name', $data) && $data['name'] !== $role->name) {
            throw new SystemRoleProtectedException('System roles cannot be renamed.');
        }

        return DB::transaction(function () use ($role, $data): Role {
            $role->fill(array_filter([
                'name' => $data['name'] ?? null,
                'description' => array_key_exists('description', $data) ? $data['description'] : $role->description,
            ], static fn ($v) => $v !== null));
            $role->save();

            return $role->fresh(['permissions']) ?? $role;
        });
    }

    public function delete(Role $role): void
    {
        if ($role->is_system) {
            throw new SystemRoleProtectedException('System roles cannot be deleted.');
        }

        DB::transaction(static function () use ($role): void {
            $role->delete();
        });
    }

    /**
     * @param  list<int>  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        return DB::transaction(function () use ($role, $permissionIds): Role {
            $permissions = Permission::query()
                ->whereIn('id', $permissionIds)
                ->where('guard_name', 'web')
                ->get();

            $role->syncPermissions($permissions);

            return $role->fresh(['permissions']) ?? $role;
        });
    }
}
