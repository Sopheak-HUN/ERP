<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records the requesting IP on the current personal access token. Lets the
 * /sessions endpoint surface "last seen from <ip>" without hitting the cost
 * of a write on every protected request — Sanctum already updates
 * `last_used_at`, this just piggybacks `last_used_ip`.
 */
final class TrackTokenMetadata
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();
        // Skip when the action deleted the token (logout, revoke-self via refresh,
        // session revocation): the in-memory model would `INSERT` on save().
        if ($token instanceof PersonalAccessToken && $token->exists) {
            $ip = (string) ($request->ip() ?? '');
            if ($ip !== '' && $token->last_used_ip !== $ip) {
                $token->forceFill(['last_used_ip' => $ip])->save();
            }
        }

        return $response;
    }
}
