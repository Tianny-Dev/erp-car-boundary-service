<?php

use App\Http\Controllers\Owner\BoundaryContractController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\ExpenseManagementController;
use App\Http\Controllers\Owner\FranchiseDriverController;
use App\Http\Controllers\Owner\NotificationController;
use App\Http\Controllers\Owner\ReportAndAnalyticController;
use App\Http\Controllers\Owner\RevenueManagementController;
use App\Http\Controllers\Owner\SupportCenterController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'user_type:owner', 'check.active'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/boundary-contracts', [BoundaryContractController::class, 'index'])->name('boundaryContracts');
    Route::get('/revenue-management', [RevenueManagementController::class, 'index'])->name('revenueManagement');
    Route::get('/expense-management', [ExpenseManagementController::class, 'index'])->name('expenseManagement');
    Route::get('/reports-and-analytics', [ReportAndAnalyticController::class, 'index'])->name('reportsAndAnalytics');
    Route::get('/support-center', [SupportCenterController::class, 'index'])->name('supportCenter');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

    Route::resource('drivers', FranchiseDriverController::class);

    Route::put('/drivers/{id}/status', [FranchiseDriverController::class, 'updateStatus'])
    ->name('drivers.updateStatus');
});
