<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/auth/redirect', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.redirect');
Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback'])
    ->name('auth.callback');

Route::middleware('auth')->group(function () {
    Route::get('/ngo/dashboard', fn() => view('ngo.dashboard'));
    Route::get('/admin/panel', fn() => view('admin.panel'));
    Route::get('/general/dashboard', fn() => view('general.dashboard'));
});
