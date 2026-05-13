<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Laravel\Sanctum\Sanctum;

it('syncs permissions onto a role and persists them', function (): void {
    $assign = Permission::query()->create(['name' => 'roles.assign-permissions', 'guard_name' => 'web']);
    $view = Permission::query()->create(['name' => 'users.view', 'guard_name' => 'web']);
    $update = Permission::query()->create(['name' => 'users.update', 'guard_name' => 'web']);

    $adminRole = Role::query()->create(['name' => 'rbac-perms-admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions([$assign]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    Sanctum::actingAs($admin);

    $target = Role::query()->create(['name' => 'rbac-perms-target', 'guard_name' => 'web']);

    $response = $this->putJson("/api/v1/rbac/roles/{$target->id}/permissions", [
        'permission_ids' => [$view->id, $update->id],
    ]);

    $response->assertOk();
    expect($target->fresh()->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['users.view', 'users.update']);
});

it('clearing permissions removes them', function (): void {
    $assign = Permission::query()->create(['name' => 'roles.assign-permissions', 'guard_name' => 'web']);
    $existing = Permission::query()->create(['name' => 'users.view', 'guard_name' => 'web']);

    $adminRole = Role::query()->create(['name' => 'rbac-clear-admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions([$assign]);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    Sanctum::actingAs($admin);

    $target = Role::query()->create(['name' => 'rbac-clear-target', 'guard_name' => 'web']);
    $target->givePermissionTo($existing);

    $this->putJson("/api/v1/rbac/roles/{$target->id}/permissions", ['permission_ids' => []])
        ->assertOk();

    expect($target->fresh()->permissions)->toHaveCount(0);
});

it('lists all permissions for the picker', function (): void {
    Permission::query()->create(['name' => 'permissions.view', 'guard_name' => 'web', 'group' => 'RBAC']);
    Permission::query()->create(['name' => 'users.view', 'guard_name' => 'web', 'group' => 'Users']);

    $role = Role::query()->create(['name' => 'rbac-pick-admin', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::query()->where('name', 'permissions.view')->get());
    $user = User::factory()->create();
    $user->assignRole($role);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/rbac/permissions');

    $response->assertOk();
    $response->assertJsonStructure(['data' => ['items' => [['id', 'name', 'group']]]]);
});
