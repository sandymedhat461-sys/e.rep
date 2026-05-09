<?php

namespace App\Http\Controllers\Doctor;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
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
                    $q->where('receiver_id', $doctorId)->whereIn('receiver_type', ['doctor', 'Doctor', 'App\\Models\\Doctor']);
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
            'receiver_type' => 'medical_rep',
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
            ->whereIn('receiver_type', ['doctor', 'Doctor', 'App\\Models\\Doctor'])
            ->first();

        if (!$message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['is_read' => true]);

        return $this->success([], 'Marked as read');
    }

    public function conversations(Request $request): JsonResponse
    {
        $doctorId = (int) $request->user()->id;
        $doctorTypes = ['doctor', 'Doctor', 'App\\Models\\Doctor'];

        $messages = Message::query()
            ->where(function ($q) use ($doctorId, $doctorTypes) {
                $q->where(function ($inner) use ($doctorId, $doctorTypes) {
                    $inner->where('sender_id', $doctorId)->whereIn('sender_type', $doctorTypes);
                })->orWhere(function ($inner) use ($doctorId, $doctorTypes) {
                    $inner->where('receiver_id', $doctorId)->whereIn('receiver_type', $doctorTypes);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $conversations = $messages->groupBy(function ($msg) use ($doctorId, $doctorTypes) {
            $isSender = in_array($msg->sender_type, $doctorTypes) && $msg->sender_id == $doctorId;
            return 'rep_' . ($isSender ? $msg->receiver_id : $msg->sender_id);
        })->map(function ($group, $key) use ($doctorId, $doctorTypes) {
            $latest = $group->first();
            $isSender = in_array($latest->sender_type, $doctorTypes) && $latest->sender_id == $doctorId;
            $partnerId = $isSender ? $latest->receiver_id : $latest->sender_id;
            $partner = \App\Models\MedicalRep::find($partnerId, ['id', 'full_name']);
            return [
                'partner_id' => $partnerId,
                'partner_type' => 'medical_rep',
                'partner_name' => $partner?->full_name ?? 'Unknown',
                'latest_message' => $latest->body,
                'latest_time' => $latest->created_at,
                'unread_count' => $group->where('receiver_id', $doctorId)->where('is_read', false)->count(),
            ];
        })->values();

        return $this->success(['conversations' => $conversations]);
    }

    public function conversation(Request $request, int $partnerId): JsonResponse
    {
        $doctorId = (int) $request->user()->id;
        $doctorTypes = ['doctor', 'Doctor', 'App\\Models\\Doctor'];

        $messages = Message::query()
            ->where(function ($q) use ($doctorId, $doctorTypes, $partnerId) {
                $q->where(function ($inner) use ($doctorId, $doctorTypes, $partnerId) {
                    $inner->where('sender_id', $doctorId)
                        ->whereIn('sender_type', $doctorTypes)
                        ->where('receiver_id', $partnerId)
                        ->where('receiver_type', 'medical_rep');
                })->orWhere(function ($inner) use ($doctorId, $doctorTypes, $partnerId) {
                    $inner->where('sender_id', $partnerId)
                        ->where('sender_type', 'medical_rep')
                        ->where('receiver_id', $doctorId)
                        ->whereIn('receiver_type', $doctorTypes);
                });
            })
            ->orderBy('created_at')
            ->get();

        $partner = \App\Models\MedicalRep::find($partnerId, ['id', 'full_name']);

        return $this->success([
            'messages' => $messages,
            'partner' => $partner,
        ]);
    }
}
