<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class MeetingController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = Meeting::query()
            ->where('doctor_id', $request->user()->id)
            ->with(['doctor:id,full_name,email', 'rep:id,full_name,email,phone']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success(['meetings' => $query->latest()->get()]);
    }


    public function show(Request $request, int $id): JsonResponse
    {
        $meeting = Meeting::query()
            ->where('doctor_id', $request->user()->id)
            ->with(['doctor:id,full_name,email', 'rep:id,full_name,email,phone'])
            ->find($id);

        if (!$meeting) {
            return $this->error('Meeting not found', 404);
        }

        return $this->success(['meeting' => $meeting]);
    }


    #[OA\Post(
        path: '/api/doctor/meetings',
        summary: 'Create a meeting request',
        security: [['bearerAuth' => []]],
        tags: ['Doctor Meetings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rep_id', 'date', 'time', 'type'],
                properties: [
                    new OA\Property(property: 'rep_id', type: 'integer'),
                    new OA\Property(property: 'date', type: 'string', format: 'date'),
                    new OA\Property(property: 'time', type: 'string'),
                    new OA\Property(property: 'type', type: 'string', enum: ['Online', 'Offline']),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, maxLength: 5000),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Meeting created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'meeting', type: 'object'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'rep_id' => ['required', 'exists:medical_reps,id'],
            'date'   => ['required', 'date'],
            'time'   => ['required', 'date_format:H:i'],
            'type'   => ['required', 'in:Online,Offline'],
            'notes'  => ['nullable', 'string', 'max:5000'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $meeting = Meeting::create([
            'doctor_id'    => $request->user()->id,
            'rep_id'       => $validated['rep_id'],
            'scheduled_at' => $validated['date'] . ' ' . $validated['time'],
            'type'         => $validated['type'],
            'notes'        => $validated['notes'] ?? null,
            'status'       => 'pending',
        ]);

        DoctorPoint::create([
            'doctor_id' => $request->user()->id,
            'source' => 'meeting',
            'source_id' => $meeting->id,
            'value' => 20,
            'description' => 'Points earned from meeting request',
        ]);

        return $this->success(['meeting' => $meeting->load(['doctor:id,full_name,email', 'rep:id,full_name,email,phone'])], null, 201);
    }
}
