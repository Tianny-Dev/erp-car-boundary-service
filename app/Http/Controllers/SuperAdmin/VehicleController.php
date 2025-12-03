<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\VehicleDatatableResource;
use App\Http\Resources\SuperAdmin\VehicleResource;
use App\Models\Vehicle;
use App\Models\Branch;
use App\Models\Franchise;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate tab, default to 'franchise'
        $tab = $request->input('tab', 'franchise');
        if (! in_array($tab, ['franchise', 'branch'])) {
            $tab = 'franchise';
        }

        // 2. Start base query
        $query = Vehicle::select('id', 'franchise_id', 'branch_id', 'vin', 'plate_number', 'status_id')
        ->with([
            'status:id,name',
        ]);

        // 3. Apply conditional filtering based on tab
        if ($tab === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($request->input('franchise'), fn($q) => $q->where('franchise_id', $request->input('franchise')))
                ->with('franchise:id,name');

        } elseif ($tab === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($request->input('branch'), fn($q) => $q->where('branch_id', $request->input('branch')))
                ->with('branch:id,name');
        }

        $drivers = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/VehicleIndex', [
            'vehicles' => VehicleDatatableResource::collection($drivers),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => [
                'tab' => $tab,
                'franchise' => $request->input('franchise'),
                'branch' => $request->input('branch'),
            ],
        ]);
    }

    public function show(Vehicle $vehicle)
    {
        // Load relationships and return as JSON
        $vehicle->loadMissing(['status:id,name']);

        return new VehicleResource($vehicle);
    }
}
