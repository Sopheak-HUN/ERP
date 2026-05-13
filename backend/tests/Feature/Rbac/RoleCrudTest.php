<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Permission::query()->create(['name' => 'roles.view', 'guard_name' => 'web']);
    Permission::query()->create(['name' => 'roles.create', 'guard_name' => 'web']);
    Permission::query()->create(['name' => 'roles.update', 'guard_name' => 'web']);
    Permission::query()->create(['name' => 'roles.delete', 'guard_name' => 'web']);
});

function actingAsAdminWith(array $permissions): User
{
    $user = User::factory()->create();
    $role = Role::query()->create(['name' => 'test-admin-'.uniqid(), 'guard_name' => 'web']);
    $role->syncPermissions(Permission::query()->whereIn('name', $permissions)->get());
    $user->assignRole($role);

    Sanctum::actingAs($user);

    return $user;
}

it('lists roles with pagination envelope', function (): void {
    Role::query()->create(['name' => 'rbac-test-alpha', 'guard_name' => 'web']);
    Role::query()->create(['name' => 'rbac-test-beta', 'guard_name' => 'web']);

    $admin = actingAsAdminWith(['roles.view']);

    $response = $this->getJson('/api/v1/rbac/roles');

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure([
        'success',
        'data' => ['items'],
        'meta' => ['pagination' => ['total', 'per_page', 'current_page']],
    ]);
});

it('rejects role listing without roles.view permission', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this
        ->getJson('/api/v1/rbac/roles')
        ->assertStatus(403);
});

it('creates a role', function (): void {
    $admin = actingAsAdminWith(['roles.create']);

    $response = $this
        ->postJson('/api/v1/rbac/roles', [
            'name' => 'editor',
            'description' => 'Edits content',
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.name', 'editor');
    $response->assertJsonPath('data.is_system', false);
    $this->assertDatabaseHas('roles', ['name' => 'editor', 'is_system' => false]);
});

it('rejects role creation with invalid name format', function (): void {
    $admin = actingAsAdminWith(['roles.create']);

    $this
        ->postJson('/api/v1/rbac/roles', ['name' => 'BAD Name!'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('rejects duplicate role name', function (): void {
    Role::query()->create(['name' => 'duplicate', 'guard_name' => 'web']);
    $admin = actingAsAdminWith(['roles.create']);

    $this
        ->postJson('/api/v1/rbac/roles', ['name' => 'duplicate'])
        ->assertStatus(422);
});

it('updates a non-system role', function (): void {
    $role = Role::query()->create(['name' => 'rbac-test-old', 'guard_name' => 'web']);
    $admin = actingAsAdminWith(['roles.update']);

    $this
        ->patchJson("/api/v1/rbac/roles/{$role->id}", ['description' => 'updated'])
        ->assertOk()
        ->assertJsonPath('data.description', 'updated');
});

it('blocks renaming a system role', function (): void {
    $role = Role::query()->create(['name' => 'rbac-test-system', 'guard_name' => 'web', 'is_system' => true]);
    $admin = actingAsAdminWith(['roles.update']);

    $this
        ->patchJson("/api/v1/rbac/roles/{$role->id}", ['name' => 'rbac-test-renamed'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'SYSTEM_ROLE_PROTECTED');
});

it('deletes a non-system role', function (): void {
    $role = Role::query()->create(['name' => 'rbac-test-temp', 'guard_name' => 'web']);
    $admin = actingAsAdminWith(['roles.delete']);

    $this
        ->deleteJson("/api/v1/rbac/roles/{$role->id}")
        ->assertOk();

    $this->assertSoftDeleted('roles', ['id' => $role->id]);
});

it('refuses to delete a system role (policy)', function (): void {
    $role = Role::query()->create(['name' => 'rbac-test-system2', 'guard_name' => 'web', 'is_system' => true]);
    $admin = actingAsAdminWith(['roles.delete']);

    $this
        ->deleteJson("/api/v1/rbac/roles/{$role->id}")
        ->assertStatus(403);
});
