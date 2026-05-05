<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\Event;
use App\Models\MedicalRep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function generate(): Response
    {
        $company = auth('company-api')->user();
        if (!$company) {
            return $this->error('Unauthenticated', 401);
        }

        $data = [
            'company' => $company,
            'generated_at' => now(),
            'drugs' => Drug::where('company_id', $company->id)
                ->with('category')
                ->get(),
            'events' => Event::where('company_id', $company->id)
                ->withCount('eventRequests')
                ->get(),
            'reps' => MedicalRep::where('company_id', $company->id)->get(),
        ];

        $pdf = Pdf::loadView('pdf.company_report', $data);

        return $pdf->download('erep-company-report-' . $company->id . '.pdf');
    }
}
