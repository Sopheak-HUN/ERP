<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final class TwoFactorService
{
    private const RECOVERY_CODE_COUNT = 10;

    public function __construct(
        private readonly Google2FA $google2fa,
    ) {}

    public function isEnabledFor(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function buildOtpAuthUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name', 'ERP'),
            (string) $user->email,
            $secret,
        );
    }

    public function verifyTotp(string $secret, string $code): bool
    {
        // 1-step window tolerates ~30s clock skew on either side. Google2FA
        // returns the matched timestamp (int) or false; coerce to bool here.
        return $this->google2fa->verifyKey($secret, $code, 1) !== false;
    }

    /**
     * Verify a code against the user's confirmed TOTP secret OR consume a
     * single-use recovery code. Returns true on success.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (! $this->isEnabledFor($user)) {
            return false;
        }

        $secret = $this->decodeSecret($user);
        if ($secret !== null && $this->verifyTotp($secret, $code)) {
            return true;
        }

        return $this->consumeRecoveryCode($user, $code);
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $this->decodeRecoveryCodes($user);
        $normalized = mb_strtoupper(trim($code));

        $remaining = array_values(array_filter(
            $codes,
            static fn (string $stored): bool => ! hash_equals($stored, $normalized),
        ));

        if (count($remaining) === count($codes)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($remaining),
        ])->save();

        return true;
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $codes[] = mb_strtoupper(Str::random(10));
        }

        return $codes;
    }

    private function decodeSecret(User $user): ?string
    {
        $secret = $user->two_factor_secret;

        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    /**
     * @return list<string>
     */
    private function decodeRecoveryCodes(User $user): array
    {
        $raw = $user->two_factor_recovery_codes;
        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn ($v): string => is_string($v) ? $v : '', $decoded),
            static fn (string $v): bool => $v !== '',
        ));
    }
}
