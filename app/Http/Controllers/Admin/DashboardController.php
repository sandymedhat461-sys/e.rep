<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\MedicalRep;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{

    public function stats(): JsonResponse
    {
        return $this->success([
            'companies' => Company::count(),
            'doctors' => Doctor::count(),
            'reps' => MedicalRep::count(),
            'drugs' => Drug::count(),
            'meetings' => Meeting::count(),
            'events' => Event::count(),
            'pending_companies' => Company::where('status', 'pending')->count(),
            'pending_doctors' => Doctor::where('status', 'pending')->count(),
            'pending_reps' => MedicalRep::where('status', 'pending')->count(),
        ]);
    }
}
