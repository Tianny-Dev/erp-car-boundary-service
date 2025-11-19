<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\TransactionDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
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
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'service' => ['sometimes', 'string', Rule::in(['Trips', 'Boundary'])],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'service' => $validated['service'] ?? 'Trips',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters)->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/finance/TransactionIndex', [
            'transactions' => TransactionDatatableResource::collection($query),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
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
            'driver.user:id,name',
        ])->where('service_type', $filters['service']);

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($filters['franchise'], fn ($q) => $q->where('franchise_id', $filters['franchise']));

            $query->with('franchise:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($filters['branch'], fn ($q) => $q->where('branch_id', $filters['branch']));
                
            $query->with('franchise:id,name');
        }

        return $query;
    }
}
