<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('forgot-password', function (Request $request): Limit {
            $email = (string) $request->input('email', '');
            $key = $email !== '' ? mb_strtolower($email) : (string) $request->ip();

            return Limit::perMinutes(15, 3)->by('forgot-password:'.$key);
        });

        // Per-IP guard against credential stuffing. Per-(email+ip) handled by
        // LockoutService inside AuthService, which produces ACCOUNT_LOCKED.
        RateLimiter::for('auth.login.ip', function (Request $request): Limit {
            return Limit::perHour(20)->by('auth.login.ip:'.((string) $request->ip()));
        });
    }
}
