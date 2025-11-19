<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('manager/dashboard/Index'))->name('dashboard');
});
