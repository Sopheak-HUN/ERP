<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Rbac\Requests\SyncRolesRequest;
use App\Modules\Rbac\Resources\RoleResource;
use App\Modules\Rbac\Services\UserRoleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UserRoleController extends Controller
{
    public function __construct(
        private readonly UserRoleService $service,
    ) {}

    public function index(int $user): JsonResponse
    {
        /** @var User $target */
        $target = User::query()->findOrFail($user);
        $this->authorize('viewRoles', $target);

        return ApiResponse::success(
            data: ['items' => RoleResource::collection($target->roles)->resolve()],
        );
    }

    public function sync(SyncRolesRequest $request, int $user): JsonResponse
    {
        /** @var User $target */
        $target = User::query()->findOrFail($user);
        $this->authorize('syncRoles', $target);

        /** @var list<int> $ids */
        $ids = array_map('intval', (array) $request->input('role_ids', []));

        $roles = $this->service->sync($target, $ids);

        return ApiResponse::success(
            data: ['items' => RoleResource::collection($roles)->resolve()],
            message: 'Roles updated.',
        );
    }
}
