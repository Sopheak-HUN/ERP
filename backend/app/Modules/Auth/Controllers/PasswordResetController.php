<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Events\PasswordReset as PasswordResetEvent;
use App\Modules\Auth\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Support\ApiResponse;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class PasswordResetController extends Controller
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {}

    public function requestLink(ForgotPasswordRequest $request): JsonResponse
    {
        Password::broker()->sendResetLink([
            'email' => (string) $request->string('email'),
        ]);

        // Always return success — no enumeration.
        return ApiResponse::success(
            message: 'If that email exists in our system, we have sent a password reset link.',
        );
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->reset(
            [
                'email' => (string) $request->string('email'),
                'password' => (string) $request->string('password'),
                'password_confirmation' => (string) $request->string('password_confirmation'),
                'token' => (string) $request->string('token'),
            ],
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $user->forceFill([
                        'password' => $this->hasher->make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    // Invalidate all existing API tokens — user must re-login.
                    $user->tokens()->delete();
                });

                Event::dispatch(new PasswordResetEvent($user));
            },
        );

        return match ($status) {
            Password::PASSWORD_RESET => ApiResponse::success(message: 'Password has been reset.'),
            Password::INVALID_TOKEN, Password::INVALID_USER => ApiResponse::error(
                code: 'INVALID_RESET_TOKEN',
                message: 'The password reset token is invalid or has expired.',
                status: 422,
            ),
            Password::RESET_THROTTLED => ApiResponse::error(
                code: 'RESET_THROTTLED',
                message: 'Please wait before requesting another reset.',
                status: 429,
            ),
            default => ApiResponse::error(
                code: 'PASSWORD_RESET_FAILED',
                message: (string) __($status),
                status: 422,
            ),
        };
    }
}
