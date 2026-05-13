<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Exceptions;

use App\Exceptions\ApiException;

final class SystemRoleProtectedException extends ApiException
{
    public function __construct(string $message = 'System roles are read-only.')
    {
        parent::__construct(
            errorCode: 'SYSTEM_ROLE_PROTECTED',
            message: $message,
            status: 422,
        );
    }
}
