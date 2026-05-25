<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\DrugSample;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\MedicalRep;
use App\Models\Post;
// cspell:ignore Barryvdh
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function generate(): Response
    {
        $admin = auth('admin-api')->user();
        if (!$admin) {
            return $this->error('Unauthenticated', 401);
        }

        $data = [
            'admin' => $admin,
            'generated_at' => now(),
            'total_doctors' => Doctor::count(),
            'total_reps' => MedicalRep::count(),
            'total_companies' => Company::count(),
            'total_drugs' => Drug::count(),
            'total_meetings' => Meeting::count(),
            'meetings_completed' => Meeting::where('status', 'completed')->count(),
            'total_samples' => DrugSample::count(),
            'samples_delivered' => DrugSample::where('status', 'delivered')->count(),
            'total_posts' => Post::count(),
            'reported_posts' => Post::has('reports')->count(),
            'total_events' => Event::count(),
        ];

        $pdf = Pdf::loadView('pdf.admin_report', $data);

        return $pdf->download('e-rep-admin-report.pdf');
    }
}
