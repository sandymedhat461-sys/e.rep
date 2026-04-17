<?php

namespace App\Http\Controllers\Doctor;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalRep;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctorId = (int) $request->user()->id;
        $messages = Message::query()
            ->where(function ($query) use ($doctorId) {
                $query->where(function ($q) use ($doctorId) {
                    $q->where('sender_type', 'doctor')->where('sender_id', $doctorId);
                })->orWhere(function ($q) use ($doctorId) {
                    $q->where('receiver_id', $doctorId)->where('receiver_type', Doctor::class);
                });
            })
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['messages' => $messages]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'receiver_rep_id' => ['required', 'exists:medical_reps,id'],
            'content' => ['required', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $message = Message::create([
            'sender_type' => 'doctor',
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_rep_id'],
            'receiver_type' => MedicalRep::class,
            'body' => $validated['content'],
            'is_read' => false,
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return $this->success(['message' => $message], null, 201);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $message = Message::where('id', $id)
            ->where('receiver_id', $request->user()->id)
            ->where('receiver_type', Doctor::class)
            ->first();

        if (!$message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['is_read' => true]);

        return $this->success([], 'Marked as read');
    }
}
