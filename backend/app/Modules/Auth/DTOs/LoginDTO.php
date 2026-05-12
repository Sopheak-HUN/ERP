<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $ip,
        public ?string $userAgent = null,
        public bool $remember = false,
        public ?string $deviceName = null,
        public ?string $twoFactorCode = null,
    ) {}
}
