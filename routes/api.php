<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\MedicalRepAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DrugCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
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
use App\Http\Controllers\Doctor\PostController as DoctorPostController;
use App\Http\Controllers\Doctor\PostLikeController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;
use App\Http\Controllers\Company\ActiveIngredientController as CompanyActiveIngredientController;
use App\Http\Controllers\Company\DrugController as CompanyDrugController;
use App\Http\Controllers\Company\DrugReviewController as CompanyDrugReviewController;
use App\Http\Controllers\Company\EventController as CompanyEventController;
use App\Http\Controllers\Company\EventInvitationController as CompanyEventInvitationController;
use App\Http\Controllers\Company\EventRequestController as CompanyEventRequestController;
use App\Http\Controllers\Company\MedicalRepController as CompanyMedicalRepController;
use App\Http\Controllers\Company\NotificationController as CompanyNotificationController;
use App\Http\Controllers\Company\PostController as CompanyPostController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\MessageController as CompanyMessageController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Doctor\ReportController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Company\ReportController as CompanyReportController;
use App\Http\Controllers\MedicalRep\ReportController as MedicalRepReportController;
use App\Http\Controllers\MedicalRep\AssignedDoctorController;
use App\Http\Controllers\MedicalRep\DrugController as MedicalRepDrugController;
use App\Http\Controllers\MedicalRep\DrugSampleController as MedicalRepDrugSampleController;
use App\Http\Controllers\MedicalRep\EventInvitationController as MedicalRepEventInvitationController;
use App\Http\Controllers\MedicalRep\MeetingController as MedicalRepMeetingController;
use App\Http\Controllers\MedicalRep\MessageController as MedicalRepMessageController;
use App\Http\Controllers\MedicalRep\NotificationController as MedicalRepNotificationController;
use App\Http\Controllers\MedicalRep\PostController as MedicalRepPostController;
use App\Http\Controllers\MedicalRep\ProfileController as MedicalRepProfileController;
use App\Http\Controllers\MedicalRep\TargetController as MedicalRepTargetController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/image/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        return response()->json(['message' => 'Image not found'], 404);
    }

    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*');

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
    Route::delete('/users/{type}/{id}', [UserManagementController::class, 'deleteUser']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/reports/stats', [DashboardController::class, 'reportStats']);
    Route::get('/report/generate', [AdminReportController::class, 'generate']);

    Route::get('/categories', [DrugCategoryController::class, 'index']);
    Route::post('/categories', [DrugCategoryController::class, 'store']);
    Route::put('/categories/{id}', [DrugCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [DrugCategoryController::class, 'destroy']);

    Route::get('/posts/reported', [PostController::class, 'reportedPosts']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    Route::get('/doctors', [UserManagementController::class, 'listDoctors']);
    Route::get('/reps', [UserManagementController::class, 'listReps']);
    Route::get('/companies', [UserManagementController::class, 'listCompanies']);
    Route::post('/companies', [UserManagementController::class, 'createCompany']);
    Route::post('/users/doctors', [UserManagementController::class, 'createDoctor']);
    Route::post('/reps', [UserManagementController::class, 'createRep']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'changePassword']);
});

Route::prefix('doctor')->middleware('auth:doctor-api')->group(function () {
    Route::get('/profile', [DoctorProfileController::class, 'show']);
    Route::put('/profile', [DoctorProfileController::class, 'update']);
    Route::put('/password', [DoctorProfileController::class, 'changePassword']);

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
    Route::post('/meetings', [MeetingController::class, 'store']);

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

    Route::get('/posts', [DoctorPostController::class, 'index']);
    Route::post('/posts', [DoctorPostController::class, 'store']);
    Route::get('/posts/{id}', [DoctorPostController::class, 'show']);
    Route::put('/posts/{id}', [DoctorPostController::class, 'update']);
    Route::delete('/posts/{id}', [DoctorPostController::class, 'destroy']);
    Route::post('/posts/{id}/report', [DoctorPostController::class, 'report']);
    Route::post('/posts/{id}/share', [DoctorPostController::class, 'share']);
    Route::post('/posts/{id}/comment', [DoctorPostController::class, 'comment']);

    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::post('/posts/{postId}/like', [PostLikeController::class, 'store']);
    Route::delete('/posts/{postId}/unlike', [PostLikeController::class, 'destroy']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/conversation/{partnerId}', [MessageController::class, 'conversation']);
    Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

Route::prefix('company')->middleware('auth:company-api')->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index']);

    Route::get('/profile', [CompanyProfileController::class, 'show']);
    Route::put('/profile', [CompanyProfileController::class, 'update']);
    Route::put('/password', [CompanyProfileController::class, 'changePassword']);

    Route::get('/messages', [CompanyMessageController::class, 'index']);
    Route::post('/messages', [CompanyMessageController::class, 'store']);
    Route::get('/messages/conversations', [CompanyMessageController::class, 'conversations']);
    Route::get('/messages/conversation/{partnerType}/{partnerId}', [CompanyMessageController::class, 'conversation']);
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

    Route::get('/drugs/{drugId}/reviews', [CompanyDrugReviewController::class, 'index']);

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

    Route::get('/reps', [CompanyMedicalRepController::class, 'index']);
    Route::get('/reps/{id}', [CompanyMedicalRepController::class, 'show']);
    Route::post('/reps/{id}/targets', [CompanyMedicalRepController::class, 'upsertTarget']);
    Route::get('/reps/{id}/targets', [CompanyMedicalRepController::class, 'targets']);
    Route::get('/doctors', [\App\Http\Controllers\Company\DoctorController::class, 'index']);
    Route::get('/report/generate', [CompanyReportController::class, 'generate']);

    Route::get('/posts', [CompanyPostController::class, 'index']);
    Route::post('/posts', [CompanyPostController::class, 'store']);
    Route::get('/posts/{id}', [CompanyPostController::class, 'show']);
    Route::put('/posts/{id}', [CompanyPostController::class, 'update']);
    Route::delete('/posts/{id}', [CompanyPostController::class, 'destroy']);
    Route::post('/posts/{postId}/comments', [CompanyPostController::class, 'storeComment']);
    Route::delete('/comments/{id}', [CompanyPostController::class, 'destroyComment']);
    Route::post('/posts/{id}/comment', [CompanyPostController::class, 'comment']);
    Route::post('/posts/{id}/like', [CompanyPostController::class, 'like']);
    Route::delete('/posts/{id}/like', [CompanyPostController::class, 'unlike']);
    Route::post('/posts/{id}/share', [CompanyPostController::class, 'share']);

    Route::get('/notifications', [CompanyNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [CompanyNotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [CompanyNotificationController::class, 'markAllAsRead']);
});

Route::prefix('rep')->middleware('auth:rep-api')->group(function () {
    Route::get('/profile', [MedicalRepProfileController::class, 'show']);
    Route::put('/profile', [MedicalRepProfileController::class, 'update']);
    Route::put('/password', [MedicalRepProfileController::class, 'changePassword']);
    Route::get('/report/generate', [MedicalRepReportController::class, 'generate']);

    Route::get('/doctors', [AssignedDoctorController::class, 'index']);
    Route::get('/doctors/{id}', [AssignedDoctorController::class, 'show']);

    Route::get('/meetings', [MedicalRepMeetingController::class, 'index']);
    Route::post('/meetings', [MedicalRepMeetingController::class, 'store']);
    Route::get('/meetings/{id}', [MedicalRepMeetingController::class, 'show']);
    Route::post('/meetings/{id}/complete', [MedicalRepMeetingController::class, 'complete']);
    Route::post('/meetings/{id}/cancel', [MedicalRepMeetingController::class, 'cancel']);
    Route::post('/meetings/{id}/approve', [MedicalRepMeetingController::class, 'approve']);
    Route::post('/meetings/{id}/reject', [MedicalRepMeetingController::class, 'reject']);

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
    Route::post('/posts/{id}/report', [MedicalRepPostController::class, 'report']);
    Route::post('/posts/{id}/share', [MedicalRepPostController::class, 'share']);
    Route::post('/posts/{id}/comment', [MedicalRepPostController::class, 'comment']);
    Route::post('/posts/{postId}/comments', [MedicalRepPostController::class, 'storeComment']);
    Route::delete('/comments/{id}', [MedicalRepPostController::class, 'destroyComment']);
    Route::post('/posts/{id}/like', [MedicalRepPostController::class, 'like']);
    Route::delete('/posts/{id}/like', [MedicalRepPostController::class, 'unlike']);

    Route::get('/messages', [MedicalRepMessageController::class, 'index']);
    Route::post('/messages', [MedicalRepMessageController::class, 'store']);
    Route::get('/messages/conversations', [MedicalRepMessageController::class, 'conversations']);
    Route::get('/messages/conversation/{partnerId}', [MedicalRepMessageController::class, 'conversation']);
    Route::post('/messages/{id}/read', [MedicalRepMessageController::class, 'markAsRead']);

    Route::get('/notifications', [MedicalRepNotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [MedicalRepNotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [MedicalRepNotificationController::class, 'markAllAsRead']);
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);
