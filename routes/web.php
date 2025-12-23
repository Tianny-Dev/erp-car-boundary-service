<?php
use App\Models\UserType;
use App\Models\Franchise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\FranchiseContractController;
use Inertia\Inertia;
use Laravel\Fortify\Features;


Route::get('/', function () {
    $userTypes = UserType::whereNotIn('name', ['super_admin', 'manager'])
        ->get()
        ->map(fn($type) => [
            'name' => $type->name,
            'encrypted_id' => Crypt::encryptString($type->id),
        ]);

    return Inertia::render('Home', [
        'canRegister' => Features::enabled(Features::registration()),
        'userTypes' => $userTypes,
        'franchises' => Franchise::select(['id', 'name', 'region', 'province', 'city', 'latitude', 'longitude'])->get(),
    ]);
})->name('home');

Route::get('/inactive', function () {
    return Inertia::render('InactiveAccount');
})->name('inactive')->middleware('check.inactive');

Route::get('dashboard', function (Request $request) {
    $user = $request->user();

    $route = match ($user->userType->name) {
        'driver' => route('driver.dashboard'),
        'passenger' => route('passenger.dashboard'),
        'technician' => route('technician.dashboard'),
        'owner' => route('owner.dashboard'),
        'manager' => route('manager.dashboard'),
        'super_admin' => route('super-admin.dashboard'),
        default => route('home'),
    };

    return redirect($route);

})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified', 'check.active'])->group(function () {
    Route::get('/franchise/contract', [FranchiseContractController::class, 'index'])->name('contract');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/driver.php';
require __DIR__ . '/passenger.php';
require __DIR__ . '/technician.php';
require __DIR__ . '/owner.php';
require __DIR__ . '/manager.php';
require __DIR__ . '/finance.php';
require __DIR__ .'/auth.php';
require __DIR__ .'/super-admin.php';
