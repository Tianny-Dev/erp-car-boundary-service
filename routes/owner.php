<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'user_type:owner', 'check.active'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('owner/dashboard/Index'))->name('dashboard');
    Route::get('/boundary-contracts', fn() => Inertia::render('owner/boundary-contracts/Index'))->name('boundaryContracts');
    Route::get('/revenue-management', fn() => Inertia::render('owner/revenue-management/Index'))->name('revenueManagement');
    Route::get('/expense-management', fn() => Inertia::render('owner/expense-management/Index'))->name('expenseManagement');
    Route::get('/reports-and-analytics', fn() => Inertia::render('owner/reports-and-analytics/Index'))->name('reportsAndAnalytics');
    Route::get('/support-center', fn() => Inertia::render('owner/support-center/Index'))->name('supportCenter');
    Route::get('/notifications', fn() => Inertia::render('owner/notifications/Index'))->name('notifications');
});
