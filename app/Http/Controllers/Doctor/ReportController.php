<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use App\Models\DrugSample;
use App\Models\EventRequest;
use App\Models\Meeting;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{

    public function generate(): Response
    {
        $doctor = auth('doctor-api')->user();
        if (!$doctor) {
            return $this->error('Unauthenticated', 401);
        }

        $data = [
            'doctor' => $doctor,
            'generated_at' => now(),
            'total_points' => DoctorPoint::where('doctor_id', $doctor->id)->sum('value'),
            'meetings' => Meeting::where('doctor_id', $doctor->id)
                ->with(['rep:id,full_name'])
                ->get(),
            'samples' => DrugSample::where('doctor_id', $doctor->id)
                ->with(['drug:id,market_name'])
                ->get(),
            'events' => EventRequest::where('doctor_id', $doctor->id)
                ->where('status', 'approved')
                ->with(['event:id,title,event_date'])
                ->get(),
        ];

        $pdf = Pdf::loadView('pdf.doctor_report', $data);

        return $pdf->download('erep-report-' . $doctor->id . '.pdf');
    }
}
