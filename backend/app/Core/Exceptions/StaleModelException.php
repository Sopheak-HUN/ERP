<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Exceptions\ApiException;
use Throwable;

final class StaleModelException extends ApiException
{
    public function __construct(string $model, int|string $id, ?Throwable $previous = null)
    {
        parent::__construct(
            errorCode: 'STALE_MODEL',
            message: 'The resource has been modified by another process. Reload and try again.',
            status: 409,
            details: ['model' => $model, 'id' => $id],
            previous: $previous,
        );
    }
}
