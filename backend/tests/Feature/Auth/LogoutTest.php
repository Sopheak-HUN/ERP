<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Events\UserLoggedOut;
use Illuminate\Support\Facades\Event;

it('revokes the current token and returns success envelope', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('returns 401 UNAUTHENTICATED with no token', function (): void {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
    $response->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('does not revoke other tokens belonging to the same user', function (): void {
    $user = User::factory()->create();
    $tokenA = $user->createToken('device-a')->plainTextToken;
    $user->createToken('device-b');

    $this->withHeader('Authorization', 'Bearer '.$tokenA)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    $this->assertDatabaseHas('personal_access_tokens', ['name' => 'device-b']);
    $this->assertDatabaseMissing('personal_access_tokens', ['name' => 'device-a']);
});

it('dispatches UserLoggedOut event', function (): void {
    Event::fake([UserLoggedOut::class]);

    $user = User::factory()->create();
    $token = $user->createToken('x')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    Event::assertDispatched(UserLoggedOut::class);
});
