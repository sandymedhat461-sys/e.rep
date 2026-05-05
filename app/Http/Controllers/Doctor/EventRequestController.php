<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\EventInvitation;
use App\Models\DoctorPoint;
use App\Models\Event;
use App\Models\EventRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRequestController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $requests = EventRequest::query()
            ->where('doctor_id', $request->user()->id)
            ->with('event:id,company_id,title,event_date,location')
            ->latest()
            ->get();

        return $this->success(['requests' => $requests]);
    }


    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'event_id' => ['required', 'exists:events,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $doctorId = (int) $request->user()->id;
        $eventId = (int) $validated['event_id'];

        $event = Event::find($eventId);
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        $totalPoints = DoctorPoint::where('doctor_id', $doctorId)->sum('value');

        if ($event->points_required && $totalPoints < $event->points_required) {
            return $this->error('Not enough points', 403);
        }

        $alreadyRequested = EventRequest::where('doctor_id', $doctorId)
            ->where('event_id', $eventId)
            ->exists();

        if ($alreadyRequested) {
            return $this->success([
                'message' => 'Already registered',
                'already' => true
            ], null, 200);
        }

        $eventRequest = EventRequest::create([
            'doctor_id' => $doctorId,
            'event_id'  => $eventId,
            'status'    => 'pending',
        ]);

        if ($event->points_required) {
            DoctorPoint::create([
                'doctor_id' => $doctorId,
                'source'    => 'event',
                'source_id' => $event->id,
                'value'     => - ((int) $event->points_required),
            ]);
        }

        $newTotal = DoctorPoint::where('doctor_id', $doctorId)->sum('value');

        return $this->success([
            'request'      => $eventRequest,
            'total_points' => $newTotal,
        ], null, 201);
    }
}
