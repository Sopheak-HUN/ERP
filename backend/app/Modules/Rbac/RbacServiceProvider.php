<?php

declare(strict_types=1);

namespace App\Modules\Rbac;

use App\Models\User;
use App\Modules\Rbac\Models\Role;
use App\Modules\Rbac\Policies\RolePolicy;
use App\Modules\Rbac\Policies\UserRolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class RbacServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1/rbac')
            ->name('api.v1.rbac.')
            ->group(__DIR__.'/Routes/api.php');

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserRolePolicy::class);

        // Super-admin bypass — applies before any policy check.
        Gate::before(static function (User $user, string $ability): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
