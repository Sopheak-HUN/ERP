<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', $this->passwordRule()],
        ];
    }

    private function passwordRule(): Password
    {
        $rule = Password::min(12)->letters()->numbers();

        // HIBP breach check makes a network call; skip in tests.
        if (! app()->environment('testing')) {
            $rule = $rule->uncompromised();
        }

        return $rule;
    }
}
