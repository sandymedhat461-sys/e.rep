<?php

namespace App\Http\Controllers\MedicalRep;

use App\Events\MessageSent;
use App\Models\Doctor;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseMedicalRepController
{
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $messages = Message::query()
            ->where(function ($q) use ($rep) {
                $q->where(function ($inner) use ($rep) {
                    $inner->where('sender_id', $rep->id)
                          ->where('sender_type', 'medical_rep');
                })->orWhere(function ($inner) use ($rep) {
                    $inner->where('receiver_id', $rep->id)
                          ->where('receiver_type', 'medical_rep');
                });
            })
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['messages' => $messages]);
    }


    public function store(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $validated = $this->validateRequest($request, [
            'receiver_id' => ['required', 'integer'],
            'body'        => ['required', 'string'],
        ]);
        if ($validated instanceof JsonResponse) return $validated;

        if (!Doctor::whereKey($validated['receiver_id'])->exists()) {
            return $this->error('Doctor not found', 404);
        }

        $message = Message::create([
            'sender_type'   => 'medical_rep',
            'sender_id'     => $rep->id,
            'receiver_id'   => $validated['receiver_id'],
            'receiver_type' => 'doctor',
            'body'          => $validated['body'],
            'is_read'       => false,
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return $this->success(['message' => $message], null, 201);
    }


    public function markAsRead(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $message = Message::where('id', $id)
            ->where('receiver_id', $rep->id)
            ->where('receiver_type', 'medical_rep')
            ->first();

        if (!$message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['is_read' => true]);

        return $this->success([], 'Marked as read');
    }

    public function conversations(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $messages = Message::query()
            ->where(function ($q) use ($rep) {
                $q->where(function ($inner) use ($rep) {
                    $inner->where('sender_id', $rep->id)
                          ->where('sender_type', 'medical_rep');
                })->orWhere(function ($inner) use ($rep) {
                    $inner->where('receiver_id', $rep->id)
                          ->where('receiver_type', 'medical_rep');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $conversations = $messages->groupBy(function ($msg) use ($rep) {
            $isSender = $msg->sender_type === 'medical_rep' && $msg->sender_id == $rep->id;
            return 'doctor_' . ($isSender ? $msg->receiver_id : $msg->sender_id);
        })->map(function ($group, $key) use ($rep) {
            $latest    = $group->first();
            $partnerId = (int) str_replace('doctor_', '', $key);
            $partner   = \App\Models\Doctor::select(['id', 'full_name'])->find($partnerId);

            return [
                'partner_id'     => $partnerId,
                'partner_type'   => 'doctor',
                'partner_name'   => $partner?->full_name ?? 'Unknown',
                'latest_message' => $latest->body,
                'latest_time'    => $latest->created_at,
                'unread_count'   => $group->where('receiver_id', $rep->id)
                                          ->where('receiver_type', 'medical_rep')
                                          ->where('is_read', false)
                                          ->count(),
            ];
        })->values();

        return $this->success(['conversations' => $conversations]);
    }

    public function conversation(int $partnerId): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $messages = Message::query()
            ->where(function ($q) use ($rep, $partnerId) {
                $q->where(function ($inner) use ($rep, $partnerId) {
                    $inner->where('sender_id', $rep->id)
                          ->where('sender_type', 'medical_rep')
                          ->where('receiver_id', $partnerId)
                          ->where('receiver_type', 'doctor');
                })->orWhere(function ($inner) use ($rep, $partnerId) {
                    $inner->where('sender_id', $partnerId)
                          ->where('sender_type', 'doctor')
                          ->where('receiver_id', $rep->id)
                          ->where('receiver_type', 'medical_rep');
                });
            })
            ->orderBy('created_at')
            ->get();

        $partner = \App\Models\Doctor::find($partnerId, ['id', 'full_name']);

        return $this->success([
            'messages' => $messages,
            'partner'  => $partner,
        ]);
    }
}
