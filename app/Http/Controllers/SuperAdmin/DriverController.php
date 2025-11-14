<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\DriverDatatableResource;
use App\Http\Resources\SuperAdmin\DriverResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // 1. Validate tab, default to 'franchise'
        $tab = $request->input('tab', 'franchise');
        if (! in_array($tab, ['franchise', 'branch'])) {
            $tab = 'franchise';
        }

        // 2. Start base query
        $query = UserDriver::with([
            'user:id,name,email,phone',
            'status:id,name',
        ]);

        // 3. Apply conditional filtering based on tab
        if ($tab === 'franchise') {
            $query->whereHas('franchises', function ($q) use ($request) {
                // If a specific franchise ID is provided, filter by it
                $q->when($request->input('franchise'), function ($sq) use ($request) {
                    $sq->where('franchises.id', $request->input('franchise'));
                });
            });
            // Eager load franchises to make name available in the resource
            $query->with('franchises:id,name');

        } elseif ($tab === 'branch') {
            $query->whereHas('branches', function ($q) use ($request) {
                // If a specific branch ID is provided, filter by it
                $q->when($request->input('branch'), function ($sq) use ($request) {
                    $sq->where('branches.id', $request->input('branch'));
                });
            });
            // Eager load branches to make name available in the resource
            $query->with('branches:id,name');
        }

        $drivers = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/DriverManagement', [
            'drivers' => DriverDatatableResource::collection($drivers),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => [
                'tab' => $tab,
                'franchise' => $request->input('franchise'),
                'branch' => $request->input('branch'),
            ],
        ]);
    }

    public function show(UserDriver $driver)
    {
        // Load relationships and return as JSON
        $driver->loadMissing(['user:id,name,email,phone,gender,address,region,city,barangay,province,postal_code', 'status:id,name']);

        return new DriverResource($driver);
    }
}