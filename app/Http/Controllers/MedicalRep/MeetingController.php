<?php

namespace App\Http\Controllers\MedicalRep;

use App\Events\PointsEarned;
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

        $query = Meeting::where('rep_id', $rep->id)->with(['doctor:id,full_name,email', 'rep:id,full_name,email']);
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
            'type' => ['nullable', 'string', 'max:50'],
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
            'type' => $validated['type'] ?? 'Online',
            'status' => 'pending',
        ]);

        return $this->success(['meeting' => $meeting->load(['doctor:id,full_name,email', 'rep:id,full_name,email'])], null, 201);
    }


    public function show(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) {
            return $meeting;
        }

        return $this->success(['meeting' => $meeting->load(['doctor:id,full_name,email', 'rep:id,full_name,email'])]);
    }


    public function complete(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) {
            return $meeting;
        }

        $point = DB::transaction(function () use ($meeting) {
            $meeting->update(['status' => 'completed', 'points_awarded' => 10]);

            return DoctorPoint::create([
                'doctor_id' => $meeting->doctor_id,
                'source' => 'meeting',
                'source_id' => $meeting->id,
                'value' => 10,
                'description' => 'Meeting completed with rep',
            ]);
        });

        broadcast(new PointsEarned($point->load('doctor')))->toOthers();

        return $this->success(['meeting' => $meeting->fresh()->load(['doctor:id,full_name,email,phone', 'rep:id,full_name,email,phone'])], 'Meeting completed');
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
        return $this->success(['meeting' => $meeting->fresh()->load(['doctor:id,full_name,email,phone', 'rep:id,full_name,email,phone'])], 'Meeting cancelled');
    }


    public function approve(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) return $meeting;

        if ($meeting->status !== 'pending') {
            return $this->error('Only pending meetings can be approved', 422);
        }

        $meeting->update(['status' => 'scheduled']);
        return $this->success(['meeting' => $meeting->fresh()->load(['doctor:id,full_name,email,phone', 'rep:id,full_name,email,phone'])], 'Meeting approved');
    }

    public function reject(int $id): JsonResponse
    {
        $meeting = $this->ownedMeeting($id);
        if ($meeting instanceof JsonResponse) return $meeting;

        if ($meeting->status !== 'pending') {
            return $this->error('Only pending meetings can be rejected', 422);
        }

        $meeting->update(['status' => 'rejected']);
        return $this->success(['meeting' => $meeting->fresh()->load(['doctor:id,full_name,email,phone', 'rep:id,full_name,email,phone'])], 'Meeting rejected');
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
