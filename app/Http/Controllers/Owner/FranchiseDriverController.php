<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FranchiseDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = User::with('driverDetails.status')
            ->whereHas('userType', fn($q) => $q->where('name', 'driver'))
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

   public function updateStatus($id)
    {
        $driver = User::findOrFail($id);
        $driverDetail = $driver->driverDetails;

        if (!$driverDetail) {
            abort(404, 'Driver details not found');
        }

        // Toggle between active and inactive
        $driverDetail->status_id = $driverDetail->status_id === 1 ? 2 : 1;
        $driverDetail->save();

        return back()->with('success', 'Driver status updated successfully.');
    }
}
