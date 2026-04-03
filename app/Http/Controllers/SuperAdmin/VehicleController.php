<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreVehicleRequest;
use App\Http\Resources\SuperAdmin\MaintenanceHistoryResource;
use App\Http\Resources\SuperAdmin\VehicleDatatableResource;
use App\Http\Resources\SuperAdmin\VehicleResource;
use App\Models\Franchise;
use App\Models\Status;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
            'franchises' => fn() => Franchise::select('id', 'name')->get(),
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
            'franchise:id,name'
        ]);
        $query->whereHas('status', fn($q) => $q->where('name', $filters['status']));
        // Apply Franchise/Driver constraints based on status
        if ($filters['status'] !== 'available') {
            // If NOT available, these fields MUST be present
            $query->whereNotNull('franchise_id');
        } 
        // Filter by specific franchise IDs if provided
        $query->when(!empty($filters['franchise']), function ($q) use ($filters) {
            $q->whereIn('franchise_id', $filters['franchise']);
        });

        return $query;
    }

    public function show(Vehicle $vehicle)
    {
        // Load relationships and return as JSON
        $vehicle->loadMissing(['status:id,name', 'driver.user:id,username', 'franchise:id,name']);

        return new VehicleResource($vehicle);
    }

    public function create(): Response
    {
        return Inertia::render('super-admin/fleet/VehicleCreate', [
            'franchises' => fn() => Franchise::select('id', 'name')->get(),
        ]);
    }

    public function assignFranchise(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'franchise_id' => ['required', 'integer', 'exists:franchises,id'],
        ]);

        $vehicle->loadMissing('status');
        if ($vehicle->franchise_id !== null || $vehicle->status->name !== 'available') {
            return back()->withErrors([
                'franchise_id' => 'This vehicle is already assigned or is not available for assignment.'
            ]);
        }
        
        $vehicle->franchise_id = $request->franchise_id;
        $vehicle->save();

        return back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'franchise_id' => 'required|exists:franchises,id',
            'plate_number' => [
                'required',
                'string',
                'unique:vehicles,plate_number',
                'regex:/^([A-Z]{3}\s?\d{3,4}|[A-Z]{2}\s?\d{5})$/i'
            ],
            'vin' => [
                'required',
                'string',
                'size:17',
                'unique:vehicles,vin',
                'regex:/^[A-HJ-NPR-Z0-9]+$/i'
            ],
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'color' => 'required|string|max:30',
            'year' => 'required|integer|digits:4|between:1900,' . (date('Y') + 1),
            'or_cr' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'plate_number.regex' => 'The plate number format is invalid (e.g., ABC 1234 or AB 12345).',
            'vin.regex' => 'The VIN contains invalid characters (I, O, and Q are not allowed).',
        ]);

        $availableStatusId = Status::where('name', 'available')->firstOrFail()->id;

        $vehicle = new Vehicle($request->except('or_cr'));
        $vehicle->status_id = $availableStatusId;
        $vehicle->plate_number = strtoupper(trim($request->plate_number));
        $vehicle->vin = strtoupper(trim($request->vin));

        if ($request->hasFile('or_cr')) {
            $file = $request->file('or_cr');
            $safePlate = str_replace(' ', '_', $vehicle->plate_number);
            $filename = time() . '_or_cr_' . $safePlate . '.' . $file->getClientOriginalExtension();

            $file->storeAs('vehicle_documents', $filename, 'public');
            $vehicle->or_cr = $filename;
        }

        $vehicle->save();

        return redirect()
            ->route('super-admin.vehicle.index', ['status' => 'available'])
            ->with('success', 'Vehicle created!');
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'plate_number' => [
                'required',
                'string',
                // 'unique:vehicles,plate_number',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id),
                'regex:/^([A-Z]{3}\s?\d{3,4}|[A-Z]{2}\s?\d{5})$/i'
            ],
            'vin' => [
                'required',
                'string',
                'size:17',
                // 'unique:vehicles,vin',
                Rule::unique('vehicles', 'vin')->ignore($vehicle->id),
                'regex:/^[A-HJ-NPR-Z0-9]+$/i'
            ],
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'color' => 'required|string|max:30',
            'year' => 'required|integer|digits:4|between:1900,' . (date('Y') + 1),
            'or_cr' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'plate_number.regex' => 'The plate number format is invalid (e.g., ABC 1234 or AB 12345).',
            'vin.regex' => 'The VIN contains invalid characters (I, O, and Q are not allowed).',
        ]);

        $vehicle->fill($request->except('or_cr'));
        $vehicle->plate_number = strtoupper(trim($request->plate_number));
        $vehicle->vin = strtoupper(trim($request->vin));

        if ($request->hasFile('or_cr')) {
            // Delete old file if it exists
            if ($vehicle->or_cr) {
                Storage::disk('public')->delete('vehicle_documents/' . $vehicle->or_cr);
            }

            $file = $request->file('or_cr');
            $safePlate = str_replace(' ', '_', $vehicle->plate_number);
            $filename = time() . '_or_cr_' . $safePlate . '.' . $file->getClientOriginalExtension();

            $file->storeAs('vehicle_documents', $filename, 'public');
            $vehicle->or_cr = $filename;
        }

        $vehicle->save();

        return back()->with('success', 'Vehicle updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'current_password'], 
        ]);

        DB::transaction(function () use ($id) {

            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
        });

        return back()->with('success', 'Vehicle deleted successfully.');
    }

    public function maintenanceHistory(Vehicle $vehicle)
    {
        // Eager load maintenance with its related inventory
        $vehicle->loadMissing([
            'maintenances' => function ($query) {
                $query->orderBy('maintenance_date', 'desc')
                    ->with('inventory:id,name,category,specification'); // eager load inventory
            }
        ]);

        return MaintenanceHistoryResource::collection($vehicle->maintenances);
    }
}
