<?php

declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use App\Modules\Auth\DTOs\TokenResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TokenResult
 */
final class TokenResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $this->expiresAt->toIso8601String(),
            'user' => (new UserResource($this->user))->resolve($request),
        ];
    }
}
