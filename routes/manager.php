<?php

use App\Http\Controllers\Manager\BoundaryContractController;
use App\Http\Controllers\Manager\BranchDriverController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\RevenueManagementController;
use App\Http\Controllers\Manager\ExpenseManagementController;
use App\Http\Controllers\Manager\ReportAndAnalyticController;
use App\Http\Controllers\Manager\SupportCenterController;
use App\Http\Controllers\Manager\NotificationController;
use App\Http\Controllers\Manager\PayOutController;
use App\Http\Controllers\Manager\SuspendDriverController;
use App\Http\Controllers\Manager\VehicleController;
use App\Http\Controllers\Manager\VehicleDriverController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/boundary-contracts', [BoundaryContractController::class, 'index'])->name('boundaryContracts');
    Route::get('/revenue-management', [RevenueManagementController::class, 'index'])->name('revenueManagement');
    Route::get('/expense-management', [ExpenseManagementController::class, 'index'])->name('expenseManagement');
    Route::get('/reports-and-analytics', [ReportAndAnalyticController::class, 'index'])->name('reportsAndAnalytics');
    Route::get('/support-center', [SupportCenterController::class, 'index'])->name('supportCenter');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/payout', [PayOutController::class, 'index'])->name('payout');

    Route::resource('drivers', BranchDriverController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('vehicle-drivers', VehicleDriverController::class);

    Route::put('/drivers/{id}/status', [BranchDriverController::class, 'updateStatus'])
    ->name('drivers.updateStatus');

    // Suspend Driver
    Route::resource('suspend-drivers', SuspendDriverController::class);
});
