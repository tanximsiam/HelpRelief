<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\NgoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NgoApplicationController;
use App\Http\Controllers\CauseFocusController;
use App\Http\Controllers\NgoStaffController;
use App\Http\Controllers\NgoInviteLinkController;
use App\Http\Controllers\VolunteerTaskController;
use App\Http\Controllers\VolunteerReportController;

use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\VolunteerTaskLogController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// TEST ROUTES
Route::get('/users', [UserController::class, 'index']);
Route::get('/ngos', [NgoController::class, 'index']);
Route::get('/ngo-applications', [NgoApplicationController::class, 'index']);
Route::get('/cause-focuses', [CauseFocusController::class, 'index']);
Route::get('/ngo-invites/{ngo}', [NgoInviteLinkController::class, 'index']);
Route::get('/ngo-staff', [NgoStaffController::class, 'index']);

// Pre-Login Routes
Route::post('/ngo-apply', [NgoApplicationController::class, 'submit']);

// Route::get('/users', function () {
//     return User.index();
// });

// Route::get('/ngos', function () {
//     return Ngo::all();
// });


// Authenticated Routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Authenticated user profile

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // NGO applications
    Route::post('/ngo-applications/{id}/approve', [NgoApplicationController::class, 'approve']);
    Route::post('/ngo-applications/{id}/reject', [NgoApplicationController::class, 'reject']);

    // Cause Focuses (optional admin use)
    Route::post('/cause-focuses', [CauseFocusController::class, 'store']);

    // NGO Staff
    Route::get('/ngo-staffs', [NgoStaffController::class, 'index']);
    Route::delete('/ngo-staffs/{id}', [NgoStaffController::class, 'destroy']);

    // Invite Accept (WIP)
    Route::post('/ngo-invite/accept', [NgoInviteLinkController::class, 'accept']);
    Route::post('/ngo-invites', [NgoInviteLinkController::class, 'store']);

    // Aid Requests
    Route::get('/aid-requests', [AidRequestController::class, 'index']);
    Route::post('/aid-requests', [AidRequestController::class, 'store']);

    // Volunteer Task Logs
    Route::post('/task-log/checkin', [VolunteerTaskLogController::class, 'checkIn']);
    Route::post('/task-log/checkout', [VolunteerTaskLogController::class, 'checkOut']);
    Route::patch('/task-log/status', [VolunteerTaskLogController::class, 'updateStatus']);


});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-tasks', [VolunteerTaskController::class, 'index']);
    Route::patch('/my-tasks/{id}/status', [VolunteerTaskController::class, 'updateStatus']);
});

Route::middleware(['auth:sanctum', 'role:ngo'])->prefix('reports/volunteers')->group(function () {
    Route::get('/aggregate', [VolunteerReportController::class, 'aggregate']);
    Route::get('/individual', [VolunteerReportController::class, 'individual']);
});
