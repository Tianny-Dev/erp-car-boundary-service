<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\PendingDriverController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('super-admin/dashboard/Index'))->name('dashboard');
    // Updated route to use the controller
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Add new routes for Accept/Deny actions
    // We'll use a new controller to keep things organized
    Route::post('/drivers/{userDriver}/accept', [PendingDriverController::class, 'accept'])->name('drivers.accept');
    Route::post('/drivers/{userDriver}/deny', [PendingDriverController::class, 'deny'])->name('drivers.deny');
});
