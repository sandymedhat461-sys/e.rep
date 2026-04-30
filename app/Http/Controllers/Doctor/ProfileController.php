<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->success(['doctor' => $request->user()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string'],
            'hospital_name' => ['sometimes', 'string'],
            'specialization' => ['sometimes', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $doctor = $request->user();
        $doctor->fill($validated);
        $doctor->save();

        return $this->success(['doctor' => $doctor->fresh()]);
    }
}
