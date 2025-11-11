<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VehicleDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with(['driver.user', 'status'])->get()->map(function($vehicle) {
            return [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'vin' => $vehicle->vin,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'color' => $vehicle->color,
                'year' => $vehicle->year,
                'status_id' => $vehicle->status_id,
                'status_name' => $vehicle->status->name,
                'driver' => $vehicle->driver?->user ? [
                    'id' => $vehicle->driver->user->id,
                    'name' => $vehicle->driver->user->name,
                    'email' => $vehicle->driver->user->email,
                    'phone' => $vehicle->driver->user->phone,
                ] : null,
            ];
        });

        $drivers = User::with('driverDetails.vehicles', 'driverDetails.status')
            ->whereHas('userType', fn($q) => $q->where('name', 'driver'))
            ->get()
            ->filter(fn($user) => $user->driverDetails && $user->driverDetails->vehicles->isEmpty())
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ])
            ->values();

        return Inertia::render('owner/vehicle-drivers/Index', [
            'vehicles' => $vehicles,
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
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $driver_id = $request->input('driver_id');
        $status_id = $driver_id ? 1 : 6;

        $vehicle->update([
            'driver_id' => $driver_id,
            'status_id' => $status_id,
        ]);

        return redirect()->back()->with('success', 'Driver assignment updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
