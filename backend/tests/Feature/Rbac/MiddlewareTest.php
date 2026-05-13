<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Laravel\Sanctum\Sanctum;

it('super-admin Gate::before bypass grants all role abilities', function (): void {
    $superRole = Role::query()->create(['name' => 'super-admin', 'guard_name' => 'web', 'is_system' => true]);
    $super = User::factory()->create();
    $super->assignRole($superRole);
    Sanctum::actingAs($super);

    // No explicit permissions attached — the Gate::before must bypass.
    $this->getJson('/api/v1/rbac/roles')->assertOk();
});

it('returns 403 envelope when policy denies', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson('/api/v1/rbac/roles');

    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

it('exposes roles and permissions on /me', function (): void {
    Permission::query()->create(['name' => 'roles.view', 'guard_name' => 'web']);
    $role = Role::query()->create(['name' => 'rbac-me-role', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::query()->where('name', 'roles.view')->get());

    $user = User::factory()->create();
    $user->assignRole($role);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertOk();
    $response->assertJsonPath('data.roles.0', 'rbac-me-role');
    $response->assertJsonPath('data.permissions.0', 'roles.view');
});
