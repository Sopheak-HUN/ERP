<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rbac\Repositories\RoleRepository;
use App\Modules\Rbac\Requests\SyncPermissionsRequest;
use App\Modules\Rbac\Resources\PermissionResource;
use App\Modules\Rbac\Resources\RoleResource;
use App\Modules\Rbac\Services\RoleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

final class RolePermissionController extends Controller
{
    public function __construct(
        private readonly RoleService $service,
        private readonly RoleRepository $repository,
    ) {}

    public function index(int $role): JsonResponse
    {
        $model = $this->repository->findOrFail($role);
        $this->authorize('view', $model);

        return ApiResponse::success(
            data: ['items' => PermissionResource::collection($model->permissions)->resolve()],
        );
    }

    public function sync(SyncPermissionsRequest $request, int $role): JsonResponse
    {
        $model = $this->repository->findOrFail($role);
        $this->authorize('syncPermissions', $model);

        /** @var list<int> $ids */
        $ids = array_map('intval', (array) $request->input('permission_ids', []));

        $updated = $this->service->syncPermissions($model, $ids);

        return ApiResponse::success(
            data: RoleResource::make($updated),
            message: 'Permissions updated.',
        );
    }
}
