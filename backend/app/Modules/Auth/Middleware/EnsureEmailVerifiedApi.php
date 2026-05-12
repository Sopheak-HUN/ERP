<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureEmailVerifiedApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return ApiResponse::error(
                code: 'EMAIL_NOT_VERIFIED',
                message: 'Your email address has not been verified.',
                status: 403,
            );
        }

        return $next($request);
    }
}
