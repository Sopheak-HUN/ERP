<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Policies;

use App\Models\User;

/**
 * Authorization for managing role assignments on users. Lives in the RBAC
 * module rather than a future User module because the actions are RBAC
 * operations on the User model.
 */
final class UserRolePolicy
{
    public function viewRoles(User $actor, User $target): bool
    {
        return $actor->can('users.view');
    }

    public function syncRoles(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            // Disallow editing your own roles to prevent self-lockout.
            return false;
        }

        return $actor->can('users.assign-roles');
    }
}
