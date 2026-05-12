<?php

declare(strict_types=1);
use Illuminate\Support\Str;

it('returns the standard envelope on /api/v1/health', function (): void {
    $response = $this->getJson('/api/v1/health');

    // Status may be 200 (ok) or 503 (degraded) depending on whether the host
    // has Redis available. Either way the envelope shape must match.
    expect($response->status())->toBeIn([200, 503]);

    $response
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'data' => ['status', 'checks', 'time'],
        ]);

    expect($response->json('data.status'))->toBeIn(['ok', 'degraded']);
});

it('includes the X-Request-Id header on the response', function (): void {
    $response = $this->getJson('/api/v1/health');

    expect($response->headers->get('X-Request-Id'))->not->toBeNull();
});

it('echoes a client-provided ULID as the X-Request-Id', function (): void {
    $ulid = (string) Str::ulid();

    $response = $this->getJson('/api/v1/health', ['X-Request-Id' => $ulid]);

    expect($response->headers->get('X-Request-Id'))->toBe($ulid);
});
