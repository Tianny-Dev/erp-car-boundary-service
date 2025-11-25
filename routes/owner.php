<?php

use App\Http\Controllers\Owner\BoundaryContractController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\DetailsDriverController;
use App\Http\Controllers\Owner\ReportDriverController;
use App\Http\Controllers\Owner\ExpenseManagementController;
use App\Http\Controllers\Owner\FranchiseDriverController;
use App\Http\Controllers\Owner\MaintenanceRequestController;
use App\Http\Controllers\Owner\NotificationController;
use App\Http\Controllers\Owner\PayOutController;
use App\Http\Controllers\Owner\ReportAndAnalyticController;
use App\Http\Controllers\Owner\RevenueManagementController;
use App\Http\Controllers\Owner\SupportCenterController;
use App\Http\Controllers\Owner\SuspendDriverController;
use App\Http\Controllers\Owner\VehicleController;
use App\Http\Controllers\Owner\VehicleDriverController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'user_type:owner', 'check.active'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/boundary-contracts', [BoundaryContractController::class, 'index'])->name('boundaryContracts');
    Route::get('/revenue-management', [RevenueManagementController::class, 'index'])->name('revenueManagement');
    Route::get('/expense-management', [ExpenseManagementController::class, 'index'])->name('expenseManagement');
    Route::get('/reports-and-analytics', [ReportAndAnalyticController::class, 'index'])->name('reportsAndAnalytics');
    Route::get('/support-center', [SupportCenterController::class, 'index'])->name('supportCenter');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/payout', [PayOutController::class, 'index'])->name('payout');

    Route::resource('drivers', FranchiseDriverController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('vehicle-drivers', VehicleDriverController::class);

    Route::put('/drivers/{id}/status', [FranchiseDriverController::class, 'updateStatus'])
    ->name('drivers.updateStatus');

    // export for driver
    Route::get('/driverreport', [ReportDriverController::class, 'index'])->name('driverownerreport');
    Route::get('/driverreport/export', [ReportDriverController::class, 'export'])->name('driverownerreport.export');
    Route::get('driverreport/details', [DetailsDriverController::class, 'show'])->name('driverownerreport.details');
    Route::get('/driverreport/details/export', [DetailsDriverController::class, 'exportDetails'])->name('driverownerreport_details.export');

    // Suspend Driver
    Route::resource('suspend-drivers', SuspendDriverController::class);

    Route::resource('maintenance-requests', MaintenanceRequestController::class);
});
