<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\DoctorPoint;
use App\Models\Meeting;
use App\Models\RepDoctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeetingController extends BaseMedicalRepController
{
    public function index(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $query = Meeting::where('rep_id', $rep->id)->with('doctor:id,full_name,email');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        return $this->success(['meetings' => $query->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $validated = $this->validateRequest($request, [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'scheduled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $assigned = RepDoctor::where('rep_id', $rep->id)->where('doctor_id', $validated['doctor_id'])->exists();
        if (!$assigned) {
            return $this->error('Doctor is not assigned to this rep', 403);
        }

        $meeting = Meeting::create([
            'rep_id' => $rep->id,
            'doctor_id' => $validated['doctor_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        return $this->success(['meeting' => $meeting], null, 201);
    }

    public function show(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) {
            return $meeting;
        }

        return $this->success(['meeting' => $meeting->load('doctor:id,full_name,email')]);
    }

    public function complete(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) {
            return $meeting;
        }

        DB::transaction(function () use ($meeting) {
            $meeting->update(['status' => 'completed']);
            DoctorPoint::create([
                'doctor_id' => $meeting->doctor_id,
                'source' => 'meeting',
                'source_id' => $meeting->id,
                'value' => 10,
                'description' => 'Meeting completed with rep',
            ]);
        });

        return $this->success(['meeting' => $meeting->fresh()], 'Meeting completed');
    }

    public function cancel(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) {
            return $meeting;
        }
        if ($meeting->status !== 'scheduled') {
            return $this->error('Only scheduled meetings can be cancelled', 422);
        }

        $meeting->update(['status' => 'cancelled']);
        return $this->success(['meeting' => $meeting->fresh()], 'Meeting cancelled');
    }

    public function getVideoRoom(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $meeting = Meeting::where('id', $id)->where('rep_id', $rep->id)->first();
        if (!$meeting) {
            return $this->error('Meeting not found', 404);
        }

        if ($meeting->status !== 'scheduled') {
            return $this->error('Meeting is not active', 403);
        }

        if (!$meeting->room_name) {
            $roomName = 'erep-'.$meeting->id.'-'.Str::random(10);
            $meeting->update(['room_name' => $roomName]);
        }

        return $this->success([
            'room_url' => 'https://meet.jit.si/'.$meeting->room_name,
            'room_name' => $meeting->room_name,
        ]);
    }

    private function ownedMeeting(int $id): Meeting|JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $meeting = Meeting::where('rep_id', $rep->id)->find($id);
        if (!$meeting) {
            return $this->error('Meeting not found', 404);
        }
        return $meeting;
    }
}
