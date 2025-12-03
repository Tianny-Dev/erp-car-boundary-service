<?php

use App\Http\Controllers\Technician\DashboardController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:technician', 'check.active'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
