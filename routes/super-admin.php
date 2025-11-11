<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/dashboard/{franchise}', [DashboardController::class, 'acceptFranchise'])->name('dashboard.franchise.accept');
    Route::get('/dashboard/franchise/{id}', [DashboardController::class, 'showFranchise'])->name('dashboard.franchise.show');
    Route::get('/dashboard/owner/{id}', [DashboardController::class, 'showOwner'])->name('dashboard.owner.show');
});
