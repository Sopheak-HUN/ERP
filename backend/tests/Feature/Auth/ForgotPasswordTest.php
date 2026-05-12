<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

it('returns 200 success envelope even when email does not exist (no enumeration)', function (): void {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'ghost@example.com',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    Notification::assertNothingSent();
});

it('sends ResetPasswordNotification when email exists', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'jane@example.com',
    ])->assertOk();

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('returns 422 VALIDATION_ERROR on missing email', function (): void {
    $this->postJson('/api/v1/auth/forgot-password', [])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('returns 422 on malformed email', function (): void {
    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'not-an-email',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('throttles after 3 attempts per email within 15 minutes', function (): void {
    Notification::fake();

    $payload = ['email' => 'rate@example.com'];

    $this->postJson('/api/v1/auth/forgot-password', $payload)->assertOk();
    $this->postJson('/api/v1/auth/forgot-password', $payload)->assertOk();
    $this->postJson('/api/v1/auth/forgot-password', $payload)->assertOk();

    $this->postJson('/api/v1/auth/forgot-password', $payload)
        ->assertStatus(429);
});
