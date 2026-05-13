<?php

declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $verified = $this->email_verified_at;

        /** @var \Illuminate\Support\Collection<int, string> $roleNames */
        $roleNames = $this->resource->getRoleNames();
        /** @var \Illuminate\Support\Collection<int, string> $permissionNames */
        $permissionNames = $this->resource->getAllPermissions()->pluck('name');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $verified !== null
                ? CarbonImmutable::parse($verified)->toIso8601String()
                : null,
            'has_two_factor' => $this->two_factor_confirmed_at !== null,
            'roles' => $roleNames->values()->all(),
            'permissions' => $permissionNames->values()->all(),
            'tenant_id' => $this->tenant_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
