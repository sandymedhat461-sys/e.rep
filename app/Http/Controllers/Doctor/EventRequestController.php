<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\EventInvitation;
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

        $alreadyRequested = EventRequest::where('doctor_id', $doctorId)->where('event_id', $eventId)->exists();
        if ($alreadyRequested) {
            return $this->error('Already registered for this event', 422);
        }

        $acceptedInvitation = EventInvitation::where('doctor_id', $doctorId)
            ->where('event_id', $eventId)
            ->where('status', 'accepted')
            ->exists();
        if ($acceptedInvitation) {
            return $this->error('Already invited and accepted', 422);
        }

        $eventRequest = EventRequest::create([
            'doctor_id' => $doctorId,
            'event_id' => $eventId,
            'status' => 'pending',
        ]);

        return $this->success(['request' => $eventRequest], null, 201);
    }
}
