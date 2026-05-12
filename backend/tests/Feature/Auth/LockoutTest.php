<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Services\LockoutService;

it('locks email+ip after 5 failed attempts and returns 423 ACCOUNT_LOCKED', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'correct-password-here',
    ]);

    for ($i = 0; $i < LockoutService::MAX_ATTEMPTS; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'correct-password-here',
    ]);

    $response->assertStatus(423);
    $response->assertJsonPath('error.code', 'ACCOUNT_LOCKED');
    expect($response->json('error.details.retry_after'))->toBeInt()->toBeGreaterThan(0);
});

it('clears the failure counter on successful login', function (): void {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'right-pass',
    ]);

    // Two failures, then a success — should reset the counter.
    for ($i = 0; $i < 2; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'right-pass',
    ])->assertOk();

    /** @var LockoutService $svc */
    $svc = app(LockoutService::class);
    expect($svc->attempts('jane@example.com', '127.0.0.1'))->toBe(0);
});
