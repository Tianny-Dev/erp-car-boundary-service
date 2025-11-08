<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:passenger'])->prefix('passenger')->name('passenger.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('passenger/dashboard/Index'))->name('dashboard');
});
