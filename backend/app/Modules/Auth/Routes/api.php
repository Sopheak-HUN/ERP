<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\EmailVerificationController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Auth\Controllers\SessionController;
use App\Modules\Auth\Controllers\SessionListController;
use App\Modules\Auth\Controllers\TwoFactorController;
use App\Modules\Auth\Middleware\TrackTokenMetadata;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth module routes — mounted under /api/v1/auth by AuthServiceProvider
|--------------------------------------------------------------------------
*/

Route::post('/login', [SessionController::class, 'store'])
    // ->middleware('throttle:auth.login.ip')
    ->name('login');

Route::post('/forgot-password', [PasswordResetController::class, 'requestLink'])
    // ->middleware('throttle:forgot-password')
    ->name('forgot-password');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->name('reset-password');

// Signed URL — public so users can click the email link without being logged in.
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('email.verify');

Route::middleware(['auth:sanctum', TrackTokenMetadata::class])->group(function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::post('/refresh', [SessionController::class, 'refresh'])->name('refresh');
    Route::get('/me', [SessionController::class, 'me'])->name('me');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('email.send-verification');

    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes');

    Route::get('/sessions', [SessionListController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{id}', [SessionListController::class, 'destroy'])
        ->whereNumber('id')
        ->name('sessions.destroy');
    Route::delete('/sessions', [SessionListController::class, 'destroyOthers'])
        ->name('sessions.destroy-others');
});
