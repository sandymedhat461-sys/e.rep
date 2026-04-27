<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $doctorId = (int) $request->user()->id;
        $doctorTypes = ['doctor', 'Doctor', 'App\\Models\\Doctor'];
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $doctorId)
            ->whereIn('notifiable_type', $doctorTypes)
            ->orderByDesc('created_at')
            ->get();

        $unreadCount = DB::table('notifications')
            ->where('notifiable_id', $doctorId)
            ->whereIn('notifiable_type', $doctorTypes)
            ->whereNull('read_at')
            ->count();

        return $this->success([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $doctorTypes = ['doctor', 'Doctor', 'App\\Models\\Doctor'];
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $request->user()->id)
            ->whereIn('notifiable_type', $doctorTypes)
            ->update(['read_at' => Carbon::now()]);

        if (!$updated) {
            return $this->error('Notification not found', 404);
        }

        return $this->success([], 'Marked as read');
    }

    
    public function markAllAsRead(Request $request): JsonResponse
    {
        $doctorTypes = ['doctor', 'Doctor', 'App\\Models\\Doctor'];
        DB::table('notifications')
            ->where('notifiable_id', $request->user()->id)
            ->whereIn('notifiable_type', $doctorTypes)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return $this->success([], 'All marked as read');
    }
}
