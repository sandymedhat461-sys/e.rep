<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseMedicalRepController
{
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $messages = Message::where('receiver_rep_id', $rep->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['messages' => $messages]);
    }

    public function store(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $validated = $this->validateRequest($request, [
            'receiver_id' => ['required', 'integer'],
            'receiver_type' => ['required', 'in:doctor,company'],
            'body' => ['required', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        if ($validated['receiver_type'] === 'company') {
            return $this->error('Messaging company receiver is not supported by current schema', 422);
        }

        $message = Message::create([
            'sender_type' => 'rep',
            'sender_id' => $rep->id,
            'receiver_doctor_id' => $validated['receiver_id'],
            'receiver_rep_id' => null,
            'content' => $validated['body'],
            'read_status' => false,
        ]);

        return $this->success(['message' => $message], null, 201);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $message = Message::where('id', $id)->where('receiver_rep_id', $rep->id)->first();
        if (!$message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['read_status' => true]);
        return $this->success([], 'Marked as read');
    }
}
