<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\EarningDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\PercentageType;
use App\Models\RevenueBreakdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use App\Models\UserDriver;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EarningController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'driver' => ['sometimes', 'nullable', 'string'],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'driver' => $validated['driver'] ?? null,
            'period' => $validated['period'] ?? 'daily',
        ];
        

        // 3. Get Dynamic Fee Types for Column Headers
        $feeTypes = PercentageType::all()->map(function ($type) {
            return [
                'id' => $type->id,
                'db_name' => $type->name,
                'display' => Str::of($type->name)->replace('_', ' ')->title()->toString(),
                'slug' => Str::snake($type->name),
            ];
        });

        // 4. Build Base Query
        $query = $this->buildBaseQuery($filters);

        // 5. Build Dynamic Breakdown Subquery to calculate Total Deductions here for the Driver Earning math
        $breakdownSelects = [
            'revenue_breakdowns.revenue_id',
            DB::raw('SUM(revenue_breakdowns.total_earning) as total_deductions') // Sum of ALL fees
        ];
        // Dynamically add columns for each fee type
        foreach ($feeTypes as $type) {
            $breakdownSelects[] = DB::raw("SUM(CASE WHEN percentage_types.name = '{$type['db_name']}' THEN total_earning ELSE 0 END) as {$type['slug']}_amount");
        }
        $breakdownSubquery = RevenueBreakdown::query()
            ->join('percentage_types', 'revenue_breakdowns.percentage_type_id', '=', 'percentage_types.id')
            ->select($breakdownSelects)
            ->groupBy('revenue_breakdowns.revenue_id');
        // 6. Join Subquery
        $query->joinSub($breakdownSubquery, 'breakdowns', function ($join) {
            $join->on('revenues.id', '=', 'breakdowns.revenue_id');
        });

        // 7. Apply Grouping & Calculations
        $earnings = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab'], $feeTypes);
        $driversList = $this->getContextualDrivers($filters);

        // 8. Return all data to Inertia
        return Inertia::render('super-admin/finance/EarningIndex', [
            'earnings' => EarningDatatableResource::collection($earnings),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'drivers' => fn () => $driversList,
            'filters' => $filters,
            'feeTypes' => $feeTypes,
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters, ?int $year = null, ?array $months = null): Builder
    {
        $query = Revenue::query()
            // Base filters: "paid" status and non-null payment_date
            ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
            ->whereNotNull('payment_date')
            ->where('service_type', 'Trips');

        // --- Apply date constraints for export only ---
        if ($year) {
            $query->whereYear('payment_date', $year);
        }
        if (! empty($months)) {
            $query->whereIn(DB::raw('MONTH(payment_date)'), $months);
        }

        // Filter by specific driver if selected
        $query->when($filters['driver'] && $filters['driver'] !== 'all', function ($q) use ($filters) {
            $q->where('driver_id', $filters['driver']);
        });

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
     * Efficiently fetches drivers based on the current view context
     */
    private function getContextualDrivers(array $filters)
    {
        // Start with UserDriver and join the base User table to get names
        $query = UserDriver::query()
            ->join('users', 'user_drivers.id', '=', 'users.id')
            ->select('user_drivers.id', 'users.name');

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

        return $query->orderBy('users.name')->get();
    }

    /**
     * Applies the SELECT and GROUP BY logic based on the period.
     */
    private function applyPeriodGrouping(Builder $query, string $period, string $tab, $feeTypes)
    {
        $query->join('user_drivers', 'revenues.driver_id', '=', 'user_drivers.id')
              ->join('users', 'user_drivers.id', '=', 'users.id');

        // Dynamic Selects for the Main Query Aggregation
        $selects = [
            DB::raw('SUM(revenues.amount) as total_amount'),
            // Driver Earning = Total Revenue - Total Deductions (calculated in subquery)
            DB::raw('(SUM(revenues.amount) - COALESCE(SUM(breakdowns.total_deductions), 0)) as driver_earning'),
            'users.name as driver_name'
        ];

        // Add SUM aggregators for each dynamic fee type
        foreach ($feeTypes as $type) {
            $selects[] = DB::raw("SUM(breakdowns.{$type['slug']}_amount) as total_{$type['slug']}");
        }

        // Add Franchise/Branch
        if ($tab === 'franchise') {
            $query->join('franchises', 'revenues.franchise_id', '=', 'franchises.id')
                ->addSelect('franchises.name as franchise_name')
                ->groupBy('franchises.id', 'franchises.name');
        } elseif ($tab === 'branch') {
            $query->join('branches', 'revenues.branch_id', '=', 'branches.id')
                ->addSelect('branches.name as branch_name')
                ->groupBy('branches.id', 'branches.name');
        }

        // Apply Selects
        $query->addSelect($selects)
              ->groupBy('users.id', 'users.name');

        // Time Period Grouping
        if ($period === 'daily') {
            return $query->addSelect('revenues.payment_date')
                ->groupBy('revenues.payment_date')
                ->orderBy('revenues.payment_date', 'desc')
                ->get();
        }
        if ($period === 'weekly') {
            return $query->addSelect(DB::raw('MIN(revenues.payment_date) as week_start, MAX(revenues.payment_date) as week_end'))
                ->groupByRaw('YEAR(revenues.payment_date), WEEK(revenues.payment_date, 1)')
                ->orderBy('week_start', 'desc')
                ->get();
        }
        if ($period === 'monthly') {
            return $query->addSelect(DB::raw('DATE_FORMAT(revenues.payment_date, "%M %Y") as month_name, MIN(revenues.payment_date) as month_sort'))
                ->groupByRaw('DATE_FORMAT(revenues.payment_date, "%M %Y")')
                ->orderBy('month_sort', 'desc')
                ->get();
        }

        return $query->get();
    }

}
