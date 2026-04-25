<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use App\Models\Drug;
use App\Models\DrugReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrugReviewController extends Controller
{
   
    public function index(int $drugId): JsonResponse
    {
        if (!Drug::whereKey($drugId)->exists()) {
            return $this->error('Drug not found', 404);
        }

        $reviews = DrugReview::query()
            ->where('drug_id', $drugId)
            ->with('doctor:id,full_name')
            ->latest()
            ->get();

        return $this->success(['reviews' => $reviews]);
    }

    
    public function store(Request $request, int $drugId): JsonResponse
    {
        if (!Drug::whereKey($drugId)->exists()) {
            return $this->error('Drug not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $doctorId = (int) $request->user()->id;
        $alreadyReviewed = DrugReview::where('drug_id', $drugId)
            ->where('doctor_id', $doctorId)
            ->exists();

        if ($alreadyReviewed) {
            return $this->error('You already reviewed this drug', 422);
        }

        $review = DrugReview::create([
            'drug_id' => $drugId,
            'doctor_id' => $doctorId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        DoctorPoint::create([
            'doctor_id' => $doctorId,
            'source' => 'review',
            'source_id' => $review->id,
            'value' => 5,
        ]);

        return $this->success(['review' => $review], null, 201);
    }
}
