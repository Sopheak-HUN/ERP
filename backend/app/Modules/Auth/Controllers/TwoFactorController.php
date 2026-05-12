<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Events\TwoFactorDisabled;
use App\Modules\Auth\Events\TwoFactorEnabled;
use App\Modules\Auth\Requests\ConfirmTwoFactorRequest;
use App\Modules\Auth\Requests\DisableTwoFactorRequest;
use App\Modules\Auth\Services\TwoFactorService;
use App\Support\ApiResponse;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService,
        private readonly Hasher $hasher,
    ) {}

    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        if ($this->twoFactorService->isEnabledFor($user)) {
            return ApiResponse::error(
                'TWO_FACTOR_ALREADY_ENABLED',
                'Two-factor authentication is already enabled. Disable it first to set up again.',
                status: 409,
            );
        }

        $secret = $this->twoFactorService->generateSecret();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $otpAuthUrl = $this->twoFactorService->buildOtpAuthUrl($user, $secret);

        return ApiResponse::success(data: [
            'qr_svg' => $this->renderQrSvg($otpAuthUrl),
            'secret' => $secret,
        ]);
    }

    public function confirm(ConfirmTwoFactorRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $secret = $user->two_factor_secret;
        if (! is_string($secret) || $secret === '') {
            return ApiResponse::error(
                'TWO_FACTOR_NOT_PENDING',
                'No 2FA setup is in progress. Start by calling /two-factor/enable.',
                status: 409,
            );
        }

        if (! $this->twoFactorService->verifyTotp($secret, (string) $request->string('code'))) {
            return ApiResponse::error(
                'INVALID_TWO_FACTOR_CODE',
                'The provided code is invalid.',
                status: 422,
            );
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        DB::transaction(function () use ($user, $recoveryCodes): void {
            $user->forceFill([
                'two_factor_recovery_codes' => json_encode($recoveryCodes),
                'two_factor_confirmed_at' => now(),
            ])->save();
        });

        Event::dispatch(new TwoFactorEnabled($user));

        return ApiResponse::success(
            data: ['recovery_codes' => $recoveryCodes],
            message: 'Two-factor authentication enabled.',
        );
    }

    public function disable(DisableTwoFactorRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        if (! $this->hasher->check((string) $request->string('password'), (string) $user->password)) {
            return ApiResponse::error('AUTH_FAILED', 'Password is incorrect.', status: 422);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        Event::dispatch(new TwoFactorDisabled($user));

        return ApiResponse::success(message: 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(DisableTwoFactorRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        if (! $this->twoFactorService->isEnabledFor($user)) {
            return ApiResponse::error(
                'TWO_FACTOR_NOT_ENABLED',
                'Two-factor authentication is not enabled.',
                status: 409,
            );
        }

        if (! $this->hasher->check((string) $request->string('password'), (string) $user->password)) {
            return ApiResponse::error('AUTH_FAILED', 'Password is incorrect.', status: 422);
        }

        $codes = $this->twoFactorService->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($codes),
        ])->save();

        return ApiResponse::success(data: ['recovery_codes' => $codes]);
    }

    private function renderQrSvg(string $otpAuthUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(192, 2),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($otpAuthUrl);
    }
}
