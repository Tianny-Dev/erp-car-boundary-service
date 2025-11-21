<?php

use App\Http\Controllers\SuperAdmin\DriverReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\OwnerController;
use App\Http\Controllers\SuperAdmin\FranchiseController;
use App\Http\Controllers\SuperAdmin\BranchController;
use App\Http\Controllers\SuperAdmin\DriverController;
use App\Http\Controllers\SuperAdmin\ManagerController;
use App\Http\Controllers\SuperAdmin\AllocationController;
use App\Http\Controllers\SuperAdmin\VehicleController;
use App\Http\Controllers\SuperAdmin\RevenueController;
use App\Http\Controllers\SuperAdmin\TransactionController;
use App\Http\Controllers\SuperAdmin\DriverDetailsController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::patch('/franchise/{franchise}', [FranchiseController::class, 'accept'])->name('franchise.accept');
    Route::get('/franchise/{franchise}', [FranchiseController::class, 'show'])->name('franchise.show');
    Route::get('/owner/{owner}', [OwnerController::class, 'show'])->name('owner.show');

    Route::get('/branch/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/branch', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');
   
    Route::get('/manager/{manager}', [ManagerController::class, 'show'])->name('manager.show');

    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');

    Route::get('/revenue/export', [RevenueController::class, 'export'])->name('revenue.export');

    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::get('/transaction/{id}', [TransactionController::class, 'show'])->name('transaction.show');

    Route::get('/allocation', [AllocationController::class, 'index'])->name('allocation.index');
    Route::post('/allocation', [AllocationController::class, 'store'])->name('allocation.store');
    Route::put('/allocation/{allocation}', [AllocationController::class, 'update'])->name('allocation.update');
    Route::delete('/allocation/{allocation}', [AllocationController::class, 'destroy'])->name('allocation.destroy');

    Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/driver/{driver}', [DriverController::class, 'show'])->name('driver.show');

    Route::get('/driverreport', [DriverReportController::class, 'index'])->name('driverreport');
    Route::get('/driverreport/export', [DriverReportController::class, 'export'])->name('driver.export');
    Route::get('driverreport/details', [DriverDetailsController::class, 'show'])->name('driverreport.details');

    Route::get('/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
    Route::get('/vehicle/{vehicle}', [VehicleController::class, 'show'])->name('vehicle.show');
});
