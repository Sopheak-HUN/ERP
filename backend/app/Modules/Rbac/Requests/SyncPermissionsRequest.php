<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ];
    }
}
