<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FranchiseDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $driversQuery = User::with('driverDetails.status')
            ->whereHas('userType', fn($q) => $q->where('name', 'driver'))
            // ->whereHas('driverDetails.status', fn($q) =>
            //     $q->whereIn('name', ['pending', 'inactive'])
            // );
            ->whereHas('driverDetails', fn ($q) =>
                $q->where('is_verified', 1)
                ->whereHas('status', fn ($s) =>
                    $s->whereIn('name', ['pending', 'inactive'])
                )
            );

        // Global search
        if ($search = request('search')) {
            $driversQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $drivers = $driversQuery->paginate(10)->through(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'region' => $user->region,
            'province' => $user->province,
            'city' => $user->city,
            'barangay' => $user->barangay,
            'status' => $user->driverDetails?->status?->name,
        ]);

        return Inertia::render('owner/franchise-driver/Index', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $driver = UserDriver::findOrFail($id);

        $ownerFranchises = auth()->user()->ownerDetails->franchises->pluck('id');

        // Toggle status
        $driver->status_id = $driver->status_id === 1 ? 2 : 1;
        $driver->save();

        if ($driver->status_id === 1) {
            // Status is now active - attach to owner's franchises if not already attached
            $driver->franchises()->syncWithoutDetaching($ownerFranchises);
        } else {
            // Status is now inactive - detach from owner's franchises only
            $driver->franchises()->detach($ownerFranchises);
        }

        return back()->with('success', 'Driver status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(string $id)
    {
        //
    }
}
