<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\TokenResult;
use App\Modules\Auth\Events\LoginFailed;
use App\Modules\Auth\Events\UserLoggedIn;
use App\Modules\Auth\Events\UserLoggedOut;
use App\Modules\Auth\Exceptions\AuthFailedException;
use App\Modules\Auth\Exceptions\TwoFactorRequiredException;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class AuthService
{
    private const DEFAULT_TTL_MINUTES = 60 * 8;

    private const REMEMBER_TTL_MINUTES = 60 * 24 * 30;

    public function __construct(
        private readonly Hasher $hasher,
        private readonly LockoutService $lockoutService,
        private readonly TwoFactorService $twoFactorService,
    ) {}

    public function attempt(LoginDTO $login): TokenResult
    {
        $this->lockoutService->ensureNotLocked($login->email, $login->ip);

        $user = User::query()->where('email', $login->email)->first();

        if (! $user instanceof User || ! $this->hasher->check($login->password, (string) $user->password)) {
            $this->lockoutService->recordFailure($login->email, $login->ip);
            Event::dispatch(new LoginFailed($login->email, $login->ip));
            throw new AuthFailedException;
        }

        if ($this->twoFactorService->isEnabledFor($user)) {
            if ($login->twoFactorCode === null || $login->twoFactorCode === '') {
                throw new TwoFactorRequiredException;
            }

            $accepted = $this->twoFactorService->verifyCode($user, $login->twoFactorCode);
            if (! $accepted) {
                $this->lockoutService->recordFailure($login->email, $login->ip);
                Event::dispatch(new LoginFailed($login->email, $login->ip));
                throw new AuthFailedException;
            }
        }

        $this->lockoutService->clear($login->email, $login->ip);

        return DB::transaction(function () use ($user, $login): TokenResult {
            $ttlMinutes = $login->remember ? self::REMEMBER_TTL_MINUTES : self::DEFAULT_TTL_MINUTES;
            $expiresAt = CarbonImmutable::now()->addMinutes($ttlMinutes);
            $deviceName = $login->deviceName ?? 'web';

            $newToken = $user->createToken($deviceName, ['*'], $expiresAt);

            // Persist session metadata on the freshly-issued token row.
            $newToken->accessToken->forceFill([
                'ip_address' => $login->ip,
                'user_agent' => $login->userAgent,
                'last_used_ip' => $login->ip,
            ])->save();

            Event::dispatch(new UserLoggedIn($user));

            return new TokenResult(
                accessToken: $newToken->plainTextToken,
                expiresAt: $expiresAt,
                user: $user,
            );
        });
    }

    public function revokeCurrent(User $user): void
    {
        $user->currentAccessToken()->delete();
        Event::dispatch(new UserLoggedOut($user));
    }

    public function refresh(User $user, string $ip, ?string $userAgent = null): TokenResult
    {
        $current = $user->currentAccessToken();
        $deviceName = (string) $current->name;

        return DB::transaction(function () use ($user, $current, $deviceName, $ip, $userAgent): TokenResult {
            $expiresAt = CarbonImmutable::now()->addMinutes(self::DEFAULT_TTL_MINUTES);
            $newToken = $user->createToken($deviceName, ['*'], $expiresAt);

            $newToken->accessToken->forceFill([
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'last_used_ip' => $ip,
            ])->save();

            $current->delete();

            return new TokenResult(
                accessToken: $newToken->plainTextToken,
                expiresAt: $expiresAt,
                user: $user,
            );
        });
    }
}
