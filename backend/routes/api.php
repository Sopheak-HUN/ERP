<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| Every API surface lives under /api/v1. Module-specific route files are
| loaded by their own service providers (see app/Modules/{Module}/...).
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/health', HealthController::class)->name('health');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', static fn (Request $request) => $request->user())->name('user.me');
    });
});
