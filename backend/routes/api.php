<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Middleware\HandleCors;

use App\Http\Controllers\UserController;
use App\Http\Controllers\NgoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NgoApplicationController;
use App\Http\Controllers\CauseFocusController;
use App\Http\Controllers\NgoStaffController;
use App\Http\Controllers\NgoInviteLinkController;
use App\Http\Controllers\DisasterController;

use App\Http\Controllers\AidSupportController;
use App\Http\Controllers\VolunteerRegistrationController;
use App\Models\AidSupport;
use App\Http\Controllers\VolunteerTaskController;
use App\Http\Controllers\VolunteerReportController;

use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\VolunteerTaskLogController;

use App\Http\Controllers\DonationReportController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\TaskController;

use App\Http\Controllers\MapController;
use App\Http\Controllers\DisasterAlertController;

use App\Http\Controllers\AidNeedController;
use App\Http\Controllers\ReportController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// TEST ROUTES
Route::get('/users', [UserController::class, 'index']);
Route::get('/ngos', [NgoController::class, 'index']);
Route::get('/ngo-staffs', [NgoStaffController::class, 'index']);
Route::get('/ngo-applications', [NgoApplicationController::class, 'index']);
Route::get('/cause-focuses', [CauseFocusController::class, 'index']);
Route::get('/ngo-invites/{ngo}', [NgoInviteLinkController::class, 'index']);
Route::get('/aid-supports', [AidSupportController::class, 'index']);
Route::get('/myRequests', [AidSupportController::class, 'myRequests']);
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


Route::get('/auth/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware([HandleCors::class, 'auth:sanctum'])->group(function () {
    // Authenticated user profile

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // NGO applications
    Route::post('/ngo-applications/{id}/approve', [NgoApplicationController::class, 'approve']);
    Route::post('/ngo-applications/{id}/reject', [NgoApplicationController::class, 'reject']);

    // Cause Focuses (optional admin use)
    Route::post('/cause-focuses', [CauseFocusController::class, 'store']);

    // NGO Staff
    Route::get('/ngo-staff', [NgoStaffController::class, 'me']);
    Route::delete('/ngo-staffs/{id}', [NgoStaffController::class, 'destroy']);

    // Invite Accept (WIP)
    Route::post('/ngo-invite/accept', [NgoInviteLinkController::class, 'accept']);
    Route::post('/ngo-invites', [NgoInviteLinkController::class, 'store']);


    // Aid Support and Volunteer Registration
    Route::post('/aid-supports', [AidSupportController::class, 'store']);
    Route::post('/volunteer-registrations', [VolunteerRegistrationController::class, 'store']);
    Route::get('/my-help-offers', [AidSupportController::class, 'myOffers']);
    Route::get('/volunteers', [VolunteerRegistrationController::class, 'index']);

    // Aid Requests
    Route::get('/aid-requests', [AidRequestController::class, 'index']);
    Route::post('/submit-aid-requests', [AidRequestController::class, 'store']);
    Route::get('/my-requests', [AidRequestController::class, 'myRequests']);
    Route::post('/aid-requests/{aid_request}/verify', [AidRequestController::class, 'verifyByRequester']);

    // Volunteer Task Logs
    Route::get('/task-logs', [VolunteerTaskLogController::class, 'index']);
    Route::post('/task-log/checkin', [VolunteerTaskLogController::class, 'checkIn']);
    Route::post('/task-log/checkout', [VolunteerTaskLogController::class, 'checkOut']);


    Route::get('/my-tasks', [VolunteerTaskController::class, 'index']);
    Route::patch('/my-tasks/{id}/status', [VolunteerTaskController::class, 'updateStatus']);

    // Assign an aid request (handles both financial + non-financial cases)
    Route::post('/aid-requests/{id}/assign', [VolunteerTaskController::class, 'assignAidRequest']);

    // Reject an aid request with remarks
    Route::post('/aid-requests/{id}/reject', [VolunteerTaskController::class, 'rejectAidRequest']);

    // Create a standalone task (independent of aid requests)
    Route::post('/tasks/standalone', [VolunteerTaskController::class, 'createStandaloneTask']);

    // Donation Reports
    Route::get('/disasters/{disasterId}/user-report', [DonationReportController::class, 'userReportForDisaster']);
    Route::get('/donation-reports/disasters', [DonationReportController::class, 'allDonationReports']);



    Route::get('/active-disasters', [DisasterController::class, 'index']);

    Route::get('/disasters/active', [DisasterController::class, 'active']);
    Route::post('/disasters/store', [DisasterController::class, 'store']);

    // Volunteer Reports
    Route::get('/reports/volunteers/aggregate', [VolunteerReportController::class, 'aggregate']);
    Route::get('/reports/volunteers/individual', [VolunteerReportController::class, 'individual']);


    // NGO Profile Update
    Route::get('/ngo/{ngoId}', [NgoController::class, 'show']);
    Route::patch('/ngo/{ngoId}', [NgoController::class, 'updateNgo']);

    // User Profile Update
    Route::get('/user', [UserController::class, 'show']);
    Route::patch('/user', [UserController::class, 'update']);

    // Campaign routes
    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/my', [CampaignController::class, 'myCampaigns']);
    Route::get('/campaigns/volunteer', [CampaignController::class, 'volunteerCampaigns']);
    Route::get('/campaigns/stats', [CampaignController::class, 'campaignStats']);
    Route::get('/campaigns/{campaignId}/volunteers', [CampaignController::class, 'campaignVolunteers']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);
    Route::post('/campaigns', [CampaignController::class, 'store']);
    // Tasks for a campaign
    Route::get('/campaigns/{campaignId}/tasks', [TaskController::class, 'campaignTasks']);

    // Map routes for NGO dashboard
    Route::get('/map/campaign-intensity', [MapController::class, 'getCampaignIntensityByState']);
    Route::get('/map/state/{stateName}', [MapController::class, 'getStateDetails']);
    Route::get('/map/aid-request-density', [MapController::class, 'getAidRequestDensityByState']);
    Route::get('/map/aid-requests/state/{stateName}', [MapController::class, 'getAidRequestsForState']);

    // Disaster Alert controller
    Route::post('/alerts/{id}/reject', [DisasterAlertController::class, 'reject']);
    Route::post('/alerts/{id}/confirm', [DisasterAlertController::class, 'confirm']);

    // Aid Needs
    Route::get('/aid-needed', [AidNeedController::class, 'index']);

    // Reports
    Route::get('/report/ngos', [ReportController::class, 'ngoReports']);
    Route::get('/report/my-ngo', [ReportController::class, 'myNgoReport']);

    Route::get('/map/aid-need', [MapController::class, 'getAidNeedByState']);
        Route::get('/map/aid-need-test', [MapController::class, 'testAidNeed']); // Test route - remove in production

});


Route::get('/alerts', [DisasterAlertController::class, 'new']);


Route::get('/alerts-all', [DisasterAlertController::class, 'index']);
// Test route outside auth middleware for development (REMOVE IN PRODUCTION)
Route::get('/test/aid-need', [MapController::class, 'testAidNeed']);

// TEMP: expose campaign tasks without auth for local debugging (auto-disabled outside local)
if (app()->environment('local')) {
    Route::get('/debug/campaigns/{campaignId}/tasks', [TaskController::class, 'campaignTasks']);
}

