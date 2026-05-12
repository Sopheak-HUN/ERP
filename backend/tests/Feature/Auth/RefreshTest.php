<?php

declare(strict_types=1);

use App\Models\User;

it('issues a new token and revokes the old one', function (): void {
    $user = User::factory()->create();
    $oldToken = $user->createToken('web')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$oldToken)
        ->postJson('/api/v1/auth/refresh');

    $response->assertOk();
    $newToken = (string) $response->json('data.access_token');

    expect($newToken)->not->toBe($oldToken);

    // Reset the AuthManager between requests so each one re-runs Sanctum's
    // token lookup (production has fresh processes; tests share state).
    $this->app['auth']->forgetGuards();
    $this->withHeader('Authorization', 'Bearer '.$oldToken)
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);

    $this->app['auth']->forgetGuards();
    $this->withHeader('Authorization', 'Bearer '.$newToken)
        ->getJson('/api/v1/auth/me')
        ->assertOk();
});

it('returns 401 with no token', function (): void {
    $this->postJson('/api/v1/auth/refresh')
        ->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
