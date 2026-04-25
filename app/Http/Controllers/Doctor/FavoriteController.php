<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::query()
            ->where('doctor_id', $request->user()->id)
            ->with(['drug.company:id,company_name', 'drug.category:id,name'])
            ->latest()
            ->get();

        return $this->success(['favorites' => $favorites]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'drug_id' => ['required', 'exists:drugs,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $doctorId = (int) $request->user()->id;
        $exists = Favorite::where('doctor_id', $doctorId)
            ->where('drug_id', $validated['drug_id'])
            ->exists();

        if ($exists) {
            return $this->error('Already in favorites', 422);
        }

        Favorite::create([
            'doctor_id' => $doctorId,
            'drug_id' => $validated['drug_id'],
        ]);

        return $this->success([], 'Added to favorites', 201);
    }

    
    public function destroy(Request $request, int $drugId): JsonResponse
    {
        $favorite = Favorite::where('doctor_id', $request->user()->id)
            ->where('drug_id', $drugId)
            ->first();

        if (!$favorite) {
            return $this->error('Favorite not found', 404);
        }

        $favorite->delete();

        return $this->success([], 'Removed from favorites');
    }
}
