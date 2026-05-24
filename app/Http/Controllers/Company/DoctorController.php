<?php

namespace App\Http\Controllers\Company;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

class DoctorController extends BaseCompanyController
{
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $doctors = Doctor::where('status', 'active')
            ->select('id', 'full_name', 'email', 'specialization')
            ->orderBy('full_name')
            ->get();

        return $this->success(['doctors' => $doctors]);
    }
}
