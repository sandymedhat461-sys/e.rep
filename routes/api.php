<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\MedicalRepAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DrugCategoryController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Doctor\CommentController;
use App\Http\Controllers\Doctor\DoctorPointController;
use App\Http\Controllers\Doctor\DrugController;
use App\Http\Controllers\Doctor\DrugReviewController;
use App\Http\Controllers\Doctor\DrugSampleController;
use App\Http\Controllers\Doctor\EventController;
use App\Http\Controllers\Doctor\EventInvitationController;
use App\Http\Controllers\Doctor\EventRequestController;
use App\Http\Controllers\Doctor\FavoriteController;
use App\Http\Controllers\Doctor\MeetingController;
use App\Http\Controllers\Doctor\MessageController;
use App\Http\Controllers\Doctor\NotificationController;
use App\Http\Controllers\Doctor\PostController;
use App\Http\Controllers\Doctor\PostLikeController;
use App\Http\Controllers\Doctor\RewardController;
use App\Http\Controllers\Doctor\RewardRedemptionController;
use App\Http\Controllers\Company\ActiveIngredientController as CompanyActiveIngredientController;
use App\Http\Controllers\Company\DrugController as CompanyDrugController;
use App\Http\Controllers\Company\EventController as CompanyEventController;
use App\Http\Controllers\Company\EventInvitationController as CompanyEventInvitationController;
use App\Http\Controllers\Company\EventRequestController as CompanyEventRequestController;
use App\Http\Controllers\Company\MedicalRepController as CompanyMedicalRepController;
use App\Http\Controllers\Company\NotificationController as CompanyNotificationController;
use App\Http\Controllers\Company\PostController as CompanyPostController;
use App\Http\Controllers\Company\RewardController as CompanyRewardController;
use App\Http\Controllers\Company\RewardRedemptionController as CompanyRewardRedemptionController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\MessageController as CompanyMessageController;
use App\Http\Controllers\Doctor\ReportController;
use App\Http\Controllers\MedicalRep\AssignedDoctorController;
use App\Http\Controllers\MedicalRep\DrugController as MedicalRepDrugController;
use App\Http\Controllers\MedicalRep\DrugSampleController as MedicalRepDrugSampleController;
use App\Http\Controllers\MedicalRep\EventInvitationController as MedicalRepEventInvitationController;
use App\Http\Controllers\MedicalRep\MeetingController as MedicalRepMeetingController;
use App\Http\Controllers\MedicalRep\MessageController as MedicalRepMessageController;
use App\Http\Controllers\MedicalRep\NotificationController as MedicalRepNotificationController;
use App\Http\Controllers\MedicalRep\PostController as MedicalRepPostController;
use App\Http\Controllers\MedicalRep\TargetController as MedicalRepTargetController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/admin/register', [AdminAuthController::class, 'register']);
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth:admin-api');
    Route::get('/admin/me', [AdminAuthController::class, 'me'])->middleware('auth:admin-api');

    Route::post('/company/register', [CompanyAuthController::class, 'register']);
    Route::post('/company/login', [CompanyAuthController::class, 'login']);
    Route::post('/company/logout', [CompanyAuthController::class, 'logout'])->middleware('auth:company-api');
    Route::get('/company/me', [CompanyAuthController::class, 'me'])->middleware('auth:company-api');

    Route::post('/doctor/register', [DoctorAuthController::class, 'register']);
    Route::post('/doctor/check-syndicate', [DoctorAuthController::class, 'checkSyndicateId']);
    Route::post('/doctor/login', [DoctorAuthController::class, 'login']);
    Route::post('/doctor/logout', [DoctorAuthController::class, 'logout'])->middleware('auth:doctor-api');
    Route::get('/doctor/me', [DoctorAuthController::class, 'me'])->middleware('auth:doctor-api');

    Route::post('/rep/register', [MedicalRepAuthController::class, 'register']);
    Route::post('/rep/login', [MedicalRepAuthController::class, 'login']);
    Route::post('/rep/logout', [MedicalRepAuthController::class, 'logout'])->middleware('auth:rep-api');
    Route::get('/rep/me', [MedicalRepAuthController::class, 'me'])->middleware('auth:rep-api');
});

Route::prefix('admin')->middleware('auth:admin-api')->group(function () {
    Route::get('/users/pending', [UserManagementController::class, 'index']);
    Route::post('/users/{type}/{id}/approve', [UserManagementController::class, 'approve']);
    Route::post('/users/{type}/{id}/block', [UserManagementController::class, 'block']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/categories', [DrugCategoryController::class, 'index']);
    Route::post('/categories', [DrugCategoryController::class, 'store']);
    Route::put('/categories/{id}', [DrugCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [DrugCategoryController::class, 'destroy']);
});

Route::prefix('doctor')->middleware('auth:doctor-api')->group(function () {
    Route::get('/drugs', [DrugController::class, 'index']);
    Route::get('/drugs/{id}', [DrugController::class, 'show']);

    Route::get('/drugs/{drugId}/reviews', [DrugReviewController::class, 'index']);
    Route::post('/drugs/{drugId}/reviews', [DrugReviewController::class, 'store']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{drugId}', [FavoriteController::class, 'destroy']);

    Route::get('/samples', [DrugSampleController::class, 'index']);
    Route::post('/samples', [DrugSampleController::class, 'store']);

    Route::get('/meetings', [MeetingController::class, 'index']);
    Route::get('/meetings/{id}', [MeetingController::class, 'show']);
    Route::get('/meetings/{id}/video-room', [MeetingController::class, 'getVideoRoom']);

    Route::get('/report/generate', [ReportController::class, 'generate']);

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);

    Route::get('/event-requests', [EventRequestController::class, 'index']);
    Route::post('/event-requests', [EventRequestController::class, 'store']);

    Route::get('/invitations', [EventInvitationController::class, 'index']);
    Route::post('/invitations/{id}/accept', [EventInvitationController::class, 'accept']);
    Route::post('/invitations/{id}/decline', [EventInvitationController::class, 'decline']);

    Route::get('/points', [DoctorPointController::class, 'index']);
    Route::get('/points/total', [DoctorPointController::class, 'total']);

    Route::get('/rewards', [RewardController::class, 'index']);

    Route::get('/redemptions', [RewardRedemptionController::class, 'index']);
    Route::post('/rewards/{rewardId}/redeem', [RewardRedemptionController::class, 'store']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::post('/posts/{postId}/like', [PostLikeController::class, 'store']);
    Route::delete('/posts/{postId}/unlike', [PostLikeController::class, 'destroy']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

Route::prefix('company')->middleware('auth:company-api')->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index']);

    Route::get('/messages', [CompanyMessageController::class, 'index']);
    Route::post('/messages', [CompanyMessageController::class, 'store']);
    Route::post('/messages/{id}/read', [CompanyMessageController::class, 'markAsRead']);

    Route::get('/ingredients', [CompanyActiveIngredientController::class, 'index']);
    Route::post('/ingredients', [CompanyActiveIngredientController::class, 'store']);
    Route::put('/ingredients/{id}', [CompanyActiveIngredientController::class, 'update']);
    Route::delete('/ingredients/{id}', [CompanyActiveIngredientController::class, 'destroy']);

    Route::get('/drugs', [CompanyDrugController::class, 'index']);
    Route::post('/drugs', [CompanyDrugController::class, 'store']);
    Route::get('/drugs/{id}', [CompanyDrugController::class, 'show']);
    Route::put('/drugs/{id}', [CompanyDrugController::class, 'update']);
    Route::delete('/drugs/{id}', [CompanyDrugController::class, 'destroy']);

    Route::get('/events', [CompanyEventController::class, 'index']);
    Route::post('/events', [CompanyEventController::class, 'store']);
    Route::get('/events/{id}', [CompanyEventController::class, 'show']);
    Route::put('/events/{id}', [CompanyEventController::class, 'update']);
    Route::delete('/events/{id}', [CompanyEventController::class, 'destroy']);

    Route::get('/events/{eventId}/requests', [CompanyEventRequestController::class, 'index']);
    Route::post('/events/{eventId}/requests/{id}/approve', [CompanyEventRequestController::class, 'approve']);
    Route::post('/events/{eventId}/requests/{id}/reject', [CompanyEventRequestController::class, 'reject']);

    Route::post('/events/{eventId}/invite', [CompanyEventInvitationController::class, 'invite']);
    Route::get('/events/{eventId}/invitations', [CompanyEventInvitationController::class, 'index']);

    Route::get('/rewards', [CompanyRewardController::class, 'index']);
    Route::post('/rewards', [CompanyRewardController::class, 'store']);
    Route::get('/rewards/{id}', [CompanyRewardController::class, 'show']);
    Route::put('/rewards/{id}', [CompanyRewardController::class, 'update']);
    Route::delete('/rewards/{id}', [CompanyRewardController::class, 'destroy']);

    Route::get('/redemptions', [CompanyRewardRedemptionController::class, 'index']);
    Route::post('/redemptions/{id}/fulfill', [CompanyRewardRedemptionController::class, 'fulfill']);
    Route::post('/redemptions/{id}/cancel', [CompanyRewardRedemptionController::class, 'cancel']);

    Route::get('/reps', [CompanyMedicalRepController::class, 'index']);
    Route::get('/reps/{id}', [CompanyMedicalRepController::class, 'show']);
    Route::post('/reps/{id}/targets', [CompanyMedicalRepController::class, 'upsertTarget']);
    Route::get('/reps/{id}/targets', [CompanyMedicalRepController::class, 'targets']);

    Route::get('/posts', [CompanyPostController::class, 'index']);
    Route::post('/posts', [CompanyPostController::class, 'store']);
    Route::get('/posts/{id}', [CompanyPostController::class, 'show']);
    Route::put('/posts/{id}', [CompanyPostController::class, 'update']);
    Route::delete('/posts/{id}', [CompanyPostController::class, 'destroy']);
    Route::post('/posts/{postId}/comments', [CompanyPostController::class, 'storeComment']);
    Route::delete('/comments/{id}', [CompanyPostController::class, 'destroyComment']);
    Route::post('/posts/{postId}/like', [CompanyPostController::class, 'like']);
    Route::delete('/posts/{postId}/unlike', [CompanyPostController::class, 'unlike']);

    Route::get('/notifications', [CompanyNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [CompanyNotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [CompanyNotificationController::class, 'markAllAsRead']);
});

Route::prefix('rep')->middleware('auth:rep-api')->group(function () {
    Route::get('/doctors', [AssignedDoctorController::class, 'index']);
    Route::get('/doctors/{id}', [AssignedDoctorController::class, 'show']);

    Route::get('/meetings', [MedicalRepMeetingController::class, 'index']);
    Route::post('/meetings', [MedicalRepMeetingController::class, 'store']);
    Route::get('/meetings/{id}', [MedicalRepMeetingController::class, 'show']);
    Route::post('/meetings/{id}/complete', [MedicalRepMeetingController::class, 'complete']);
    Route::post('/meetings/{id}/cancel', [MedicalRepMeetingController::class, 'cancel']);
    Route::get('/meetings/{id}/video-room', [MedicalRepMeetingController::class, 'getVideoRoom']);

    Route::get('/samples', [MedicalRepDrugSampleController::class, 'index']);
    Route::get('/samples/{id}', [MedicalRepDrugSampleController::class, 'show']);
    Route::post('/samples/{id}/approve', [MedicalRepDrugSampleController::class, 'approve']);
    Route::post('/samples/{id}/reject', [MedicalRepDrugSampleController::class, 'reject']);
    Route::post('/samples/{id}/deliver', [MedicalRepDrugSampleController::class, 'deliver']);

    Route::get('/drugs', [MedicalRepDrugController::class, 'index']);
    Route::get('/drugs/{id}', [MedicalRepDrugController::class, 'show']);

    Route::get('/targets', [MedicalRepTargetController::class, 'index']);

    Route::get('/invitations', [MedicalRepEventInvitationController::class, 'index']);
    Route::post('/events/{eventId}/invite', [MedicalRepEventInvitationController::class, 'invite']);

    Route::get('/posts', [MedicalRepPostController::class, 'index']);
    Route::post('/posts', [MedicalRepPostController::class, 'store']);
    Route::get('/posts/{id}', [MedicalRepPostController::class, 'show']);
    Route::put('/posts/{id}', [MedicalRepPostController::class, 'update']);
    Route::delete('/posts/{id}', [MedicalRepPostController::class, 'destroy']);
    Route::post('/posts/{postId}/comments', [MedicalRepPostController::class, 'storeComment']);
    Route::delete('/comments/{id}', [MedicalRepPostController::class, 'destroyComment']);
    Route::post('/posts/{postId}/like', [MedicalRepPostController::class, 'like']);
    Route::delete('/posts/{postId}/unlike', [MedicalRepPostController::class, 'unlike']);

    Route::get('/messages', [MedicalRepMessageController::class, 'index']);
    Route::post('/messages', [MedicalRepMessageController::class, 'store']);
    Route::post('/messages/{id}/read', [MedicalRepMessageController::class, 'markAsRead']);

    Route::get('/notifications', [MedicalRepNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [MedicalRepNotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [MedicalRepNotificationController::class, 'markAllAsRead']);
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);
