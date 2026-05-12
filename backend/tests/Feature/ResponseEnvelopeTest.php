<?php

declare(strict_types=1);

use App\Exceptions\ApiException;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Route::middleware('api')->prefix('api/v1')->group(function (): void {
        Route::get('/__test/success', static fn () => ApiResponse::success(['hello' => 'world'], 'ok'));

        Route::get('/__test/validation', static function (): void {
            throw ValidationException::withMessages([
                'email' => ['The email field is required.'],
            ]);
        });

        Route::get('/__test/api-exception', static function (): void {
            throw new ApiException('THING_BROKE', 'A thing broke', 418, ['hint' => 'try again']);
        });

        Route::get('/__test/server-error', static function (): void {
            throw new RuntimeException('boom');
        });
    });
});

it('wraps controller payloads in the success envelope', function (): void {
    $this->getJson('/api/v1/__test/success')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['hello' => 'world'],
            'message' => 'ok',
        ]);
});

it('renders ValidationException as the error envelope', function (): void {
    $this->getJson('/api/v1/__test/validation')
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['error' => ['code', 'message', 'details']]);
});

it('renders ApiException with its declared status and code', function (): void {
    $this->getJson('/api/v1/__test/api-exception')
        ->assertStatus(418)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'THING_BROKE')
        ->assertJsonPath('error.details.hint', 'try again');
});

it('renders unhandled exceptions as SERVER_ERROR', function (): void {
    config()->set('app.debug', false);

    $this->getJson('/api/v1/__test/server-error')
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'SERVER_ERROR');
});

it('returns NOT_FOUND for unknown routes', function (): void {
    $this->getJson('/api/v1/__nope')
        ->assertStatus(404)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'NOT_FOUND');
});
