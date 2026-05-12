<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Resources\TokenResource;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function store(LoginRequest $request): JsonResponse
    {
        $deviceName = $request->input('device_name');
        $twoFactorCode = $request->input('two_factor_code');

        $result = $this->authService->attempt(new LoginDTO(
            email: (string) $request->string('email'),
            password: (string) $request->string('password'),
            ip: (string) ($request->ip() ?? ''),
            userAgent: $request->userAgent(),
            remember: $request->boolean('remember'),
            deviceName: is_string($deviceName) ? $deviceName : null,
            twoFactorCode: is_string($twoFactorCode) ? $twoFactorCode : null,
        ));

        return ApiResponse::success(
            data: TokenResource::make($result),
            message: 'Signed in successfully.',
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $this->authService->revokeCurrent($user);

        return ApiResponse::success(message: 'Signed out.');
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $result = $this->authService->refresh(
            $user,
            ip: (string) ($request->ip() ?? ''),
            userAgent: $request->userAgent(),
        );

        return ApiResponse::success(
            data: TokenResource::make($result),
            message: 'Token refreshed.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        return ApiResponse::success(
            data: UserResource::make($user),
        );
    }
}
