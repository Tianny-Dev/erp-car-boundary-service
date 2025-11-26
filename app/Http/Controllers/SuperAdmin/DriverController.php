<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\DriverDatatableResource;
use App\Http\Resources\SuperAdmin\DriverResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'retired', 'suspended'])],
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
        $drivers = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/DriverManagement', [
            'drivers' => DriverDatatableResource::collection($drivers),
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
        $query = UserDriver::with([
            'user:id,name,email,phone',
            'status:id,name',
        ])->whereHas('status', fn ($q) => $q->where('name', $filters['status']));

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereHas('franchises')
                ->when($filters['franchise'], fn ($q) => $q->where('franchises.id', $filters['franchise']));

            // Eager load franchises to make name available in the resource
            $query->with('franchises:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereHas('branches')
                ->when($filters['branch'], fn ($q) => $q->where('franchises.id', $filters['branch']));

            // Eager load branches to make name available in the resource
            $query->with('branches:id,name');
        }

        return $query;
    }

    public function show(UserDriver $driver)
    {
        // Load relationships and return as JSON
        $driver->loadMissing(['user:id,name,email,phone,gender,address,region,city,barangay,province,postal_code', 'status:id,name']);

        return new DriverResource($driver);
    }
}