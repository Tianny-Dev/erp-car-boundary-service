<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\VehicleDatatableResource;
use App\Http\Resources\SuperAdmin\MaintenanceHistoryResource;
use App\Http\Resources\SuperAdmin\VehicleResource;
use App\Http\Requests\SuperAdmin\StoreVehicleRequest;
use App\Models\Vehicle;
use App\Models\Franchise;
use App\Models\Status;
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
            'franchise' => ['sometimes', 'nullable', 'array'], 
            'status' => ['sometimes', 'string', Rule::in(['active', 'available', 'maintenance'])],
        ]);

        // 2. Set defaults
        $filters = [
            'franchise' => $validated['franchise'] ?? [],
            'status' => $validated['status'] ?? 'active',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $vehicles = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/VehicleIndex', [
            'vehicles' => VehicleDatatableResource::collection($vehicles),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'filters' => [
                'franchise' => $filters['franchise'],
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

        $query->whereNotNull('franchise_id')
            ->when(!empty($filters['franchise']), fn ($q) => $q->whereIn('franchise_id', $filters['franchise']))
            ->with('franchise:id,name');

        return $query;
    }

    public function show(Vehicle $vehicle)
    {
        // Load relationships and return as JSON
        $vehicle->loadMissing(['status:id,name']);

        return new VehicleResource($vehicle);
    }

    public function create(): Response
    {
        return Inertia::render('super-admin/fleet/VehicleCreate', [
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
        ]);
    }

    public function changeStatus(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['active', 'available', 'maintenance'])],
        ]);

        $status = Status::where('name', $request->status)->firstOrFail();
        $vehicle->status_id = $status->id;
        $vehicle->save();

        return back();
    }

    public function store(StoreVehicleRequest $request)
    {
        $availableStatusId = Status::where('name', 'available')->firstOrFail()->id;

        Vehicle::create([
            'status_id' => $availableStatusId,
            'franchise_id' => $request->franchise_id,
            'plate_number' => $request->plate_number,
            'vin' => $request->vin,
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'year' => $request->year
        ]);

        return redirect(route('super-admin.vehicle.index'));
    }

    public function maintenanceHistory(Vehicle $vehicle)
    {
        // Eager load maintenance with its related inventory
        $vehicle->loadMissing(['maintenances' => function($query) {
            $query->orderBy('maintenance_date', 'desc')
                ->with('inventory:id,name,category,specification'); // eager load inventory
        }]);

        return MaintenanceHistoryResource::collection($vehicle->maintenances);
    }

}
