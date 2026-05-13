<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:64',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
