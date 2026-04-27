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
}
