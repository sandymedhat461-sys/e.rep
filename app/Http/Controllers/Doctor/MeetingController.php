<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = Meeting::query()
            ->where('doctor_id', $request->user()->id)
            ->with('rep:id,full_name,email,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success(['meetings' => $query->latest()->get()]);
    }


    public function show(Request $request, int $id): JsonResponse
    {
        $meeting = Meeting::query()
            ->where('doctor_id', $request->user()->id)
            ->with('rep:id,full_name,email,phone')
            ->find($id);

        if (!$meeting) {
            return $this->error('Meeting not found', 404);
        }

        return $this->success(['meeting' => $meeting]);
    }


    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'rep_id' => ['required', 'exists:medical_reps,id'],
            'date'   => ['required', 'date'],
            'time'   => ['required', 'string'],
            'type'   => ['required', 'in:Online,Offline'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $meeting = Meeting::create([
            'doctor_id'    => $request->user()->id,
            'rep_id'       => $validated['rep_id'],
            'scheduled_at' => $validated['date'] . ' ' . $validated['time'],
            'type'         => $validated['type'],
            'status'       => 'pending',
        ]);

        DoctorPoint::create([
            'doctor_id' => $request->user()->id,
            'source' => 'meeting',
            'source_id' => $meeting->id,
            'value' => 20,
            'description' => 'Points earned from meeting request',
        ]);

        return $this->success(['meeting' => $meeting], null, 201);
    }
}
