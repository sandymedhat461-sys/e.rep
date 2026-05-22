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
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $messages = Message::query()
            ->where('receiver_id', $rep->id)
            ->whereIn('receiver_type', ['medical_rep', 'MedicalRep', 'rep', 'App\\Models\\MedicalRep'])
            ->with('sender')
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
            return $this->error('Messaging company is not supported for reps', 422);
        }

        if (!Doctor::whereKey($validated['receiver_id'])->exists()) {
            return $this->error('Doctor not found', 404);
        }

        $message = Message::create([
            'sender_type' => 'medical_rep',
            'sender_id' => $rep->id,
            'receiver_id' => $validated['receiver_id'],
            'receiver_type' => 'doctor',
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return $this->success(['message' => $message], null, 201);
    }


    public function markAsRead(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $message = Message::query()
            ->where('id', $id)
            ->where('receiver_id', $rep->id)
            ->whereIn('receiver_type', ['medical_rep', 'MedicalRep', 'rep', 'App\\Models\\MedicalRep'])
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

        $repTypes = ['medical_rep', 'MedicalRep', 'rep', 'App\\Models\\MedicalRep'];

        // Get all messages involving this rep
        $messages = Message::query()
            ->where(function ($q) use ($rep, $repTypes) {
                $q->where(function ($inner) use ($rep, $repTypes) {
                    $inner->where('sender_id', $rep->id)->whereIn('sender_type', $repTypes);
                })->orWhere(function ($inner) use ($rep, $repTypes) {
                    $inner->where('receiver_id', $rep->id)->whereIn('receiver_type', $repTypes);
                });
            })
            ->with('sender')
            ->orderByDesc('created_at')
            ->get();

        // Group by partner (the other person's id, always a doctor here)
        $conversations = $messages->groupBy(function ($msg) use ($rep, $repTypes) {
            $isSender = in_array($msg->sender_type, $repTypes) && $msg->sender_id == $rep->id;
            return 'doctor_' . ($isSender ? $msg->receiver_id : $msg->sender_id);
        })->map(function ($group, $key) use ($rep) {
            $latest = $group->first();
            $partnerId = (int) str_replace('doctor_', '', $key);
            $partner = \App\Models\Doctor::select(['id', 'full_name'])->find($partnerId);
            return [
                'partner_id'     => $partnerId,
                'partner_type'   => 'doctor',
                'partner_name'   => $partner?->full_name ?? 'Unknown',
                'latest_message' => $latest->body,
                'latest_time'    => $latest->created_at,
                'unread_count'   => $group->where('receiver_id', $rep->id)->where('is_read', false)->count(),
            ];
        })->values();

        return $this->success(['conversations' => $conversations]);
    }

    public function conversation(int $partnerId): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) return $rep;

        $repTypes = ['medical_rep', 'MedicalRep', 'rep', 'App\\Models\\MedicalRep'];

        $messages = Message::query()
            ->where(function ($q) use ($rep, $repTypes, $partnerId) {
                // rep → doctor
                $q->where(function ($inner) use ($rep, $repTypes, $partnerId) {
                    $inner->where('sender_id', $rep->id)
                        ->whereIn('sender_type', $repTypes)
                        ->where('receiver_id', $partnerId)
                        ->where('receiver_type', 'doctor');
                    // doctor → rep
                })->orWhere(function ($inner) use ($rep, $repTypes, $partnerId) {
                    $inner->where('sender_id', $partnerId)
                        ->where('sender_type', 'doctor')
                        ->where('receiver_id', $rep->id)
                        ->whereIn('receiver_type', $repTypes);
                });
            })
            ->orderBy('created_at')
            ->get();

        $partner = \App\Models\Doctor::find($partnerId, ['id', 'full_name']);

        return $this->success([
            'messages' => $messages,
            'partner' => $partner,
        ]);
    }
}
