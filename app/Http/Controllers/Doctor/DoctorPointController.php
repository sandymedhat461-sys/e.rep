<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorPointController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $points = DoctorPoint::where('doctor_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['source', 'source_id', 'value', 'created_at']);

        return $this->success(['points' => $points]);
    }

    
    public function total(Request $request): JsonResponse
    {
        $total = (int) DoctorPoint::where('doctor_id', $request->user()->id)->sum('value');

        return $this->success(['total_points' => $total]);
    }
}
