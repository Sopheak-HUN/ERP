<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('returns the authenticated user wrapped in success envelope', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    $token = $user->createToken('web')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/me');

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.id', $user->id);
    $response->assertJsonPath('data.email', 'jane@example.com');
    $response->assertJsonPath('data.has_two_factor', false);
});

it('returns 401 UNAUTHENTICATED when token absent', function (): void {
    $this->getJson('/api/v1/auth/me')
        ->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('returns 401 when token has been revoked', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('web')->plainTextToken;

    // Revoke it.
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect(PersonalAccessToken::count())->toBe(0);

    // Reset the AuthManager so the next request re-resolves the Sanctum guard
    // (in production each request is a fresh process; in tests guards persist).
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});
