<?php

namespace App\Http\Controllers\Company;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends BaseCompanyController
{
    
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $types = ['company', 'Company', Company::class, 'App\\Models\\Company'];
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $company->id)
            ->whereIn('notifiable_type', $types)
            ->orderByDesc('created_at')
            ->get();

        $unreadCount = DB::table('notifications')
            ->where('notifiable_id', $company->id)
            ->whereIn('notifiable_type', $types)
            ->whereNull('read_at')
            ->count();

        return $this->success([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    
    public function markAsRead(string $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $types = ['company', 'Company', Company::class, 'App\\Models\\Company'];
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $company->id)
            ->whereIn('notifiable_type', $types)
            ->update(['read_at' => Carbon::now()]);

        if (!$updated) {
            return $this->error('Notification not found', 404);
        }

        return $this->success([], 'Marked as read');
    }

    
    public function markAllAsRead(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $types = ['company', 'Company', Company::class, 'App\\Models\\Company'];
        DB::table('notifications')
            ->where('notifiable_id', $company->id)
            ->whereIn('notifiable_type', $types)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return $this->success([], 'All marked as read');
    }
}
