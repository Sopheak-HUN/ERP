<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Organization\Models\Company;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanyController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $tenantId = $user->tenant_id;
        if (! $tenantId) {
            return ApiResponse::error('NO_TENANT', 'User does not belong to any company.', status: 404);
        }

        $company = Company::find($tenantId);
        if (! $company) {
            return ApiResponse::error('NOT_FOUND', 'Company not found.', status: 404);
        }

        return ApiResponse::success(data: $company);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('UNAUTHENTICATED', 'Unauthenticated.', status: 401);
        }

        $tenantId = $user->tenant_id;
        if (! $tenantId) {
            return ApiResponse::error('NO_TENANT', 'User does not belong to any company.', status: 404);
        }

        $company = Company::find($tenantId);
        if (! $company) {
            return ApiResponse::error('NOT_FOUND', 'Company not found.', status: 404);
        }

        // Ideally handled via FormRequest with proper validation
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'registration_number' => 'sometimes|nullable|string|max:255',
            'tax_id' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string',
            'website' => 'sometimes|nullable|url|max:255',
            'currency' => 'sometimes|string|max:3',
            'timezone' => 'sometimes|string|max:255',
        ]);

        $company->update($validated);

        return ApiResponse::success(data: $company, message: 'Company settings updated.');
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User || ! $user->tenant_id) {
            return ApiResponse::error('NO_TENANT', 'User does not belong to any company.', status: 404);
        }

        $company = Company::find($user->tenant_id);
        if (! $company) {
            return ApiResponse::error('NOT_FOUND', 'Company not found.', status: 404);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $file = $request->file('logo');
        if (! $file) {
            return ApiResponse::error('INVALID_FILE', 'No file uploaded.', status: 400);
        }

        $path = $file->store('logos', 'public');
        
        if ($company->logo_path) {
            $oldPath = str_replace('/storage/', '', $company->logo_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }

        $url = \Illuminate\Support\Facades\Storage::url($path);
        
        $company->update(['logo_path' => $url]);

        return ApiResponse::success(data: ['logo_path' => $url], message: 'Logo uploaded successfully.');
    }
}
