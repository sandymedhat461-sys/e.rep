<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\Doctor;
use App\Models\RepDoctor;
use Illuminate\Http\JsonResponse;

class AssignedDoctorController extends BaseMedicalRepController
{
    
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $doctors = Doctor::whereIn('id', RepDoctor::where('rep_id', $rep->id)->pluck('doctor_id'))->get();
        return $this->success(['doctors' => $doctors]);
    }

    
    public function show(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $assigned = RepDoctor::where('rep_id', $rep->id)->where('doctor_id', $id)->exists();
        if (!$assigned) {
            return $this->error('Doctor not found', 404);
        }

        return $this->success(['doctor' => Doctor::find($id)]);
    }
}
