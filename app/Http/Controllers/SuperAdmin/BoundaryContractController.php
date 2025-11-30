<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuperAdmin\BoundaryContractDatatableResource;
use App\Http\Resources\SuperAdmin\BoundaryContractResource;
use App\Models\BoundaryContract;
use App\Models\Branch;
use App\Models\Franchise;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class BoundaryContractController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'pending', 'terminated', 'expired'])],
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
        $contracts = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/fleet/BoundaryContract', [
            'contracts' => BoundaryContractDatatableResource::collection($contracts),
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
        $query = BoundaryContract::with([
            'driver.user:id,name',
            'status:id,name',
        ])->whereHas('status', fn ($q) => $q->where('name', $filters['status']));

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($filters['franchise'], fn($q) => $q->where('franchise_id',  $filters['franchise']))
                ->with('franchise:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($filters['branch'], fn($q) => $q->where('branch_id', $filters['branch']))
                ->with('branch:id,name');
        }

        return $query;
    }

    public function show(BoundaryContract $contract)
    {
        // Load relationships and return as JSON
        $contract->loadMissing([
            'driver.user:id,name,email,phone',
            'franchise:id,name,email,phone',
            'branch:id,name,email,phone', 
            'status:id,name'
        ]);

        return new BoundaryContractResource($contract);
    }
}
