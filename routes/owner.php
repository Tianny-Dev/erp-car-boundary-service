<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:owner', 'check.active'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('owner/dashboard/Index'))->name('dashboard');
});
