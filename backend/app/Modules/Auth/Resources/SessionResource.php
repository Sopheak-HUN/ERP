<?php

declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @mixin PersonalAccessToken
 */
final class SessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentToken = $request->user()?->currentAccessToken();
        $currentId = $currentToken instanceof PersonalAccessToken ? $currentToken->getKey() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'last_used_ip' => $this->last_used_ip,
            'created_at' => $this->created_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_current' => $currentId !== null && $currentId === $this->id,
        ];
    }
}
