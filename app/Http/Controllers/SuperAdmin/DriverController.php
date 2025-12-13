<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\DriverDatatableResource;
use App\Http\Resources\SuperAdmin\DriverVerificationResource;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuperAdmin\DriverResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
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
        return Inertia::render('super-admin/fleet/DriverIndex', [
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

    public function assign(Request $request, UserDriver $driver)
    {
        $validated = $request->validate([
            'assign_type' => ['required', 'string', 'in:franchise,branch'],
            'assign_id' => ['required', 'integer'],
        ]);

        DB::transaction(function () use ($driver, $validated) {
            $activeStatus = Status::where('name', 'active')->firstOrFail();

            // 1. Attach to the specific Pivot Table
            if ($validated['assign_type'] === 'franchise') {
                // Ensure franchise exists
                $franchise = Franchise::findOrFail($validated['assign_id']);
                // Sync without detaching if uniqueness is not needed,
                $franchise->drivers()->sync([$driver->id]);
            } else {
                $branch = Branch::findOrFail($validated['assign_id']);
                $branch->drivers()->sync([$driver->id]);
            }

            // 2. Update Driver Details (Hire Date & Status)
            $driver->update([
                'hire_date' => Carbon::now(),
                'status_id' => $activeStatus->id,
            ]);
        });

        return back();
    }

    /**
     * Display a listing of the resource.
     */
    public function verification(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['inactive', 'pending'])],
        ]);

        // 2. Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'inactive',
        ];

        // 3. Build and execute query
        $query = UserDriver::with([
            'user:id,username,email,phone',
            'status:id,name',
        ])->whereHas('status', fn ($q) => $q->where('name', $filters['status']));
        $drivers = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/DriverVerification', [
            'drivers' => DriverVerificationResource::collection($drivers),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => [
                'status' => $filters['status'],
            ],
        ]);
    }

    public function verify(UserDriver $driver)
    {
        $pendingStatus = Status::where('name', 'pending')->firstOrFail();

        DB::transaction(function () use ($driver, $pendingStatus) {
            // Update driver status and verified
            $driver->status_id = $pendingStatus->id;
            $driver->is_verified = true;
            $driver->save();

        });

        return back();
    }

    public function changeStatus(Request $request, UserDriver $driver)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['active', 'retired', 'suspended'])],
        ]);

        $status = Status::where('name', $request->status)->firstOrFail();
        $driver->status_id = $status->id;
        $driver->save();

        return back();
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
            $query->whereHas('franchises', function ($q) use ($filters) {
                $q->when($filters['franchise'], fn ($subQ) =>
                    $subQ->where('franchises.id', $filters['franchise'])
                );
            });

            // Eager load franchises to make name available in the resource
            $query->with('franchises:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereHas('branches', function ($q) use ($filters) {
                $q->when($filters['branch'], fn ($subQ) =>
                    $subQ->where('branches.id', $filters['branch'])
                );
            });

            // Eager load branches to make name available in the resource
            $query->with('branches:id,name');
        }

        return $query;
    }

    public function show(UserDriver $driver)
    {
        // Load relationships and return as JSON
        $driver->loadMissing(['user:id,username,name,email,phone,gender,address,region,city,barangay,province,postal_code', 'status:id,name']);

        return new DriverResource($driver);
    }
}
