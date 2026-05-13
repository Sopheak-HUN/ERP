<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Resources;

use App\Modules\Rbac\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Permission
 */
final class PermissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group,
            'guard_name' => $this->guard_name,
        ];
    }
}
