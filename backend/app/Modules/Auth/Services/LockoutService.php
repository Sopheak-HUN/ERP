<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Exceptions\AccountLockedException;
use Illuminate\Support\Facades\Cache;

final class LockoutService
{
    /**
     * Per (email + IP) attempt budget: 5 failures within 15 minutes.
     */
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 60 * 15;

    public function ensureNotLocked(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        $attempts = (int) Cache::get($key.':attempts', 0);

        if ($attempts < self::MAX_ATTEMPTS) {
            return;
        }

        /** @var int|null $lockedUntil */
        $lockedUntil = Cache::get($key.':locked_until');
        if ($lockedUntil === null) {
            return;
        }

        $retryAfter = max(0, $lockedUntil - time());
        if ($retryAfter <= 0) {
            $this->clear($email, $ip);

            return;
        }

        throw new AccountLockedException($retryAfter);
    }

    public function recordFailure(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        $attempts = (int) Cache::get($key.':attempts', 0) + 1;

        Cache::put($key.':attempts', $attempts, self::DECAY_SECONDS);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::put($key.':locked_until', time() + self::DECAY_SECONDS, self::DECAY_SECONDS);
        }
    }

    public function clear(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        Cache::forget($key.':attempts');
        Cache::forget($key.':locked_until');
    }

    public function attempts(string $email, string $ip): int
    {
        return (int) Cache::get($this->key($email, $ip).':attempts', 0);
    }

    private function key(string $email, string $ip): string
    {
        return 'auth.login.'.mb_strtolower($email).'|'.$ip;
    }
}
