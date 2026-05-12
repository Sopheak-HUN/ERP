<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('returns the authenticated user`s tokens with is_current flag set correctly', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $currentPlain = $user->createToken('current-device')->plainTextToken;
    $user->createToken('other-device');
    $other->createToken('someone-elses');

    $response = $this->withHeader('Authorization', 'Bearer '.$currentPlain)
        ->getJson('/api/v1/auth/sessions');

    $response->assertOk();
    $items = $response->json('data.items');
    expect($items)->toBeArray()->toHaveCount(2);

    $names = array_column((array) $items, 'name');
    sort($names);
    expect($names)->toBe(['current-device', 'other-device']);

    $currentFlags = array_filter((array) $items, fn ($t) => $t['name'] === 'current-device');
    expect(array_values($currentFlags)[0]['is_current'])->toBeTrue();
});

it('revokes a specific non-current token by id', function (): void {
    $user = User::factory()->create();
    $currentPlain = $user->createToken('current')->plainTextToken;
    $otherToken = $user->createToken('victim');

    $this->withHeader('Authorization', 'Bearer '.$currentPlain)
        ->deleteJson('/api/v1/auth/sessions/'.$otherToken->accessToken->id)
        ->assertOk();

    expect($user->tokens()->whereKey($otherToken->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->count())->toBe(1);
});

it('refuses to revoke the current token via destroy', function (): void {
    $user = User::factory()->create();
    $currentToken = $user->createToken('current');
    $plain = $currentToken->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$plain)
        ->deleteJson('/api/v1/auth/sessions/'.$currentToken->accessToken->id)
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'CANNOT_REVOKE_CURRENT_SESSION');
});

it('returns 403 FORBIDDEN when attempting to revoke another user`s token', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $myToken = $user->createToken('mine')->plainTextToken;
    $theirToken = $other->createToken('theirs');

    $this->withHeader('Authorization', 'Bearer '.$myToken)
        ->deleteJson('/api/v1/auth/sessions/'.$theirToken->accessToken->id)
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('destroyOthers revokes all tokens except the current one', function (): void {
    $user = User::factory()->create();
    $current = $user->createToken('current')->plainTextToken;
    $user->createToken('other-1');
    $user->createToken('other-2');
    $user->createToken('other-3');
    expect($user->tokens()->count())->toBe(4);

    $this->withHeader('Authorization', 'Bearer '.$current)
        ->deleteJson('/api/v1/auth/sessions')
        ->assertOk()
        ->assertJsonPath('data.revoked_count', 3);

    expect($user->tokens()->count())->toBe(1);
});

it('records IP and user_agent at login time', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => bcrypt('right-pass'),
    ]);

    $this->withHeader('User-Agent', 'PestTestSuite/1.0')
        ->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'right-pass',
        ])
        ->assertOk();

    $token = PersonalAccessToken::query()->first();
    expect($token?->ip_address)->toBe('127.0.0.1');
    expect((string) $token?->user_agent)->toBe('PestTestSuite/1.0');
});
