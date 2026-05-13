<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncRolesRequest extends FormRequest
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
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }
}
