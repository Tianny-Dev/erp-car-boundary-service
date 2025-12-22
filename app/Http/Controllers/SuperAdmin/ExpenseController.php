<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\ExpenseDatatableResource;
use App\Http\Resources\SuperAdmin\ExpenseShowResource;
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
            'franchise' => ['sometimes', 'nullable', 'array'], 
            'branch' => ['sometimes', 'nullable', 'array'],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? [],
            'branch' => $validated['branch'] ?? [],
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

    public function show(Request $request): Response
    {
        $validated = $request->validate([
            'start'     => ['required', 'date'],
            'end'       => ['required', 'date'],
            'label'     => ['required', 'string'],
            'tab'       => ['required', 'string'],
            'franchise' => ['nullable'],
            'branch'    => ['nullable'],
        ]);

        // 1. Determine which ID we are filtering for
        $id = $validated['tab'] === 'franchise' ? $validated['franchise'] : $validated['branch'];
        
        // 2. Normalize filters for the buildBaseQuery
        $filters = [
            'tab'       => $validated['tab'],
            'franchise' => $validated['tab'] === 'franchise' ? [$id] : [],
            'branch'    => $validated['tab'] === 'branch' ? [$id] : [],
        ];

        // 3. Fetch specific Target Name for header
        $targetName = 'N/A';
        if ($validated['tab'] === 'franchise' && $id) {
            $targetName = Franchise::find($id)?->name;
        } elseif ($validated['tab'] === 'branch' && $id) {
            $targetName = Branch::find($id)?->name;
        }

        // 4. Build Query
        $query = $this->buildBaseQuery($filters);

        // Filter by the exact date range from the clicked row
        $query->whereBetween(DB::raw('DATE(payment_date)'), [
            $validated['start'], 
            $validated['end']
        ]);

        $details = $query->with([
                'maintenance.vehicle:id,plate_number', 
                'maintenance.inventory:id,name'])
            ->orderBy('payment_date', 'desc')
            ->get();

        return Inertia::render('super-admin/finance/ExpenseShow', [
            'details'     => ExpenseShowResource::collection($details),
            'periodLabel' => $validated['label'],
            'targetName'  => $targetName,
            'targetType'  => ucfirst($validated['tab']),
            'totalSum'    => $details->sum('amount'),
            'filters'     => $filters,
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
                ->when(!empty($filters['franchise']), fn ($q) => $q->whereIn('franchise_id', $filters['franchise']));
        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when(!empty($filters['branch']), fn ($q) => $q->whereIn('branch_id', $filters['branch']));
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

    /**
     * Handles the export process.
     */
    public function exportIndex(Request $request)
    {
        // 1. Validate all inputs
        $validated = $request->validate([
            'tab' => ['required', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'array'], 
            'branch' => ['sometimes', 'nullable', 'array'],
            'period' => ['required', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'export' => ['required', 'string', Rule::in(['pdf', 'excel', 'csv'])],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'months' => ['required', 'array', 'min:1'],
            'months.*' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $filters = [
            'tab' => $validated['tab'],
            'franchise' => $validated['franchise'] ?? [],
            'branch' => $validated['branch'] ?? [],
            'period' => $validated['period'],
            'export' => $validated['export'],
        ];

        // 2. Build Query with date constraints
        $query = $this->buildBaseQuery($filters, $validated['year'], $validated['months']);

        // 3. Get and group data
        $expenses = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab']);

        // 4. Generate Title
        $title = $this->buildExportTitle($filters, $validated['year'], $validated['months']);
        $fileName = 'expense_' . now()->format('Y-m-d_His');

        // 5. EXPORT (Let ExpenseExport handle transformation)
        if ($filters['export'] === 'pdf') {
            return Pdf::loadView('exports.expense', [
                'rows' => $expenses,
                'title' => $title,
                'tab' => $filters['tab'],
                'source' => 'index',
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->download($fileName . '.pdf');
        }

        // Excel/CSV
        return (new ExpenseExport(
            $expenses,
            $title,
            $filters['tab'],
            'index'
        ))->download($fileName . '.' . ($filters['export'] === 'excel' ? 'xlsx' : 'csv'));
    }

    public function exportShow(Request $request)
    {
        $validated = $request->validate([
            'start'     => ['required', 'date'],
            'end'       => ['required', 'date'],
            'label'     => ['required', 'string'],
            'tab'       => ['required', 'string'],
            'franchise' => ['nullable'],
            'branch'    => ['nullable'],
            'export'     => ['required', 'string', Rule::in(['pdf', 'excel', 'csv'])],
        ]);

        // 1. Determine which ID we are filtering for
        $id = $validated['tab'] === 'franchise' ? $validated['franchise'] : $validated['branch'];
        
        // 2. Normalize filters for the buildBaseQuery
        $filters = [
            'tab'       => $validated['tab'],
            'franchise' => $validated['tab'] === 'franchise' ? [$id] : [],
            'branch'    => $validated['tab'] === 'branch' ? [$id] : [],
            'export'    => $validated['export'],
        ];

        // 3. Fetch specific Target Name for header
        $targetName = 'N/A';
        if ($validated['tab'] === 'franchise' && $id) {
            $targetName = Franchise::find($id)?->name;
        } elseif ($validated['tab'] === 'branch' && $id) {
            $targetName = Branch::find($id)?->name;
        }

        // 4. Build Query
        $query = $this->buildBaseQuery($filters);

        // Filter by the exact date range from the clicked row
        $query->whereBetween(DB::raw('DATE(payment_date)'), [
            $validated['start'], 
            $validated['end']
        ]);

        $details = $query->with([
                'maintenance.vehicle:id,plate_number', 
                'maintenance.inventory:id,name'])
            ->orderBy('payment_date', 'desc')
            ->get();

        // 4. Generate Title
        $title = $targetName . ' ' . 'Maintenance Expenses for ' . $validated['label'];
        $fileName = 'expenses_' . $targetName . '_' . now()->format('Y-m-d_His');

        // 5. EXPORT (Let ExpenseExport handle transformation)
        if ($filters['export'] === 'pdf') {
            return Pdf::loadView('exports.expense', [
                'rows' => $details,
                'title' => $title,
                'tab' => $filters['tab'],
                'source' => 'show'
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->download($fileName . '.pdf');
        }

        // Excel/CSV
        return (new ExpenseExport(
            $details,
            $title,
            $filters['tab'],
            'show'
        ))->download($fileName . '.' . ($filters['export'] === 'excel' ? 'xlsx' : 'csv'));
    }

    /**
     * Helper to build a descriptive title for the export.
     */
    private function buildExportTitle(array $filters, int $year, array $months): string
    {
        $period = ucfirst($filters['period']);
        $tabName = $filters['tab'] === 'franchise' ? 'Franchise' : 'Branch';

        // Get specific name if filtered
        $targetName = "All {$tabName}s";
        if (!empty($filters['franchise'])) {
            $names = Franchise::whereIn('id', $filters['franchise'])
                ->pluck('name')
                ->join(', ');
            $targetName = $names ?: 'Franchise';
        } elseif (!empty($filters['branch'])) {
            $names = Branch::whereIn('id', $filters['branch'])
                ->pluck('name')
                ->join(', ');
            $targetName = $names ?: 'Branch';
        }

        // Format months
        $monthNames = collect($months)->map(fn ($m) => date('F', mktime(0, 0, 0, $m, 1)))->join(', ');

        return "{$period} Expense for {$targetName} - {$monthNames} {$year}";
    }
}
