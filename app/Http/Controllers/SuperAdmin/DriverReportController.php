<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\DriverReportDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\DriverExport;
use Maatwebsite\Excel\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class DriverReportController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'driver' => ['sometimes', 'nullable', 'string'],
            'service' => ['sometimes', 'string', Rule::in(['Trips'])],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'driver' => $validated['driver'] ?? null,
            'service' => $validated['service'] ?? 'Trips',
            'period' => $validated['period'] ?? 'daily',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $revenues = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab']);
        $driversList = $this->getContextualDrivers($filters);

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/driver-report/Index', [
            'revenues' => DriverReportDatatableResource::collection($revenues),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'drivers' => fn () => $driversList,
            'filters' => $filters,
        ]);

    }

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

        // --- NEW: Apply Driver Filter ---
        // The driver ID is on the 'revenues' table itself.
        if (!empty($filters['driver']) && $filters['driver'] !== 'all') {
            $query->where('driver_id', $filters['driver']);
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
        // Fetch all breakdown types to dynamically generate SUM(CASE...) expressions
        $breakdownTypes = DB::table('percentage_types')->pluck('name');

        // Dynamically create select statements for breakdown sums
        // E.g., SUM(CASE WHEN percentage_types.name = 'tax' THEN revenue_breakdowns.total_earning ELSE 0 END) AS breakdown_tax
        $breakdownSelects = $breakdownTypes->map(function ($type) {
            return "SUM(CASE WHEN percentage_types.name = '{$type}' THEN revenue_breakdowns.total_earning ELSE 0 END) AS breakdown_{$type}";
        })->join(', ');

        // 1. Core Joins (needed for all grouped reports)
        $query->join('users', 'revenues.driver_id', '=', 'users.id')
              ->leftJoin('revenue_breakdowns', 'revenues.id', '=', 'revenue_breakdowns.revenue_id')
              ->leftJoin('percentage_types', 'revenue_breakdowns.percentage_type_id', '=', 'percentage_types.id');

        // 2. Base Grouping fields for all periods
        $groupingSelects = "
            SUM(DISTINCT revenues.amount) as total_amount,
            revenues.service_type,
            users.id as driver_id,
            users.name as driver_username
        ";

        // 3. Add Tab-specific Joins and Selects
        $groupingFields = ['users.id', 'users.name', 'revenues.service_type'];

        if ($tab === 'franchise') {
            $query->join('franchises', 'revenues.franchise_id', '=', 'franchises.id')
                ->addSelect('franchises.id as franchise_id', 'franchises.name as franchise_name');
            $groupingFields[] = 'franchises.id';
            $groupingFields[] = 'franchises.name';

        } elseif ($tab === 'branch') {
            $query->join('branches', 'revenues.branch_id', '=', 'branches.id')
                ->addSelect('branches.id as branch_id', 'branches.name as branch_name');
            $groupingFields[] = 'branches.id';
            $groupingFields[] = 'branches.name';
        }

        // --- Handle Daily Period (GROUPED QUERY) ---
        if ($period === 'daily') {
            $query->selectRaw($groupingSelects . ", DATE(revenues.payment_date) as daily_date_sort, " . $breakdownSelects);

            $query->groupBy(array_merge($groupingFields, [DB::raw('DATE(revenues.payment_date)'), 'revenues.service_type']))
                ->orderBy('daily_date_sort', 'desc');

            return $query->get();
        }

        // --- Handle Weekly/Monthly (Grouped Query with JOINs) ---

        // Apply period-specific grouping
        if ($period === 'weekly') {
            $query->selectRaw($groupingSelects . ", MIN(revenues.payment_date) as week_start, MAX(revenues.payment_date) as week_end, " . $breakdownSelects);

            $query->groupBy(array_merge($groupingFields, [DB::raw('YEAR(revenues.payment_date)'), DB::raw('WEEK(revenues.payment_date, 1)'), 'revenues.service_type']))
                ->orderBy('week_start', 'desc');
        }

        if ($period === 'monthly') {
            $query->selectRaw($groupingSelects . ",
                DATE_FORMAT(revenues.payment_date, '%M %Y') as month_name,
                YEAR(revenues.payment_date) as year_sort,
                MONTH(revenues.payment_date) as month_sort,
                " . $breakdownSelects);

            // FIX: Add the full DATE_FORMAT expression to the GROUP BY to satisfy ONLY_FULL_GROUP_BY mode.
            $query->groupBy(array_merge($groupingFields, [
                DB::raw('YEAR(revenues.payment_date)'),
                DB::raw('MONTH(revenues.payment_date)'),
                // Add the formatted date column reference here:
                DB::raw("DATE_FORMAT(revenues.payment_date, '%M %Y')"),
                'revenues.service_type'
            ]))
                ->orderBy('year_sort', 'desc')
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
            'driver' => ['nullable', 'string'],
            'service' => ['required', 'string', Rule::in(['Trips'])],
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
            'driver' => $validated['driver'] ?? null,
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

        // 3. Get and group data (Main driver report data, including per-row breakdown sums)
        $revenues = $this->applyPeriodGrouping(
            $query,
            $filters['period'],
            $filters['tab']
        );

        // --- CALCULATE GRAND TOTALS AND GET TYPES FROM GROUPED DATA ---
        // 3.1 Get the breakdown type names from the database once
        $breakdownTypes = DB::table('percentage_types')->pluck('name')->toArray();
        $breakdownKeys = array_map(fn ($type) => "breakdown_{$type}", $breakdownTypes);

        // 3.2 Calculate Grand Total for each breakdown type by summing the grouped results
        $breakdownTotals = collect($breakdownKeys)->mapWithKeys(function ($key) use ($revenues) {
            // Key will be 'breakdown_tax', 'breakdown_bank', etc.
            $originalName = str_replace('breakdown_', '', $key);
            // Sum the columns from all the grouped revenue rows
            $total = $revenues->sum($key);
            return [$originalName => (float) $total];
        });
        $breakdownTypes = $breakdownTotals->keys()->toArray(); // Re-use the clean type names
        // -------------------------------------------------------------

        // 4. Transform data for export
        $resourceCollection = DriverReportDatatableResource::collection($revenues);
        $arrayData = collect($resourceCollection->toArray(request()));

        // Build rows for export, including Driver ID and Driver Name
       $exportRows = $arrayData->map(function ($r) use ($filters, $breakdownTypes) {
        // pick name key (franchise_name / branch_name)
        $nameKey = $filters['tab'] . '_name';

        // --- New: Calculate Breakdown Total ---
        $totalBreakdowns = 0;

        // Base row structure
        $row = [
            'driver_username' => $r['driver_username'] ?? 'N/A',
            'tab_name' => $r[$nameKey] ?? 'N/A',
            'period' => $r['payment_date'] ?? '',
            'amount' => (float) ($r['amount'] ?? 0),
        ];

        // Append the breakdown totals from the resource row dynamically
        foreach ($breakdownTypes as $type) {
            $key = strtolower($type); // 'tax', 'bank'
            $breakdownValue = (float) ($r[$key] ?? 0);
            $row[$key] = $breakdownValue;
            $totalBreakdowns += $breakdownValue; // Sum up the breakdown values
        }

        // --- New: Calculate Driver Earning (Amount - Breakdowns) ---
        $driverEarning = max(0, $row['amount'] - $totalBreakdowns);
        $row['driver_earning'] = (float) $driverEarning;

        return $row;
    })->values();

        // 5. Add Grand Total Row (sum of main amounts + breakdown totals)
        $grandTotal = $exportRows->sum('amount');
        // --- New: Calculate Driver Earning Grand Total ---
        $grandTotalDriverEarning = $exportRows->sum('driver_earning');

        // Build the standard Grand Total Row
        $grandTotalRow = [
            'driver_username' => 'GRAND TOTAL', // Label for the row
            'tab_name' => '',
            'period' => '',
            'amount' => (float) $grandTotal, // Main amount total
        ];

        // Append the breakdown totals dynamically
        foreach ($breakdownTypes as $type) {
            // Use the breakdown type name (in lowercase) as the array key
            $key = strtolower($type);
            $grandTotalRow[$key] = (float) ($breakdownTotals[$type] ?? 0);
        }

        $grandTotalRow['driver_earning'] = (float) $grandTotalDriverEarning;

        $exportRows = $exportRows->push($grandTotalRow);

        // 6. Define Headings (Updated to include Driver and Breakdown columns)
        $headings = [
            'Driver Name',
            $filters['tab'] === 'franchise' ? 'Franchise' : 'Branch',
            'Date',
            'Amount',
        ];

        // Append breakdown headings (e.g., 'Tax', 'Bank', 'Markup Fee')
        $breakdownHeadings = array_map(fn ($type) => ucwords(str_replace('_', ' ', $type)), $breakdownTypes);

        // Append Breakdown Headings first
        $headings = array_merge($headings, $breakdownHeadings);

        // Append Driver Earning as the LAST heading
        $headings[] = 'Driver Earning'; // Moved to the end

        $title = $this->buildExportTitle($filters, $validated['year'], $validated['months']);
        $fileName = 'revenues_'.date('Y-m-d');

        // 7. Dispatch Download
        $export = new DriverExport($exportRows, $headings, $title);
        $exportType = $filters['export_type'];

        // Define the list of keys to ensure the Blade loop iterates over all columns correctly
        $dataKeys = [
            'driver_username', 'tab_name', 'period', 'amount'
        ];
        // Add the dynamically generated keys for the breakdown columns
        $dataKeys = array_merge($dataKeys, array_keys($breakdownTotals->toArray()));

        // Append driver_earning as the LAST data key
        $dataKeys[] = 'driver_earning';


        if ($exportType === 'pdf') {
            return Pdf::loadView('exports.driver', [
                'rows' => $exportRows,
                'title' => $title,
                'headings' => $headings,
                'dataKeys' => $dataKeys // Pass the dynamic keys to the view
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->download($fileName.'.pdf');
        }
        if ($exportType === 'excel') {
            // Note: The Excel export needs $exportRows and $headings which now contain the breakdown columns
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
        // NOTE: You need to ensure Franchise/Branch models are imported or fully qualified
        if ($filters['franchise']) {
            $targetName = Franchise::find($filters['franchise'])->name ?? 'Franchise';
        } elseif ($filters['branch']) {
            $targetName = Branch::find($filters['branch'])->name ?? 'Branch';
        }

        // Format months
        $monthNames = collect($months)->map(fn ($m) => date('F', mktime(0, 0, 0, $m, 1)))->join(', ');

        return "{$period} {$service} Revenue for Driver Report - {$targetName} - {$monthNames} {$year}";
    }
}
