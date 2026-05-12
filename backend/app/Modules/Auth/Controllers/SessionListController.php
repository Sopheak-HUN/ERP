<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Resources\SessionResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class SessionListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $tokens = PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->getKey())
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success(
            data: ['items' => SessionResource::collection($tokens)->resolve($request)],
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $token = PersonalAccessToken::query()->find($id);
        if (! $token instanceof PersonalAccessToken) {
            return ApiResponse::error('NOT_FOUND', 'Session not found.', status: 404);
        }

        if ($token->tokenable_id !== $user->getKey() || $token->tokenable_type !== $user->getMorphClass()) {
            return ApiResponse::error('FORBIDDEN', 'You may only revoke your own sessions.', status: 403);
        }

        if ($user->currentAccessToken()->getKey() === $token->getKey()) {
            return ApiResponse::error(
                'CANNOT_REVOKE_CURRENT_SESSION',
                'Use /auth/logout to revoke your current session.',
                status: 422,
            );
        }

        $token->delete();

        return ApiResponse::success(message: 'Session revoked.');
    }

    public function destroyOthers(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $currentId = $user->currentAccessToken()->getKey();

        $deleted = PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->getKey())
            ->whereKeyNot($currentId)
            ->delete();

        return ApiResponse::success(
            data: ['revoked_count' => $deleted],
            message: 'Other sessions revoked.',
        );
    }
}
