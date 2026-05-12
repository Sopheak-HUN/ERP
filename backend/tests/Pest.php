<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()
    ->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeApiSuccess', function () {
    return $this->toHaveKey('success', true)->toHaveKey('data');
});

expect()->extend('toBeApiError', function (?string $code = null) {
    $this->toHaveKey('success', false)->toHaveKey('error');
    if ($code !== null) {
        $this->{'error.code'}->toBe($code);
    }

    return $this;
});
