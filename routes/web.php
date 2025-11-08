<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/inactive', function () {
    return Inertia::render('InactiveAccount');
})->name('inactive');

Route::get('dashboard', function (Request $request) {
    $user = $request->user();

    $route = match ($user->userType->name) {
        'driver' => route('driver.dashboard'),
        'passenger' => route('passenger.dashboard'),
        'technician' => route('technician.dashboard'),
        'owner' => route('owner.dashboard'),
        'super_admin' => route('super-admin.dashboard'),
        default => route('home'),
    };

    return redirect($route);

})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/driver.php';
require __DIR__ . '/passenger.php';
require __DIR__ . '/technician.php';
require __DIR__ . '/owner.php';
require __DIR__ . '/finance.php';
require __DIR__ .'/auth.php';
require __DIR__ .'/super-admin.php';
