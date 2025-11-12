<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\OwnerController;
use App\Http\Controllers\SuperAdmin\FranchiseController;

Route::middleware(['auth', 'verified', 'user_type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/franchise/{franchise}', [FranchiseController::class, 'accept'])->name('franchise.accept');
    Route::get('/franchise/{franchise}', [FranchiseController::class, 'show'])->name('franchise.show');
    Route::get('/owner/{owner}', [OwnerController::class, 'show'])->name('owner.show');
});
