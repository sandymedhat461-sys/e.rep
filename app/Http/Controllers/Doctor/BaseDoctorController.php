<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

abstract class BaseDoctorController extends Controller
{
    protected function doctorOrForbidden(): Doctor|JsonResponse
    {
        $doctor = auth('doctor-api')->user();
        if (!$doctor) {
            return $this->error('Unauthenticated', 401);
        }

        if ((string) $doctor->status !== 'active') {
            return $this->error('Doctor account is not active', 403);
        }

        return $doctor;
    }
}
