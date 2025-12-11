<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\ExpenseDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\ExpenseExport;
use Maatwebsite\Excel\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'period' => $validated['period'] ?? 'daily',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $expenses = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab']);

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/finance/ExpenseIndex', [
            'expenses' => ExpenseDatatableResource::collection($expenses),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => $filters,
        ]);
        
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters, ?int $year = null, ?array $months = null): Builder
    {
        $query = Expense::query()
            ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
            ->whereNotNull('payment_date');

            // --- Apply date constraints for export only ---
            if ($year) {
                $query->whereYear('payment_date', $year);
            }
            if (! empty($months)) {
                $query->whereIn(DB::raw('MONTH(payment_date)'), $months);
            }

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
    private function applyPeriodGrouping(Builder $query, string $period, string $tab)
    {
        // Base selections for ALL periods (now including daily)
        $query->selectRaw('
            SUM(expenses.amount) as total_amount
        ');

        // Add JOINs and group by franchise/branch
        if ($tab === 'franchise') {
            $query->join('franchises', 'expenses.franchise_id', '=', 'franchises.id')
                ->addSelect('franchises.id as franchise_id', 'franchises.name as franchise_name')
                ->groupBy('franchises.id', 'franchises.name');
        } elseif ($tab === 'branch') {
            $query->join('branches', 'expenses.branch_id', '=', 'branches.id')
                ->addSelect('branches.id as branch_id', 'branches.name as branch_name')
                ->groupBy('branches.id', 'branches.name');
        }

        // Apply period-specific grouping
        if ($period === 'daily') {
            $query->addSelect(DB::raw('DATE(expenses.payment_date) as payment_date'))
                ->groupBy(DB::raw('DATE(expenses.payment_date)'))
                ->orderBy('payment_date', 'desc');
        } elseif ($period === 'weekly') {
            $query->addSelect(DB::raw('MIN(expenses.payment_date) as week_start, MAX(expenses.payment_date) as week_end'))
                ->groupByRaw('YEAR(expenses.payment_date), WEEK(expenses.payment_date, 1)')
                ->orderBy('week_start', 'desc');
        } elseif ($period === 'monthly') {
            $query->addSelect(DB::raw('DATE_FORMAT(expenses.payment_date, "%M %Y") as month_name, MIN(expenses.payment_date) as month_sort'))
                ->groupBy('month_name')
                ->orderBy('month_sort', 'desc');
        }

        return $query->get();
    }
}
