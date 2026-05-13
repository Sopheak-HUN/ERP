<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:64', 'regex:/^[a-z0-9._-]+$/', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
