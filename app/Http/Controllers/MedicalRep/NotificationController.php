<?php

namespace App\Http\Controllers\MedicalRep;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends BaseMedicalRepController
{
    
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $notifications = DB::table('notifications')
            ->where('notifiable_id', $rep->id)
            ->whereIn('notifiable_type', ['medical_rep', 'MedicalRep', 'App\\Models\\MedicalRep'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success(['notifications' => $notifications]);
    }

    
    public function markAsRead(string $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $rep->id)
            ->whereIn('notifiable_type', ['medical_rep', 'MedicalRep', 'App\\Models\\MedicalRep'])
            ->update(['read_at' => Carbon::now()]);

        if (!$updated) {
            return $this->error('Notification not found', 404);
        }

        return $this->success([], 'Marked as read');
    }

   
    public function markAllAsRead(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        DB::table('notifications')
            ->where('notifiable_id', $rep->id)
            ->whereIn('notifiable_type', ['medical_rep', 'MedicalRep', 'App\\Models\\MedicalRep'])
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return $this->success([], 'All marked as read');
    }
}
