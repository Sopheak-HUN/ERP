<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Exceptions\ApiException;

final class TwoFactorRequiredException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'TWO_FACTOR_REQUIRED',
            message: 'Two-factor authentication is required to continue.',
            status: 423,
        );
    }
}
