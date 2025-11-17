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
use App\Exports\RevenueExport;
use Maatwebsite\Excel\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
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
    private function buildBaseQuery(array $filters, ?int $year = null, ?array $months = null): Builder
    {
        $query = Revenue::query()
            // Base filters: "paid" status and non-null payment_date
            ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
            ->whereNotNull('payment_date')
            ->where('service_type', $filters['service']);

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

    /**
     * Handles the export process.
     */
    public function export(Request $request)
    {
        // 1. Validate all inputs (page filters + modal filters)
        $validated = $request->validate([
            'tab' => ['required', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['nullable', 'string'],
            'branch' => ['nullable', 'string'],
            'service' => ['required', 'string', Rule::in(['Trips', 'Boundary'])],
            'period' => ['required', 'string',Rule::in(['daily', 'weekly', 'monthly'])],
            'export_type' => ['required', 'string', Rule::in(['pdf', 'excel', 'csv'])],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'months' => ['required', 'array', 'min:1'],
            'months.*' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'service' => $validated['service'] ?? 'Trips',
            'period' => $validated['period'] ?? 'daily',
            'export_type' => $validated['export_type'] ?? 'pdf',
        ];

        // 2. Build Query with date constraints
        $query = $this->buildBaseQuery(
            $filters,
            $validated['year'],
            $validated['months']
        );

        // 3. Get and group data
        $revenues = $this->applyPeriodGrouping(
            $query,
            $filters['period'],
            $filters['tab']
        );

        // 4. Transform data for export
        $resourceCollection = RevenueDatatableResource::collection($revenues);
        $arrayData = collect($resourceCollection->toArray(request()));

        // Build rows for export using array items (safe)
        $exportRows = $arrayData->map(function ($r) use ($filters) {
            // pick name key (franchise_name / branch_name)
            $nameKey = $filters['tab'] . '_name';
            $name = $r[$nameKey] ?? 'N/A';

            // period is provided by the resource as 'payment_date' (for daily) or formatted month_name / week range as we returned
            $period = $r['payment_date'] ?? '';

            // amount might already be numeric or a formatted string; sanitize it to numeric
            $amount = $r['amount'] ?? 0;

            return [
                'name' => $name,
                'period' => $period,
                'amount' => $amount,
            ];
        })->values();

        // 5. Add Grand Total Row (sum of numeric amounts)
        $grandTotal = $exportRows->sum('amount');

        $exportRows = $exportRows->push([
            'name' => 'GRAND TOTAL',
            'period' => '',
            'amount' => (float) $grandTotal,
        ]);

        // Keep headings and title as before
        $headings = [$filters['tab'] === 'franchise' ? 'Franchise' : 'Branch', 'Date', 'Amount'];
        $title = $this->buildExportTitle($filters, $validated['year'], $validated['months']);
        $fileName = 'revenues_'.date('Y-m-d');

        // 7. Dispatch Download
        $export = new RevenueExport($exportRows, $headings, $title);
        $exportType = $filters['export_type'];

        if ($exportType === 'pdf') {
            return Pdf::loadView('exports.revenue', [
                'rows' => $exportRows,
                'title' => $title,
                'headings' => $headings
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->download($fileName.'.pdf');
        }
        if ($exportType === 'excel') {
            return $export->download($fileName.'.xlsx', Excel::XLSX);
        }
        if ($exportType === 'csv') {
            return $export->download($fileName.'.csv', Excel::CSV);
        }
    }

    /**
     * Helper to build a descriptive title for the export.
     */
    private function buildExportTitle(array $filters, int $year, array $months): string
    {
        $period = ucfirst($filters['period']);
        $service = $filters['service'];
        $tabName = $filters['tab'] === 'franchise' ? 'Franchise' : 'Branch';

        // Get specific name if filtered
        $targetName = "All {$tabName}s";
        if ($filters['franchise']) {
            $targetName = Franchise::find($filters['franchise'])->name ?? 'Franchise';
        } elseif ($filters['branch']) {
            $targetName = Branch::find($filters['branch'])->name ?? 'Branch';
        }

        // Format months
        $monthNames = collect($months)->map(fn ($m) => date('F', mktime(0, 0, 0, $m, 1)))->join(', ');

        return "{$period} {$service} Revenue for {$targetName} - {$monthNames} {$year}";
    }
}