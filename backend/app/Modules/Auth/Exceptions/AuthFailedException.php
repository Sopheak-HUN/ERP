<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Exceptions\ApiException;

final class AuthFailedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'AUTH_FAILED',
            message: 'These credentials do not match our records.',
            status: 401,
        );
    }
}
