<?php

use App\Http\Controllers\SuperAdmin\RevenuesController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\PendingDriverController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Revenues
    Route::get('/revenues', [RevenuesController::class, 'index'])->name('revenues');

    // Export All Route must be before the generic ID route
    Route::get('/revenues/all/export/{format}', [RevenuesController::class, 'exportAll'])->name('revenues.exportAll'); // ADDED & MOVED
    // Show All Route must be before the generic ID route
    Route::get('/revenues/all', [RevenuesController::class, 'showAll'])->name('revenues.showAll'); // ADDED & MOVED

    Route::get('/revenues/{id}', [RevenuesController::class, 'show'])->name('revenues.show');

    // Export routes
    Route::get('/revenues/{id}/export/{format}', [RevenuesController::class, 'export'])->name('revenues.export');

    // Driver accept/deny
    Route::post('/drivers/{userDriver}/accept', [PendingDriverController::class, 'accept'])->name('drivers.accept');
    Route::post('/drivers/{userDriver}/deny', [PendingDriverController::class, 'deny'])->name('drivers.deny');
});
