<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->success(['company' => $request->user()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'company_name' => ['sometimes', 'string', 'max:255'],
            'hotline' => ['sometimes', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $company = $request->user();
        $company->fill($validated);
        $company->save();

        return $this->success(['company' => $company->fresh()]);
    }
}
