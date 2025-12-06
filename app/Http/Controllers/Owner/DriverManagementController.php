<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            abort(404, 'Franchise not found');
        }

        $statusQuery = ['active', 'inactive', 'suspended', 'retired'];

        $drivers = $franchise->drivers()
            ->with(['user', 'status'])
            ->whereHas('status', function ($query) use ($statusQuery) {
                $query->whereIn('name', $statusQuery);
            })
            ->paginate(10)
            ->through(fn($driver) => [
                'id' => $driver->id,
                'name' => $driver->user?->name,
                'username' => $driver->user?->username,
                'email' => $driver->user?->email,
                'phone' => $driver->user?->phone,
                'region' => $driver->user?->region,
                'province' => $driver->user?->province,
                'city' => $driver->user?->city,
                'barangay' => $driver->user?->barangay,
                'address' => $driver->user?->address,
                'status' => $driver->status?->name,

                'details' => [
                    'license_number'  => $driver->driverDetails?->license_number,
                    'license_expiry'  => $driver->driverDetails?->license_expiry,
                    'is_verified'     => $driver->driverDetails?->is_verified,
                    'shift'           => $driver->driverDetails?->shift,
                    'hire_date'       => $driver->driverDetails?->hire_date,

                    'front_license_picture' => $driver->driverDetails?->front_license_picture
                        ? asset('storage/' . $driver->driverDetails->front_license_picture)
                        : null,

                    'back_license_picture' => $driver->driverDetails?->back_license_picture
                        ? asset('storage/' . $driver->driverDetails->back_license_picture)
                        : null,

                    'nbi_clearance' => $driver->driverDetails?->nbi_clearance
                        ? asset('storage/' . $driver->driverDetails->nbi_clearance)
                        : null,

                    'selfie_picture' => $driver->driverDetails?->selfie_picture
                        ? asset('storage/' . $driver->driverDetails->selfie_picture)
                        : null,
                ],
            ]);

        $statuses = Status::whereIn('name', ['active', 'suspended', 'retired', 'inactive'])
            ->get(['id', 'name']);

        return Inertia::render('owner/driver-management/Index', [
            'drivers' => $drivers,
            'statuses' => $statuses,
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
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            return response()->json(['message' => 'Franchise not found'], 404);
        }

        $driver = $franchise->drivers()->find($id);
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $driver->update([
            'status_id' => $request->status_id
        ]);

        return redirect()->back()->with('success', 'Driver status updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $driver = UserDriver::findOrFail($id);

        $ownerFranchises = auth()->user()->ownerDetails->franchises->pluck('id');

        $driver->franchises()->detach($ownerFranchises);
        $driver->status_id = 6;
        $driver->save();

        return back()->with('success', 'Driver removed from your franchise(s) successfully.');
    }
}
