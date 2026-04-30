<?php

namespace App\Http\Controllers\MedicalRep;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $points = PointTransaction::query()
            ->where('pointable_type', 'medical_rep')
            ->where('pointable_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['points' => $points]);
    }

    public function total(Request $request): JsonResponse
    {
        $total = (int) PointTransaction::query()
            ->where('pointable_type', 'medical_rep')
            ->where('pointable_id', $request->user()->id)
            ->sum('points');

        return $this->success(['total' => $total]);
    }
}
