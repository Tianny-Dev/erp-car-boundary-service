<?php

use App\Http\Controllers\SuperAdmin\DriverHistoryController;
use App\Http\Controllers\SuperAdmin\DriverReportController;
use App\Http\Controllers\SuperAdmin\RevenuesController;
use App\Http\Controllers\SuperAdmin\RevenueController;
use App\Http\Controllers\SuperAdmin\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\OwnerController;
use App\Http\Controllers\SuperAdmin\FranchiseController;
use App\Http\Controllers\SuperAdmin\BranchController;
use App\Http\Controllers\SuperAdmin\DriverController;
use App\Http\Controllers\SuperAdmin\ManagerController;
use App\Http\Controllers\SuperAdmin\VehicleController;
use App\Http\Controllers\SuperAdmin\TripController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/franchise/{franchise}', [FranchiseController::class, 'accept'])->name('franchise.accept');
    Route::get('/franchise/{franchise}', [FranchiseController::class, 'show'])->name('franchise.show');
    Route::get('/owner/{owner}', [OwnerController::class, 'show'])->name('owner.show');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');
    Route::get('/manager/{manager}', [ManagerController::class, 'show'])->name('manager.show');

    // Revenues
    Route::get('/revenues', [RevenuesController::class, 'index'])->name('revenues');

    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');

    Route::get('/revenue/export', [RevenueController::class, 'export'])->name('revenue.export');

    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction');
    Route::get('/transaction/{id}', [TransactionController::class, 'show'])->name('transaction.show');

    Route::get('/driverlist', [DriverHistoryController::class, 'index'])->name('driverlist');
    Route::get('/driverlist/{id}', [DriverHistoryController::class, 'show'])->name('driverlist.show');

    Route::get('/driverreport', [DriverReportController::class, 'index'])->name('driverreport');
    Route::get('/driverreport/export', [DriverReportController::class, 'export'])->name('driver.export');


    // Export All Route must be before the generic ID route
    Route::get('/revenues/all/export/{format}', [RevenuesController::class, 'exportAll'])->name('revenues.exportAll'); // ADDED & MOVED
    // Show All Route must be before the generic ID route
    Route::get('/revenues/all', [RevenuesController::class, 'showAll'])->name('revenues.showAll'); // ADDED & MOVED

    Route::get('/revenues/{id}', [RevenuesController::class, 'show'])->name('revenues.show');

    // Export routes
    Route::get('/revenues/{id}/export/{format}', [RevenuesController::class, 'export'])->name('revenues.export');



    Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/driver/{driver}', [DriverController::class, 'show'])->name('driver.show');

    Route::get('/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
    Route::get('/vehicle/{vehicle}', [VehicleController::class, 'show'])->name('vehicle.show');

    Route::get('/trip', [TripController::class, 'index'])->name('trip.index');
});
