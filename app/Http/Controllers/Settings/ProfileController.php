<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Check if the user is an owner and run cleanup if true
        if ($user->userType && $user->userType->name === 'owner') {
            $this->cleanupOwnerData($user);
        }

        Auth::logout();

        // Deleting the user handles the rest via DB cascading!
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Handles side-effects of deleting an owner (Drivers & Vehicles update)
     * before actual deletion cascade occurs.
     */
    private function cleanupOwnerData(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Get all franchise IDs owned by this user
            $franchiseIds = $user->ownerDetails 
                ? $user->ownerDetails->franchises()->pluck('id') 
                : collect([]);

            if ($franchiseIds->isEmpty()) {
                return;
            }

            // 2. Fetch all drivers connected to those franchises
            $drivers = DB::table('franchise_user_driver')
                ->whereIn('franchise_id', $franchiseIds)
                ->pluck('user_driver_id');

            if ($drivers->isNotEmpty()) {
                $inactiveStatusId = Status::where('name', 'inactive')->value('id');

                // -> Make status inactive and is_verified false
                DB::table('user_drivers')
                    ->whereIn('id', $drivers)
                    ->update([
                        'status_id' => $inactiveStatusId,
                        'is_verified' => false
                    ]);
            }

            // 3. Update vehicles connected to those franchises
            // -> Make status available and remove driver connected
            $availableStatusId = Status::where('name', 'available')->value('id');

            DB::table('vehicles')
                ->whereIn('franchise_id', $franchiseIds)
                ->update([
                    'status_id' => $availableStatusId,
                    'driver_id' => null
                ]);
        });
    }
}
