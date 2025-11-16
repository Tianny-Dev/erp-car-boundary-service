<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\RevenueDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class RevenueController extends Controller
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
            'period' => $validated['period'] ?? 'daily',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $revenues = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab']);

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/finance/RevenueIndex', [
            'revenues' => RevenueDatatableResource::collection($revenues),
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
        $query = Revenue::query()
            // Base filters: "paid" status and non-null payment_date
            ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
            ->whereNotNull('payment_date')
            ->where('service_type', $filters['service']);

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($filters['franchise'], fn ($q) => $q->where('franchise_id', $filters['franchise']));

        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($filters['branch'], fn ($q) => $q->where('branch_id', $filters['branch']));
        }

        return $query;
    }

    /**
     * Applies the SELECT and GROUP BY logic based on the period.
     */
    private function applyPeriodGrouping(
        Builder $query,
        string $period,
        string $tab
    ) {
        // --- Handle Daily Period (Standard Eloquent) ---
        if ($period === 'daily') {
            $selects = [
                'id', 'franchise_id', 'branch_id', 'invoice_no',
                'service_type', 'amount', 'payment_date',
            ];

            // Eager load the relationship
            if ($tab === 'franchise') {
                $query->with('franchise:id,name');
            } elseif ($tab === 'branch') {
                $query->with('branch:id,name');
            }

            return $query->select($selects)
                ->orderBy('payment_date', 'desc')
                ->get();
        }

        // --- Handle Weekly/Monthly (Grouped Query with JOINs) ---

        // Base selections for grouping
        $query->selectRaw('
            SUM(revenues.amount) as total_amount,
            revenues.service_type
        ');

        // Add JOINs and group by franchise/branch
        if ($tab === 'franchise') {
            $query->join('franchises', 'revenues.franchise_id', '=', 'franchises.id')
                ->addSelect('franchises.id as franchise_id', 'franchises.name as franchise_name')
                ->groupBy('franchises.id', 'franchises.name');
        } elseif ($tab === 'branch') {
            $query->join('branches', 'revenues.branch_id', '=', 'branches.id')
                ->addSelect('branches.id as branch_id', 'branches.name as branch_name')
                ->groupBy('branches.id', 'branches.name');
        }

        // Apply period-specific grouping
        if ($period === 'weekly') {
            $query->addSelect(DB::raw('MIN(revenues.payment_date) as week_start, MAX(revenues.payment_date) as week_end'))
                // Group by week (mode 1 starts on Monday), year, and service_type
                ->groupByRaw('YEAR(revenues.payment_date), WEEK(revenues.payment_date, 1), revenues.service_type')
                ->orderBy('week_start', 'desc');
        }

        if ($period === 'monthly') {
            $query->addSelect(DB::raw('DATE_FORMAT(revenues.payment_date, "%M %Y") as month_name, MIN(revenues.payment_date) as month_sort'))
                // **THE FIX**: Group by the non-aggregate aliases. The aggregate 'month_sort' is removed from group by.
                ->groupBy('month_name', 'revenues.service_type')
                ->orderBy('month_sort', 'desc');
        }

        return $query->get();
    }
}