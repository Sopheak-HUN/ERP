<?php

declare(strict_types=1);

use App\Modules\Organization\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(static function (): void {
    // A user manages their own tenant's company details.
    Route::get('/company', [CompanyController::class, 'show'])->name('company.show');
    Route::patch('/company', [CompanyController::class, 'update'])->name('company.update');
    Route::post('/company/logo', [CompanyController::class, 'uploadLogo'])->name('company.logo.upload');
});
