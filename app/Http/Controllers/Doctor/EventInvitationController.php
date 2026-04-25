<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\EventInvitation;
use App\Models\EventRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventInvitationController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = EventInvitation::query()
            ->where('doctor_id', $request->user()->id)
            ->with(['event:id,title,event_date,location', 'invitedByRep:id,full_name,email']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success(['invitations' => $query->latest()->get()]);
    }

    
    public function accept(Request $request, int $id): JsonResponse
    {
        $invitation = EventInvitation::where('id', $id)
            ->where('doctor_id', $request->user()->id)
            ->first();

        if (!$invitation) {
            return $this->error('Invitation not found', 404);
        }
        if ($invitation->status !== 'pending') {
            return $this->error('Already responded', 422);
        }

        $invitation->update([
            'status' => 'accepted',
            'responded_at' => Carbon::now(),
        ]);

        EventRequest::updateOrCreate(
            ['doctor_id' => $request->user()->id, 'event_id' => $invitation->event_id],
            ['status' => 'approved']
        );

        return $this->success([], 'Invitation accepted');
    }

    
    public function decline(Request $request, int $id): JsonResponse
    {
        $invitation = EventInvitation::where('id', $id)
            ->where('doctor_id', $request->user()->id)
            ->first();

        if (!$invitation) {
            return $this->error('Invitation not found', 404);
        }
        if ($invitation->status !== 'pending') {
            return $this->error('Already responded', 422);
        }

        $invitation->update([
            'status' => 'declined',
            'responded_at' => Carbon::now(),
        ]);

        return $this->success([], 'Invitation declined');
    }
}
