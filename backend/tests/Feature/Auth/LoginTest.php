<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Events\UserLoggedIn;
use Illuminate\Support\Facades\Event;

it('returns 200 with token + user envelope on valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.token_type', 'Bearer');
    $response->assertJsonStructure([
        'success',
        'data' => [
            'access_token',
            'token_type',
            'expires_at',
            'user' => ['id', 'name', 'email', 'has_two_factor'],
        ],
    ]);
    $response->assertJsonPath('data.user.id', $user->id);
});

it('returns 422 VALIDATION_ERROR on missing email', function (): void {
    $response = $this->postJson('/api/v1/auth/login', ['password' => 'password']);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns 422 on malformed email', function (): void {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns 401 AUTH_FAILED on wrong password', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'correct-horse-battery-staple',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong',
    ]);

    $response->assertStatus(401);
    $response->assertJsonPath('error.code', 'AUTH_FAILED');
});

it('returns 401 AUTH_FAILED with same message when user does not exist (no enumeration)', function (): void {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ghost@example.com',
        'password' => 'whatever',
    ]);

    $response->assertStatus(401);
    $response->assertJsonPath('error.code', 'AUTH_FAILED');
});

it('dispatches UserLoggedIn event on successful login', function (): void {
    Event::fake([UserLoggedIn::class]);

    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
    ])->assertOk();

    Event::assertDispatched(UserLoggedIn::class);
});

it('respects device_name when provided', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
        'device_name' => 'MacBook Pro',
    ])->assertOk();

    $this->assertDatabaseHas('personal_access_tokens', ['name' => 'MacBook Pro']);
});

it('issues a longer-lived token when remember=true', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $shortResp = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
        'remember' => false,
    ]);
    $shortExpiresAt = (string) $shortResp->json('data.expires_at');

    $longResp = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
        'remember' => true,
    ]);
    $longExpiresAt = (string) $longResp->json('data.expires_at');

    expect(strtotime($longExpiresAt))->toBeGreaterThan(strtotime($shortExpiresAt) + 60 * 60 * 24);
});
