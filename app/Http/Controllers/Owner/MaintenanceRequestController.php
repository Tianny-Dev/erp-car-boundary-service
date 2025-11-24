<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $franchise = $this->getFranchiseOrDefault();
        $franchiseId = $franchise?->id;

        $query = Maintenance::with(['status', 'franchise', 'vehicle'])
            ->when($franchiseId, fn ($q) => $q->where('franchise_id', $franchiseId))
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }
        }

        // Global search
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                ->orWhere('maintenance_type', 'like', "%{$search}%")
                ->orWhereHas('franchise', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('vehicle', function ($q2) use ($search) {
                    $q2->where(function ($q3) use ($search) {
                        $q3->where('plate_number', 'like', "%{$search}%")
                            ->orWhere('vin', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('color', 'like', "%{$search}%")
                            ->orWhere('year', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
                });
            });
        }

        $requests = $query
            ->paginate(10)
            ->through(function ($request) {
                return [
                    'id' => $request->id,
                    'maintenance_type' => $request->maintenance_type,
                    'description' => $request->description,
                    'maintenance_date' => $request->maintenance_date,
                    'next_maintenance_date' => $request->next_maintenance_date,

                    'vehicle' => $request->vehicle ? [
                        'id' => $request->vehicle->id,
                        'plate_number' => $request->vehicle->plate_number,
                        'vin' => $request->vehicle->vin,
                        'brand' => $request->vehicle->brand,
                        'model' => $request->vehicle->model,
                    ] : null,
                ];
            });

        return Inertia::render('owner/maintenance-requests/Index', [
            'requests' => $requests,
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


    /**
     * Find current franchise
     */
    protected function getFranchiseOrDefault()
    {
        return auth()->user()->ownerDetails?->franchises()->first();
    }
}
