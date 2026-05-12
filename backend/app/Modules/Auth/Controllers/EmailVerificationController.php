<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Events\EmailVerified;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

final class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(message: 'Email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(message: 'Verification link sent.');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse|JsonResponse
    {
        if (! URL::hasValidSignature($request)) {
            return ApiResponse::error(
                'INVALID_SIGNATURE',
                'The verification link is invalid or has expired.',
                status: 403,
            );
        }

        $user = User::query()->find($id);
        if (! $user instanceof User) {
            return ApiResponse::error(
                'INVALID_SIGNATURE',
                'The verification link is invalid or has expired.',
                status: 403,
            );
        }

        if (! hash_equals(sha1((string) $user->getEmailForVerification()), $hash)) {
            return ApiResponse::error(
                'INVALID_SIGNATURE',
                'The verification link is invalid or has expired.',
                status: 403,
            );
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            Event::dispatch(new EmailVerified($user));
        }

        $base = rtrim((string) config('app.frontend_url'), '/');

        return redirect()->away($base.'/auth/login?verified=1');
    }
}
