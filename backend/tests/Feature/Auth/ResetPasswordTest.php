<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets the password with a valid token and revokes all existing tokens', function (): void {
    Event::fake([PasswordResetEvent::class]);

    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('old-password'),
    ]);
    $user->createToken('device-a');
    $user->createToken('device-b');
    expect($user->tokens()->count())->toBe(2);

    $token = Password::broker()->createToken($user);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => 'Brand-New-Password-2026',
        'password_confirmation' => 'Brand-New-Password-2026',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);

    $user->refresh();
    expect(Hash::check('Brand-New-Password-2026', (string) $user->password))->toBeTrue();
    expect($user->tokens()->count())->toBe(0);

    Event::assertDispatched(PasswordResetEvent::class);
});

it('returns 422 INVALID_RESET_TOKEN with a bad token', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'totally-not-a-real-token',
        'email' => 'jane@example.com',
        'password' => 'Brand-New-Password-2026',
        'password_confirmation' => 'Brand-New-Password-2026',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_RESET_TOKEN');
});

it('returns 422 INVALID_RESET_TOKEN when the user does not exist (no enumeration)', function (): void {
    $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'any-token',
        'email' => 'ghost@example.com',
        'password' => 'Brand-New-Password-2026',
        'password_confirmation' => 'Brand-New-Password-2026',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_RESET_TOKEN');
});

it('returns 422 VALIDATION_ERROR when password is too short', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns 422 VALIDATION_ERROR when password lacks letters or numbers', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => '------------',
        'password_confirmation' => '------------',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns 422 VALIDATION_ERROR when password confirmation does not match', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => 'Brand-New-Password-2026',
        'password_confirmation' => 'Different-Password-2026',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});
