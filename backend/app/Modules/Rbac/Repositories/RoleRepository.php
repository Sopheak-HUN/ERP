<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Repositories;

use App\Modules\Rbac\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RoleRepository
{
    /**
     * @param  array{search?: ?string, per_page?: ?int}  $filters
     * @return LengthAwarePaginator<int, Role>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 25);
        $perPage = max(1, min($perPage, 100));

        return Role::query()
            ->when(
                isset($filters['search']) && $filters['search'] !== '',
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%'),
            )
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Role
    {
        /** @var Role */
        return Role::query()->with('permissions')->findOrFail($id);
    }

    public function findByName(string $name): ?Role
    {
        /** @var Role|null */
        return Role::query()->where('name', $name)->where('guard_name', 'web')->first();
    }
}
