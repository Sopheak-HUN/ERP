<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a unique X-Request-Id header to every request/response pair.
 *
 * Trusts a client-supplied X-Request-Id if it looks like a ULID; otherwise
 * generates a fresh one. The id is also bound into the request attributes
 * and the log context, so every log line for this request can be correlated.
 */
final class RequestId
{
    public const HEADER = 'X-Request-Id';

    public const ATTRIBUTE = 'request_id';

    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->headers->get(self::HEADER, '');
        $requestId = $this->isValidUlid($incoming) ? $incoming : (string) Str::ulid();

        $request->attributes->set(self::ATTRIBUTE, $requestId);
        $request->headers->set(self::HEADER, $requestId);

        logger()->withContext(['request_id' => $requestId]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }

    private function isValidUlid(string $value): bool
    {
        return $value !== '' && Str::isUlid($value);
    }
}
