<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Services;

use App\Models\User;
use App\Modules\Rbac\Models\Role;
use Illuminate\Support\Facades\DB;

final class UserRoleService
{
    /**
     * @param  list<int>  $roleIds
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function sync(User $user, array $roleIds): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($user, $roleIds) {
            $roles = Role::query()
                ->whereIn('id', $roleIds)
                ->where('guard_name', 'web')
                ->get();

            $user->syncRoles($roles);

            return $user->load('roles')->roles;
        });
    }
}
