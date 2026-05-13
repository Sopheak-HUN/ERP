<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Resources\PermissionResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null || ! $user->can('permissions.view')) {
            abort(403);
        }

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            data: ['items' => PermissionResource::collection($permissions)->resolve()],
        );
    }
}
