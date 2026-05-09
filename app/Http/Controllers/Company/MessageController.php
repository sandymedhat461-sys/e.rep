<?php

namespace App\Http\Controllers\Company;

use App\Events\MessageSent;
use App\Models\Doctor;
use App\Models\MedicalRep;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseCompanyController
{

    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $messages = Message::query()
            ->where('receiver_id', $company->id)
            ->whereIn('receiver_type', ['company', 'Company', 'App\\Models\\Company'])
            ->with('sender')
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['messages' => $messages]);
    }


    public function store(Request $request): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $validated = $this->validateRequest($request, [
            'receiver_id' => ['required', 'integer'],
            'receiver_type' => ['required', 'in:doctor,medical_rep'],
            'body' => ['required', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $receiverType = $validated['receiver_type'];

        if ($receiverType === 'doctor') {
            if (!Doctor::whereKey($validated['receiver_id'])->exists()) {
                return $this->error('Receiver not found', 404);
            }
        } elseif (!MedicalRep::whereKey($validated['receiver_id'])->where('company_id', $company->id)->exists()) {
            return $this->error('Receiver not found', 404);
        }

        $message = Message::create([
            'sender_type' => 'company',
            'sender_id' => $company->id,
            'receiver_id' => $validated['receiver_id'],
            'receiver_type' => $receiverType,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return $this->success(['message' => $message], null, 201);
    }


    public function markAsRead(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $message = Message::query()
            ->where('id', $id)
            ->where('receiver_id', $company->id)
            ->whereIn('receiver_type', ['company', 'Company', 'App\\Models\\Company'])
            ->first();

        if (!$message) {
            return $this->error('Message not found', 404);
        }

        $message->update(['is_read' => true]);

        return $this->success([], 'Marked as read');
    }

    public function conversations(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) return $company;

        $companyTypes = ['company', 'Company', 'App\\Models\\Company'];

        $messages = Message::query()
            ->where(function ($q) use ($company, $companyTypes) {
                $q->where(function ($inner) use ($company, $companyTypes) {
                    $inner->where('sender_id', $company->id)->whereIn('sender_type', $companyTypes);
                })->orWhere(function ($inner) use ($company, $companyTypes) {
                    $inner->where('receiver_id', $company->id)->whereIn('receiver_type', $companyTypes);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $conversations = $messages->groupBy(function ($msg) use ($company, $companyTypes) {
            $isSender = in_array($msg->sender_type, $companyTypes) && $msg->sender_id == $company->id;
            $partnerType = $isSender ? $msg->receiver_type : $msg->sender_type;
            $partnerId = $isSender ? $msg->receiver_id : $msg->sender_id;
            return $partnerType . '_' . $partnerId;
        })->map(function ($group) use ($company, $companyTypes) {
            $latest = $group->first();
            $isSender = in_array($latest->sender_type, $companyTypes) && $latest->sender_id == $company->id;
            $partnerType = $isSender ? $latest->receiver_type : $latest->sender_type;
            $partnerId = $isSender ? $latest->receiver_id : $latest->sender_id;

            if ($partnerType === 'doctor') {
                $partner = \App\Models\Doctor::find($partnerId, ['id', 'full_name']);
            } else {
                $partner = \App\Models\MedicalRep::find($partnerId, ['id', 'full_name']);
            }

            return [
                'partner_id' => $partnerId,
                'partner_type' => $partnerType,
                'partner_name' => $partner?->full_name ?? 'Unknown',
                'latest_message' => $latest->body,
                'latest_time' => $latest->created_at,
                'unread_count' => $group->where('receiver_id', $company->id)->where('is_read', false)->count(),
            ];
        })->values();

        return $this->success(['conversations' => $conversations]);
    }

    public function conversation(string $partnerType, int $partnerId): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) return $company;

        $companyTypes = ['company', 'Company', 'App\\Models\\Company'];

        $messages = Message::query()
            ->where(function ($q) use ($company, $companyTypes, $partnerType, $partnerId) {
                $q->where(function ($inner) use ($company, $companyTypes, $partnerType, $partnerId) {
                    $inner->where('sender_id', $company->id)
                        ->whereIn('sender_type', $companyTypes)
                        ->where('receiver_id', $partnerId)
                        ->where('receiver_type', $partnerType);
                })->orWhere(function ($inner) use ($company, $companyTypes, $partnerType, $partnerId) {
                    $inner->where('sender_id', $partnerId)
                        ->where('sender_type', $partnerType)
                        ->where('receiver_id', $company->id)
                        ->whereIn('receiver_type', $companyTypes);
                });
            })
            ->orderBy('created_at')
            ->get();

        $partner = $partnerType === 'doctor'
            ? \App\Models\Doctor::find($partnerId, ['id', 'full_name'])
            : \App\Models\MedicalRep::find($partnerId, ['id', 'full_name']);

        return $this->success([
            'messages' => $messages,
            'partner' => $partner,
        ]);
    }
}
