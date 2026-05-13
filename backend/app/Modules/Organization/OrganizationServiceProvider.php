<?php

declare(strict_types=1);

namespace App\Modules\Organization;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class OrganizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('api/v1/organization')
            ->middleware('api')
            ->name('api.v1.organization.')
            ->group(base_path('app/Modules/Organization/Routes/api.php'));
    }
}
