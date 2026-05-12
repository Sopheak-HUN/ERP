<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Exceptions\ApiException;

final class AccountLockedException extends ApiException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct(
            errorCode: 'ACCOUNT_LOCKED',
            message: 'Too many failed login attempts. Please try again later.',
            status: 423,
            details: ['retry_after' => $retryAfterSeconds],
        );
    }
}
