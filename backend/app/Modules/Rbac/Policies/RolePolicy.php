<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Policies;

use App\Models\User;
use App\Modules\Rbac\Models\Role;

final class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $user->can('roles.delete');
    }

    public function syncPermissions(User $user, Role $role): bool
    {
        return $user->can('roles.assign-permissions');
    }
}
