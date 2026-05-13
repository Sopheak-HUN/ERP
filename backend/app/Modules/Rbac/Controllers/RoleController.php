<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rbac\Models\Role;
use App\Modules\Rbac\Repositories\RoleRepository;
use App\Modules\Rbac\Requests\StoreRoleRequest;
use App\Modules\Rbac\Requests\UpdateRoleRequest;
use App\Modules\Rbac\Resources\RoleResource;
use App\Modules\Rbac\Services\RoleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $service,
        private readonly RoleRepository $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $perPageInput = $request->input('per_page');
        $searchInput = $request->input('search');

        $paginator = $this->repository->paginate([
            'search' => is_string($searchInput) ? $searchInput : null,
            'per_page' => is_numeric($perPageInput) ? (int) $perPageInput : null,
        ]);

        return ApiResponse::paginated(
            RoleResource::collection($paginator),
        );
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->service->create([
            'name' => (string) $request->string('name'),
            'description' => $request->input('description'),
        ]);

        return ApiResponse::success(
            data: RoleResource::make($role->loadMissing('permissions')),
            message: 'Role created.',
            status: 201,
        );
    }

    public function show(int $role): JsonResponse
    {
        $model = $this->repository->findOrFail($role);
        $this->authorize('view', $model);

        return ApiResponse::success(
            data: RoleResource::make($model),
        );
    }

    public function update(UpdateRoleRequest $request, int $role): JsonResponse
    {
        $model = $this->repository->findOrFail($role);
        $this->authorize('update', $model);

        $updated = $this->service->update($model, $request->only(['name', 'description']));

        return ApiResponse::success(
            data: RoleResource::make($updated),
            message: 'Role updated.',
        );
    }

    public function destroy(int $role): JsonResponse
    {
        $model = $this->repository->findOrFail($role);
        $this->authorize('delete', $model);

        $this->service->delete($model);

        return ApiResponse::success(message: 'Role deleted.');
    }
}
