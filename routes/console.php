<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ExpireBoundaryContracts;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpireBoundaryContracts::class)
    ->dailyAt('00:05') // Runs 5 mins past midnight daily
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        Log::error('ExpireBoundaryContracts cron job failed.');
    });

Schedule::call(function () {
    User::onlyTrashed()
        ->where('deleted_at', '<=', now()->subDays(30))
        ->forceDelete();
})->daily();
