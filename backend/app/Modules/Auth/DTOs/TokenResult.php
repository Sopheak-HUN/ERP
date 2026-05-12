<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class TokenResult
{
    public function __construct(
        public string $accessToken,
        public CarbonImmutable $expiresAt,
        public User $user,
    ) {}
}
