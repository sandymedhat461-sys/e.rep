<?php

namespace App\Http\Controllers\Company;

use App\Models\Drug;
use App\Models\DrugReview;
use Illuminate\Http\JsonResponse;

class DrugReviewController extends BaseCompanyController
{
    public function index(int $drugId): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $drug = Drug::where('company_id', $company->id)->find($drugId);
        if (!$drug) {
            return $this->error('Drug not found', 404);
        }

        $reviews = DrugReview::where('drug_id', $drugId)
            ->with('doctor:id,full_name')
            ->latest()
            ->get();

        $averageRating = $reviews->avg('rating');

        return $this->success([
            'reviews' => $reviews,
            'average_rating' => $averageRating ? round($averageRating, 2) : null,
            'reviews_count' => $reviews->count(),
        ]);
    }
}
