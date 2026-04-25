<?php

namespace App\Http\Controllers\Company;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends BaseCompanyController
{

    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $events = Event::where('company_id', $company->id)
            ->withCount([
                'eventRequests as requests_count',
                'eventInvitations as invitations_count',
            ])
            ->latest('event_date')
            ->get();

        return $this->success(['events' => $events]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:upcoming,ongoing,completed,cancelled'],
            'points_required' => ['nullable', 'integer', 'min:0'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $eventDate = $validated['event_date'];

        $event = Event::create([
            'company_id' => $company->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'event_date' => $eventDate,
            'max_capacity' => $validated['max_capacity'] ?? null,
            'status' => $validated['status'] ?? 'upcoming',
            'points_required' => $validated['points_required'] ?? null,
        ]);

        return $this->success(['event' => $event], null, 201);
    }

    
    public function show(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $event = Event::where('company_id', $company->id)
            ->withCount([
                'eventRequests as requests_count',
                'eventInvitations as invitations_count',
            ])
            ->find($id);
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        return $this->success(['event' => $event]);
    }

    
    public function update(Request $request, int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $event = Event::where('company_id', $company->id)->find($id);
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:upcoming,ongoing,completed,cancelled'],
            'points_required' => ['nullable', 'integer', 'min:0'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $eventDate = $validated['event_date'];
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'event_date' => $eventDate,
            'max_capacity' => array_key_exists('max_capacity', $validated) ? $validated['max_capacity'] : $event->max_capacity,
            'status' => $validated['status'] ?? $event->status,
            'points_required' => $validated['points_required'] ?? $event->points_required,
        ]);

        $event = Event::where('company_id', $company->id)
            ->withCount([
                'eventRequests as requests_count',
                'eventInvitations as invitations_count',
            ])
            ->find($event->id);

        return $this->success(['event' => $event]);
    }

    
    public function destroy(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $event = Event::where('company_id', $company->id)->find($id);
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        $event->delete();
        return $this->success([], 'Event deleted');
    }
}
