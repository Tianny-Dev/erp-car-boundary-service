<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\TransactionDatatableResource;
use App\Http\Resources\SuperAdmin\TransactionResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'array'], 
            'branch' => ['sometimes', 'nullable', 'array'],
            'driver' => ['sometimes', 'nullable', 'array'],
            'service' => ['sometimes', 'string', Rule::in(['Trips', 'Boundary'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? [],
            'branch' => $validated['branch'] ?? [],
            'driver' => $validated['driver'] ?? [],
            'service' => $validated['service'] ?? 'Trips',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters)->get();

        // 4. Fetch Context-Aware Drivers List
        $driversList = $this->getContextualDrivers($filters);

        // 5. Return all data to Inertia
        return Inertia::render('super-admin/finance/TransactionIndex', [
            'transactions' => TransactionDatatableResource::collection($query),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'drivers' => fn () => $driversList,
            'filters' => $filters,
        ]);
        
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = Revenue::with([
            'status:id,name',
            'driver.user:id,username',
        ])->where('service_type', $filters['service']);

        // Filter by specific driver if selected
        $query->when(!empty($filters['driver']), function ($q) use ($filters) {
            $q->whereIn('driver_id', $filters['driver']);
        });

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when(!empty($filters['franchise']), fn ($q) => $q->whereIn('franchise_id', $filters['franchise']));
            $query->with('franchise:id,name');
        } else {
            $query->whereNotNull('branch_id')
                ->when(!empty($filters['branch']), fn ($q) => $q->whereIn('branch_id', $filters['branch']));
            $query->with('branch:id,name');
        }

        return $query;
    }

    /**
     * Efficiently fetches drivers based on the current view context
     */
    private function getContextualDrivers(array $filters)
    {
        // Start with UserDriver and join the base User table to get username
        $query = UserDriver::query()
            ->join('users', 'user_drivers.id', '=', 'users.id')
            ->select('user_drivers.id', 'users.username');

        if ($filters['tab'] === 'franchise') {
            if (!empty($filters['franchise']) && $filters['franchise'] !== 'all') {
                // Get drivers strictly belonging to this franchise
                $query->whereHas('franchises', function ($q) use ($filters) {
                    $q->where('franchises.id', $filters['franchise']);
                });
            } else {
                // Get ALL drivers that belong to ANY franchise
                $query->has('franchises');
            }
        } elseif ($filters['tab'] === 'branch') {
            if (!empty($filters['branch']) && $filters['branch'] !== 'all') {
                // Get drivers strictly belonging to this branch
                $query->whereHas('branches', function ($q) use ($filters) {
                    $q->where('branches.id', $filters['branch']);
                });
            } else {
                // Get ALL drivers that belong to ANY branch
                $query->has('branches');
            }
        }

        return $query->orderBy('users.username')->get();
    }

    public function show(Revenue $transaction)
    {
        // Load relationships and return as JSON
        $transaction->loadMissing([
            'status:id,name',
            'driver.user:id,username',
            'franchise:id,name',
            'branch:id,name',
            'paymentOption:id,name',
        ]);

        return new TransactionResource($transaction);
    }
}
