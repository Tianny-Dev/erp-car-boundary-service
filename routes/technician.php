<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('technician/dashboard/Index'))->name('dashboard');
});
