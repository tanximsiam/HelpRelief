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

use App\Http\Controllers\AidSupportController;
use App\Http\Controllers\VolunteerRegistrationController;
use App\Models\AidSupport;
use App\Http\Controllers\VolunteerTaskController;
use App\Http\Controllers\VolunteerReportController;

use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\VolunteerTaskLogController;

use App\Http\Controllers\DonationReportController;
use App\Http\Controllers\DisasterController; // added


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// TEST ROUTES
Route::get('/users', [UserController::class, 'index']);
Route::get('/ngos', [NgoController::class, 'index']);
Route::get('/ngo-applications', [NgoApplicationController::class, 'index']);
Route::get('/cause-focuses', [CauseFocusController::class, 'index']);
Route::get('/ngo-invites/{ngo}', [NgoInviteLinkController::class, 'index']);
Route::get('/ngo-staff', [NgoStaffController::class, 'index']);
Route::get('/aid-supports', [AidSupportController::class, 'index']);
Route::get('/myRequests', [AidSupportController::class, 'myRequests']);
// Active disasters route (public for selection)
Route::get('/disasters/active', [DisasterController::class, 'active']);
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

    Route::get('/user', [AuthController::class, 'user']);
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


    // Aid Support and Volunteer Registration
    Route::post('/aid-supports', [AidSupportController::class, 'store']);
    Route::post('/volunteer-registrations', [VolunteerRegistrationController::class, 'store']);
    Route::get('/my-help-offers', [AidSupportController::class, 'myOffers']);

    // End of Authenticated Routes

    // Aid Requests
    Route::get('/aid-requests', [AidRequestController::class, 'index']);
    Route::post('/submit-aid-requests', [AidRequestController::class, 'store']);
    Route::post('/aid-requests/{aid_request}/verify', [AidRequestController::class, 'verifyByRequester']);

    // Volunteer Task Logs
    Route::post('/task-log/checkin', [VolunteerTaskLogController::class, 'checkIn']);
    Route::post('/task-log/checkout', [VolunteerTaskLogController::class, 'checkOut']);
    Route::patch('/task-log/status', [VolunteerTaskLogController::class, 'updateStatus']);


    Route::get('/my-tasks', [VolunteerTaskController::class, 'index']);
    Route::patch('/my-tasks/{id}/status', [VolunteerTaskController::class, 'updateStatus']);

    // Assign a task to a volunteer (NGO action)
    Route::post('/tasks/{id}/assign', [VolunteerTaskController::class, 'assignTask']);

    // Reject a task (NGO action with remarks)
    Route::patch('/tasks/{id}/reject', [VolunteerTaskController::class, 'rejectTask']);

    // Create a standalone task (NGO action)
    Route::post('/tasks/create', [VolunteerTaskController::class, 'createStandaloneTask']);

    // Donation Reports
    Route::get('/disasters/{disasterId}/user-report', [DonationReportController::class, 'userReportForDisaster']);

});


Route::middleware(['auth:sanctum', 'role:ngo'])->prefix('reports/volunteers')->group(function () {
    Route::get('/aggregate', [VolunteerReportController::class, 'aggregate']);
    Route::get('/individual', [VolunteerReportController::class, 'individual']);
});

