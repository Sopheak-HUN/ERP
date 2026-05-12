<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Events\TwoFactorDisabled;
use App\Modules\Auth\Events\TwoFactorEnabled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

function tokenFor(User $user): string
{
    return $user->createToken('test')->plainTextToken;
}

it('enable returns a QR SVG and secret', function (): void {
    $user = User::factory()->create();
    $token = tokenFor($user);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/two-factor/enable');

    $response->assertOk();
    $response->assertJsonStructure(['data' => ['qr_svg', 'secret']]);
    expect((string) $response->json('data.qr_svg'))->toContain('<svg');
    expect((string) $response->json('data.secret'))->not->toBe('');
});

it('confirm activates 2FA with a valid TOTP code and returns recovery codes', function (): void {
    Event::fake([TwoFactorEnabled::class]);

    $user = User::factory()->create();
    $token = tokenFor($user);

    $enable = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/two-factor/enable');
    $secret = (string) $enable->json('data.secret');

    $google = new Google2FA;
    $code = $google->getCurrentOtp($secret);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/two-factor/confirm', ['code' => $code]);

    $response->assertOk();
    expect($response->json('data.recovery_codes'))->toBeArray()->toHaveCount(10);
    expect($user->fresh()?->two_factor_confirmed_at)->not->toBeNull();
    Event::assertDispatched(TwoFactorEnabled::class);
});

it('confirm rejects an invalid code', function (): void {
    $user = User::factory()->create();
    $token = tokenFor($user);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/two-factor/enable')
        ->assertOk();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/two-factor/confirm', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_TWO_FACTOR_CODE');
});

it('disable requires the correct password and clears 2FA', function (): void {
    Event::fake([TwoFactorDisabled::class]);

    $user = User::factory()->create(['password' => Hash::make('correct-pass')]);
    $token = tokenFor($user);

    // Bootstrap 2FA state directly so the test isn't dependent on the
    // enable/confirm flow.
    $secret = (new Google2FA)->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode(['ABCDEFGHIJ']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/auth/two-factor', ['password' => 'wrong-pass'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'AUTH_FAILED');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/auth/two-factor', ['password' => 'correct-pass'])
        ->assertOk();

    expect($user->fresh()?->two_factor_confirmed_at)->toBeNull();
    expect($user->fresh()?->two_factor_secret)->toBeNull();
    Event::assertDispatched(TwoFactorDisabled::class);
});

it('login returns 423 TWO_FACTOR_REQUIRED when code missing on a 2FA-enabled account', function (): void {
    $user = User::factory()->create([
        'email' => 'mfa@example.com',
        'password' => Hash::make('right-pass'),
    ]);
    $secret = (new Google2FA)->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode(['REC0000001']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@example.com',
        'password' => 'right-pass',
    ])
        ->assertStatus(423)
        ->assertJsonPath('error.code', 'TWO_FACTOR_REQUIRED');
});

it('login succeeds with a valid TOTP code', function (): void {
    $user = User::factory()->create([
        'email' => 'mfa@example.com',
        'password' => Hash::make('right-pass'),
    ]);
    $google = new Google2FA;
    $secret = $google->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode(['REC0000001']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@example.com',
        'password' => 'right-pass',
        'two_factor_code' => $google->getCurrentOtp($secret),
    ])
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('login consumes a recovery code single-use', function (): void {
    $user = User::factory()->create([
        'email' => 'mfa@example.com',
        'password' => Hash::make('right-pass'),
    ]);
    $user->forceFill([
        'two_factor_secret' => (new Google2FA)->generateSecretKey(),
        'two_factor_recovery_codes' => json_encode(['REC0000001', 'REC0000002']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@example.com',
        'password' => 'right-pass',
        'two_factor_code' => 'REC0000001',
    ])->assertOk();

    $remaining = json_decode((string) $user->fresh()?->two_factor_recovery_codes, true);
    expect($remaining)->toBe(['REC0000002']);

    // Second use of the same code must fail.
    $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@example.com',
        'password' => 'right-pass',
        'two_factor_code' => 'REC0000001',
    ])->assertStatus(401);
});
