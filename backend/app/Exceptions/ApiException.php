<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Application-level exception carrying an error code and HTTP status.
 *
 * Thrown by services and caught by the global exception handler to be
 * rendered through the standard error envelope.
 */
class ApiException extends RuntimeException
{
    /**
     * @param  array<int|string, mixed>  $details
     */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 400,
        public readonly array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
