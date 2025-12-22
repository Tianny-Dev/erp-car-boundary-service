<?php

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
use App\Http\Controllers\SuperAdmin\EarningController;
use App\Http\Controllers\SuperAdmin\BoundaryContractController;
use App\Http\Controllers\SuperAdmin\ExpenseController;
use App\Http\Controllers\SuperAdmin\GpsTrackerController;
use App\Http\Controllers\SuperAdmin\InventoryController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('/franchise/{franchise}', [FranchiseController::class, 'accept'])->name('franchise.accept');
    Route::get('/franchise/{franchise}', [FranchiseController::class, 'show'])->name('franchise.show');
    Route::get('/owner/{owner}', [OwnerController::class, 'show'])->name('owner.show');

    Route::patch('/branch/{branch}', [BranchController::class, 'assign'])->name('branch.assign');
    Route::get('/branch/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/branch', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');

    Route::get('/manager/create', [ManagerController::class, 'create'])->name('manager.create');
    Route::get('/manager/{manager}', [ManagerController::class, 'show'])->name('manager.show');
    Route::post('/manager', [ManagerController::class, 'store'])->name('manager.store');

    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
    Route::get('/revenue/show', [RevenueController::class, 'show'])->name('revenue.show');
    Route::get('/revenue/export/index', [RevenueController::class, 'exportIndex'])->name('revenue.export.index');
    Route::get('/revenue/export/show', [RevenueController::class, 'exportShow'])->name('revenue.export.show');

    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::get('/transaction/{id}', [TransactionController::class, 'show'])->name('transaction.show');

    Route::get('/allocation', [AllocationController::class, 'index'])->name('allocation.index');
    Route::post('/allocation', [AllocationController::class, 'store'])->name('allocation.store');
    Route::put('/allocation/{allocation}', [AllocationController::class, 'update'])->name('allocation.update');

    Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
    Route::get('/driver/{driver}', [DriverController::class, 'show'])->name('driver.show');

    Route::get('/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
    Route::post('/vehicle', [VehicleController::class, 'store'])->name('vehicle.store');
    Route::get('/vehicle/create', [VehicleController::class, 'create'])->name('vehicle.create');
    Route::get('/vehicle/{vehicle}', [VehicleController::class, 'show'])->name('vehicle.show');
    Route::patch('/vehicle/{vehicle}/change', [VehicleController::class, 'changeStatus'])->name('vehicle.change');
    Route::get('/vehicle/{vehicle}/maintenances', [VehicleController::class, 'maintenanceHistory'])->name('vehicle.maintenances');

    Route::get('/earning', [EarningController::class, 'index'])->name('earning.index');
    Route::get('/earning/show', [EarningController::class, 'show'])->name('earning.show');
    Route::get('/earning/export/index', [EarningController::class, 'exportIndex'])->name('earning.export.index');
    Route::get('/earning/export/show', [EarningController::class, 'exportShow'])->name('earning.export.show');

    Route::get('/boundary-contract', [BoundaryContractController::class, 'index'])->name('boundaryContract.index');
    Route::post('/boundary-contract', [BoundaryContractController::class, 'store'])->name('boundaryContract.store');
    Route::get('/boundary-contract/create', [BoundaryContractController::class, 'create'])->name('boundaryContract.create');
    Route::get('/boundary-contract/resources', [BoundaryContractController::class, 'getContractResources'])->name('boundaryContract.resources');
    Route::get('/boundary-contract/{contract}', [BoundaryContractController::class, 'show'])->name('boundaryContract.show');

    Route::get('/gps-tracker', [GpsTrackerController::class, 'index'])->name('gpsTracker.index');

    Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('/expense/show', [ExpenseController::class, 'show'])->name('expense.show');
    Route::get('/expense/export', [ExpenseController::class, 'export'])->name('expense.export');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
});
