<?php

namespace App\Http\Controllers\MedicalRep;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->success(['rep' => $request->user()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $rep = $request->user();
        $rep->fill($validated);
        $rep->save();

        return $this->success(['rep' => $rep->fresh()]);
    }
}
