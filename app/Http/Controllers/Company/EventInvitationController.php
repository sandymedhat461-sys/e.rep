<?php

namespace App\Http\Controllers\Company;

use App\Models\Doctor;
use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventInvitationController extends BaseCompanyController
{
   
    public function invite(Request $request, int $eventId): JsonResponse
    {
        $event = $this->ownedEvent($eventId);
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $validated = $this->validateRequest($request, [
            'doctor_ids' => ['required', 'array', 'min:1'],
            'doctor_ids.*' => ['exists:doctors,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $doctorIds = Doctor::whereIn('id', $validated['doctor_ids'])->pluck('id')->all();
        $created = [];
        foreach ($doctorIds as $doctorId) {
            $created[] = EventInvitation::firstOrCreate(
                ['event_id' => $event->id, 'doctor_id' => $doctorId],
                ['status' => 'pending', 'invited_at' => now()]
            );
        }

        return $this->success(['invitations' => $created], null, 201);
    }

    
    public function index(int $eventId): JsonResponse
    {
        $event = $this->ownedEvent($eventId);
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $invitations = EventInvitation::where('event_id', $event->id)
            ->with('doctor:id,full_name,email')
            ->latest()
            ->get();

        return $this->success(['invitations' => $invitations]);
    }

    private function ownedEvent(int $eventId): Event|JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $event = Event::where('company_id', $company->id)->find($eventId);
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        return $event;
    }
}
