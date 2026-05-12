<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Auth\Events\EmailVerified;
use App\Modules\Auth\Middleware\EnsureEmailVerifiedApi;
use App\Modules\Auth\Notifications\VerifyEmailNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

it('sends VerifyEmailNotification to authenticated unverified user', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $token = $user->createToken('web')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertOk();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('short-circuits with already-verified message when user is already verified', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    $token = $user->createToken('web')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertOk();

    Notification::assertNothingSent();
});

it('verifies email via valid signed URL and dispatches EmailVerified', function (): void {
    Event::fake([EmailVerified::class]);

    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.email.verify',
        Carbon::now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1((string) $user->getEmailForVerification())],
    );

    $response = $this->get($url);

    $response->assertRedirect(rtrim((string) config('app.frontend_url'), '/').'/auth/login?verified=1');
    expect($user->fresh()?->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(EmailVerified::class);
});

it('rejects an expired signed URL', function (): void {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.email.verify',
        Carbon::now()->subMinute(),
        ['id' => $user->id, 'hash' => sha1((string) $user->getEmailForVerification())],
    );

    $this->get($url)
        ->assertStatus(403);
});

it('rejects a tampered signature', function (): void {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.email.verify',
        Carbon::now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1((string) $user->getEmailForVerification())],
    );

    $tampered = $url.'X';

    $this->get($tampered)
        ->assertStatus(403);
});

it('blocks protected routes with EnsureEmailVerifiedApi middleware when unverified', function (): void {
    Route::middleware(['auth:sanctum', EnsureEmailVerifiedApi::class])
        ->get('/api/v1/__test/verified-only', fn () => response()->json(['ok' => true]));

    $user = User::factory()->unverified()->create();
    $token = $user->createToken('web')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/__test/verified-only')
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'EMAIL_NOT_VERIFIED');
});
