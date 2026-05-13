<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Support;

/**
 * Catalogue of system-defined permissions. Each entry is the canonical name
 * (resource.action) along with metadata used by the seeder and the permission
 * picker UI on the frontend.
 *
 * The list is intentionally narrow for Phase 2.2 — Auth/RBAC. New modules
 * append entries here as they are built (HR, Inventory, etc.).
 */
final class SystemPermissions
{
    /**
     * @return list<array{name: string, description: string, group: string}>
     */
    public static function all(): array
    {
        return [
            // RBAC ----------------------------------------------------------
            ['name' => 'roles.view', 'description' => 'View roles', 'group' => 'RBAC'],
            ['name' => 'roles.create', 'description' => 'Create roles', 'group' => 'RBAC'],
            ['name' => 'roles.update', 'description' => 'Update roles', 'group' => 'RBAC'],
            ['name' => 'roles.delete', 'description' => 'Delete roles', 'group' => 'RBAC'],
            ['name' => 'roles.assign-permissions', 'description' => 'Assign permissions to roles', 'group' => 'RBAC'],
            ['name' => 'permissions.view', 'description' => 'View permissions', 'group' => 'RBAC'],

            // Users ---------------------------------------------------------
            ['name' => 'users.view', 'description' => 'View users', 'group' => 'Users'],
            ['name' => 'users.create', 'description' => 'Create users', 'group' => 'Users'],
            ['name' => 'users.update', 'description' => 'Update users', 'group' => 'Users'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'group' => 'Users'],
            ['name' => 'users.assign-roles', 'description' => 'Assign roles to users', 'group' => 'Users'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(static fn (array $row): string => $row['name'], self::all());
    }
}
