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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'available', 'maintenance'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $vehicles = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/VehicleIndex', [
            'vehicles' => VehicleDatatableResource::collection($vehicles),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => [
                'tab' => $filters['tab'],
                'franchise' => $filters['franchise'],
                'branch' => $filters['branch'],
                'status' => $filters['status'],
            ],
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = Vehicle::with([
            'status:id,name',
        ])->whereHas('status', fn ($q) => $q->where('name', $filters['status']));

        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($filters['franchise'], fn($q) => $q->where('franchise_id', $filters['franchise']))
                ->with('franchise:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($filters['branch'], fn($q) => $q->where('branch_id', $filters['branch']))
                ->with('branch:id,name');
        }
        
        return $query;
    }

    public function show(Vehicle $vehicle)
    {
        // Load relationships and return as JSON
        $vehicle->loadMissing(['status:id,name']);

        return new VehicleResource($vehicle);
    }
}
