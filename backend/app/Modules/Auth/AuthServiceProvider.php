<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1/auth')
            ->name('api.v1.auth.')
            ->group(__DIR__.'/Routes/api.php');
    }
}
