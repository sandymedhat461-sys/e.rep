<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\DrugSampleRequest;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\MedicalRep;
use App\Models\Post;
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

    public function reportStats(): JsonResponse
    {
        return $this->success([
            'doctors' => [
                'total' => Doctor::count(),
                'active' => Doctor::where('status', 'active')->count(),
                'pending' => Doctor::where('status', 'pending')->count(),
                'blocked' => Doctor::where('status', 'blocked')->count(),
            ],
            'medical_reps' => [
                'total' => MedicalRep::count(),
                'active' => MedicalRep::where('status', 'active')->count(),
                'pending' => MedicalRep::where('status', 'pending')->count(),
                'blocked' => MedicalRep::where('status', 'blocked')->count(),
            ],
            'companies' => [
                'total' => Company::count(),
                'approved' => Company::where('status', 'approved')->count(),
                'pending' => Company::where('status', 'pending')->count(),
                'blocked' => Company::where('status', 'blocked')->count(),
            ],
            'drugs' => [
                'total' => Drug::count(),
            ],
            'meetings' => [
                'total' => Meeting::count(),
                'completed' => Meeting::where('status', 'completed')->count(),
                'scheduled' => Meeting::where('status', 'scheduled')->count(),
                'cancelled' => Meeting::where('status', 'cancelled')->count(),
            ],
            'samples' => [
                'total' => DrugSampleRequest::count(),
                'pending' => DrugSampleRequest::where('status', 'pending')->count(),
                'delivered' => DrugSampleRequest::where('status', 'delivered')->count(),
            ],
            'events' => [
                'total' => Event::count(),
                'upcoming' => Event::where('event_date', '>=', now())->count(),
                'completed' => Event::where('event_date', '<', now())->count(),
            ],
            'posts' => [
                'total' => Post::count(),
                'reported' => Post::has('reports')->count(),
            ],
        ]);
    }
}
