<?php

namespace App\Http\Controllers\MedicalRep;

use App\Http\Controllers\Controller;
use App\Models\DrugSample;
use App\Models\Meeting;
use App\Models\RepTarget;
// cspell:ignore Barryvdh
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function generate(): Response
    {
        $rep = auth('rep-api')->user();
        if (!$rep) {
            return $this->error('Unauthenticated', 401);
        }

        $data = [
            'rep' => $rep,
            'generated_at' => now(),
            'total_meetings' => Meeting::where('rep_id', $rep->id)->count(),
            'meetings' => Meeting::where('rep_id', $rep->id)
                ->with(['doctor:id,full_name,specialization'])
                ->get(),
            'samples' => DrugSample::where('rep_id', $rep->id)
                ->with(['drug:id,market_name'])
                ->get(),
            'targets' => RepTarget::where('rep_id', $rep->id)->get(),
        ];

        $pdf = Pdf::loadView('pdf.rep_report', $data);

        return $pdf->download('e-rep-rep-report-' . $rep->id . '.pdf');
    }
}
