<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Laravel\Sanctum\Sanctum;

it('assigns roles to a user', function (): void {
    Permission::query()->create(['name' => 'users.assign-roles', 'guard_name' => 'web']);
    Permission::query()->create(['name' => 'users.view', 'guard_name' => 'web']);

    $adminRole = Role::query()->create(['name' => 'rbac-ur-admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(Permission::query()->whereIn('name', ['users.assign-roles', 'users.view'])->get());
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    Sanctum::actingAs($admin);

    $editorRole = Role::query()->create(['name' => 'rbac-ur-editor', 'guard_name' => 'web']);
    $target = User::factory()->create();

    $this->putJson("/api/v1/rbac/users/{$target->id}/roles", [
        'role_ids' => [$editorRole->id],
    ])->assertOk();

    expect($target->fresh()->roles->pluck('name')->all())->toContain('rbac-ur-editor');
});

it('blocks editing your own roles', function (): void {
    Permission::query()->create(['name' => 'users.assign-roles', 'guard_name' => 'web']);
    $adminRole = Role::query()->create(['name' => 'rbac-ur-self-admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(Permission::query()->where('name', 'users.assign-roles')->get());

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    Sanctum::actingAs($admin);

    $other = Role::query()->create(['name' => 'rbac-ur-self-other', 'guard_name' => 'web']);

    $this->putJson("/api/v1/rbac/users/{$admin->id}/roles", [
        'role_ids' => [$other->id],
    ])->assertStatus(403);
});

it('lists roles for a user', function (): void {
    Permission::query()->create(['name' => 'users.view', 'guard_name' => 'web']);
    $adminRole = Role::query()->create(['name' => 'rbac-ur-list-admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(Permission::query()->where('name', 'users.view')->get());
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    Sanctum::actingAs($admin);

    $editor = Role::query()->create(['name' => 'rbac-ur-list-editor', 'guard_name' => 'web']);
    $target = User::factory()->create();
    $target->assignRole($editor);

    $response = $this->getJson("/api/v1/rbac/users/{$target->id}/roles");

    $response->assertOk();
    $response->assertJsonPath('data.items.0.name', 'rbac-ur-list-editor');
});
