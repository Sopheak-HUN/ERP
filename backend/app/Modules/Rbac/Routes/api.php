<?php

declare(strict_types=1);

use App\Modules\Rbac\Controllers\PermissionController;
use App\Modules\Rbac\Controllers\RoleController;
use App\Modules\Rbac\Controllers\RolePermissionController;
use App\Modules\Rbac\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RBAC module routes — mounted under /api/v1/rbac by RbacServiceProvider
|--------------------------------------------------------------------------
| All routes require an authenticated user. Authorization is enforced via
| policies (RolePolicy, UserRolePolicy) inside the controllers.
*/

Route::middleware('auth:sanctum')->group(static function (): void {
    // Permissions — read-only catalogue.
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');

    // Roles — full CRUD.
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->whereNumber('role')->name('roles.show');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])
        ->whereNumber('role')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->whereNumber('role')->name('roles.destroy');

    // Role <-> Permission assignment.
    Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'index'])
        ->whereNumber('role')->name('roles.permissions.index');
    Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'sync'])
        ->whereNumber('role')->name('roles.permissions.sync');

    // User <-> Role assignment.
    Route::get('/users/{user}/roles', [UserRoleController::class, 'index'])
        ->whereNumber('user')->name('users.roles.index');
    Route::put('/users/{user}/roles', [UserRoleController::class, 'sync'])
        ->whereNumber('user')->name('users.roles.sync');
});
