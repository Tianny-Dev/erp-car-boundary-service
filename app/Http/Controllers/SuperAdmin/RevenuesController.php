<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RevenueExport;
use Barryvdh\DomPDF\Facade\Pdf;

class RevenuesController extends Controller
{
    public function index(): Response
    {
        $franchises = Franchise::all();

        return Inertia::render('super-admin/revenues-history/Index', [
            'franchises' => $franchises,
        ]);
    }

    // Table Show Table Show.vue Start
    public function show(Request $request, $id): Response
    {
        $period = $request->query('period', 'daily');
        $serviceType = $request->query('service_type', 'All');

        $franchise = Franchise::findOrFail($id);
        $query = Revenue::where('franchise_id', $id);

        if ($serviceType && $serviceType !== 'All') {
            $query->where('service_type', $serviceType);
        }

        switch ($period) {
            case 'daily':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("DATE(payment_date) as date"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('date', 'service_type')
                ->orderBy('date', 'asc')
                ->get()
                ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F j, Y', strtotime($rev->date))));
                break;

            case 'weekly':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("YEAR(payment_date) as year"),
                    DB::raw("WEEK(payment_date, 1) as week"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('year', 'week', 'service_type')
                ->orderBy('year')
                ->orderBy('week')
                ->get()
                ->map(function ($rev) {
                    $dto = new \DateTime();
                    $dto->setISODate($rev->year, $rev->week);
                    $startOfWeek = $dto->format('Y-m-d');
                    $endOfWeek = $dto->modify('+6 days')->format('Y-m-d');
                    $rev->start_date = $startOfWeek;
                    $rev->end_date = $endOfWeek;
                    $rev->formatted_date = date('F j', strtotime($startOfWeek)) . ' – ' . date('F j, Y', strtotime($endOfWeek));
                    return $rev;
                });
                break;

            case 'monthly':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('month', 'service_type')
                ->orderBy('month', 'asc')
                ->get()
                ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F Y', strtotime($rev->month . '-01'))));
                break;

            default:
                $revenues = collect([]);
        }

        return Inertia::render('super-admin/revenues-history/Show', [
            'franchise' => $franchise,
            'revenues' => $revenues,
            'period' => $period,
        ]);
    }
    // Table Show Table Show.vue Start

    // Exporting PDF, EXCEL, CSV Show.vue Start
    public function export(Request $request, $id, $format)
    {
        $franchise = Franchise::findOrFail($id);
        $period = $request->query('period', 'daily');
        $serviceType = $request->query('service_type', 'Trips'); // default Trips
        $dates = $request->query('dates');

        $query = Revenue::where('franchise_id', $id);

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        // Filter by selected dates
        if ($dates) {
            $datesArray = explode(',', $dates);
            $query->where(function ($q) use ($datesArray, $period) {
                foreach ($datesArray as $d) {
                    if ($period === 'daily') {
                        $q->orWhereDate('payment_date', $d);
                    } elseif ($period === 'weekly') {
                        [$start, $end] = explode('|', $d);
                        $q->orWhereBetween('payment_date', [$start, $end]);
                    } elseif ($period === 'monthly') {
                        $q->orWhere(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"), $d);
                    }
                }
            });
        }

        // --- GROUP BY PERIOD ---
        if ($period === 'daily') {
            $revenues = $query->select(
                'service_type',
                DB::raw("DATE(payment_date) as date"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('date', 'service_type')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F j, Y', strtotime($rev->date))));

        } elseif ($period === 'weekly') {
            $revenues = $query->select(
                'service_type',
                DB::raw("YEAR(payment_date) as year"),
                DB::raw("WEEK(payment_date, 1) as week"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('year', 'week', 'service_type')
            ->orderBy('year')
            ->orderBy('week')
            ->get()
            ->map(function ($rev) {
                $dto = new \DateTime();
                $dto->setISODate($rev->year, $rev->week);
                $startOfWeek = $dto->format('Y-m-d');
                $endOfWeek = $dto->modify('+6 days')->format('Y-m-d');
                $rev->start_date = $startOfWeek;
                $rev->end_date = $endOfWeek;
                $rev->formatted_date = date('F j', strtotime($startOfWeek)) . ' – ' . date('F j, Y', strtotime($endOfWeek));
                return $rev;
            });

        } elseif ($period === 'monthly') {
            $revenues = $query->select(
                'service_type',
                DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('month', 'service_type')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F Y', strtotime($rev->month . '-01'))));

        } else {
            $revenues = collect([]);
        }

        // --- GRAND TOTAL ---
        $grandTotal = $revenues->sum('total');
        $revenues->push((object)[
            'formatted_date' => 'Grand Total',
            'total' => $grandTotal
        ]);

        // Get the FIRST row
        $first = $revenues->firstWhere('formatted_date', '!=', 'Grand Total');

        // Get the LAST row before Grand Total
        $last  = $revenues->slice(0, count($revenues)-1)->last();

        $rangeStart = $first? $first->formatted_date : '';
        $rangeEnd   = $last?  $last->formatted_date : '';

        // Correct single-date logic
        if ($rangeStart && $rangeEnd) {
            if ($rangeStart === $rangeEnd) {
                // Only one date selected
                $dateRange = $rangeStart;
            } else {
                // Real range
                $dateRange = "$rangeStart to $rangeEnd";
            }
        } else {
            $dateRange = "No Date Range";
        }

        $pdfTitle =
            "Revenues for {$franchise->name} ({$serviceType} – {$dateRange})";

        switch ($format) {
            case 'pdf':
                $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
                <title>Revenues</title>
                <style>
                    body { font-family: DejaVu Sans, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                    th { background-color: #eee; }
                    h1 { font-size: 22px; text-align: center; margin-bottom: 10px; }
                    .total-row td { font-weight: bold; background-color: #f2f2f2; }
                </style>
                </head><body>';

                // Final Title
                $html .= '<h1>' . $pdfTitle . '</h1>';

                // Table
                $html .= '<table border="1"><tr><th>No</th><th>Date</th><th>Total Amount</th></tr>';

                $i = 1;
                foreach ($revenues as $rev) {
                    $isTotal = $rev->formatted_date === 'Grand Total';
                    $no = $isTotal ? '' : $i;
                    $html .= '<tr'.($isTotal ? ' class="total-row"' : '').'>
                        <td>'.$no.'</td>
                        <td>'.$rev->formatted_date.'</td>
                        <td>₱'.number_format($rev->total, 2).'</td>
                    </tr>';
                    if (!$isTotal) $i++;
                }

                $html .= '</table></body></html>';

                return Pdf::loadHTML($html)->download($franchise->name.'_revenues.pdf');

            case 'excel':
            case 'csv':
                $filename = $format === 'excel'
                    ? $franchise->name.'_revenues.xlsx'
                    : $franchise->name.'_revenues.csv';
                $writerType = $format === 'excel'
                    ? \Maatwebsite\Excel\Excel::XLSX
                    : \Maatwebsite\Excel\Excel::CSV;

                return Excel::download(
                    new RevenueExport($revenues, $franchise->name, $serviceType),
                    $filename,
                    $writerType
                );

            default:
                abort(404);
        }
    }
    // Exporting PDF, EXCEL, CSV Show.vue End

    // Show All Table ins ShhowAll.vue Start
    public function showAll(Request $request): Response
    {
        $period = $request->query('period', 'daily');
            $serviceType = $request->query('service_type', 'All');

            $query = Revenue::query();

            // Filter by service type
            if ($serviceType && $serviceType !== 'All') {
                $query->where('service_type', $serviceType);
            }

        switch ($period) {
            case 'daily':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("DATE(payment_date) as date"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('date', 'service_type')
                ->orderBy('date', 'asc')
                ->get()
                ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F j, Y', strtotime($rev->date))));
                break;

            case 'weekly':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("YEAR(payment_date) as year"),
                    DB::raw("WEEK(payment_date, 1) as week"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('year', 'week', 'service_type')
                ->orderBy('year')
                ->orderBy('week')
                ->get()
                ->map(function ($rev) {
                    $dto = new \DateTime();
                    $dto->setISODate($rev->year, $rev->week);
                    $startOfWeek = $dto->format('Y-m-d');
                    $endOfWeek = $dto->modify('+6 days')->format('Y-m-d');
                    $rev->start_date = $startOfWeek;
                    $rev->end_date = $endOfWeek;
                    $rev->formatted_date = date('F j', strtotime($startOfWeek)) . ' – ' . date('F j, Y', strtotime($endOfWeek));
                    return $rev;
                });
                break;

            case 'monthly':
                $revenues = $query->select(
                    'service_type',
                    DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                    DB::raw("SUM(amount) as total")
                )
                ->groupBy('month', 'service_type')
                ->orderBy('month', 'asc')
                ->get()
                ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F Y', strtotime($rev->month . '-01'))));
                break;

            default:
                $revenues = collect([]);
        }

        return Inertia::render('super-admin/revenues-history/ShowAll', [
            'revenues' => $revenues,
            'period' => $period,
        ]);
    }
    // Show All Table ins ShhowAll.vue End

    // Exporting PDF, EXCEL, CSV ShowAll.vue Start
    public function exportAll(Request $request, $format)
    {
        $period = $request->query('period', 'daily');
        $dates = $request->query('dates');
        $serviceType = $request->query('service_type', 'Trips');
        $title = $serviceType;

        $query = Revenue::query();

        // Filter by service type
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        // Filter by selected dates
        if ($dates) {
            $datesArray = explode(',', $dates);
            $query->where(function ($q) use ($datesArray, $period) {
                foreach ($datesArray as $d) {
                    if ($period === 'daily') {
                        $q->orWhereDate('payment_date', $d);
                    } elseif ($period === 'weekly') {
                        [$start, $end] = explode('|', $d);
                        $q->orWhereBetween('payment_date', [$start, $end]);
                    } elseif ($period === 'monthly') {
                        $q->orWhere(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"), $d);
                    }
                }
            });
        }

        // Grouping logic (same as showAll)
        if ($period === 'daily') {
            $revenues = $query->select(
                'service_type',
                DB::raw("DATE(payment_date) as date"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('date', 'service_type')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F j, Y', strtotime($rev->date))));
        } elseif ($period === 'weekly') {
            $revenues = $query->select(
                'service_type',
                DB::raw("YEAR(payment_date) as year"),
                DB::raw("WEEK(payment_date, 1) as week"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('year', 'week', 'service_type')
            ->orderBy('year')
            ->orderBy('week')
            ->get()
            ->map(function ($rev) {
                $dto = new \DateTime();
                $dto->setISODate($rev->year, $rev->week);
                $startOfWeek = $dto->format('Y-m-d');
                $endOfWeek = $dto->modify('+6 days')->format('Y-m-d');
                $rev->start_date = $startOfWeek;
                $rev->end_date = $endOfWeek;
                $rev->formatted_date = date('F j', strtotime($startOfWeek)) . ' – ' . date('F j, Y', strtotime($endOfWeek));
                return $rev;
            });
        } elseif ($period === 'monthly') {
            $revenues = $query->select(
                'service_type',
                DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                DB::raw("SUM(amount) as total")
            )
            ->groupBy('month', 'service_type')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($rev) => tap($rev, fn($r) => $r->formatted_date = date('F Y', strtotime($rev->month . '-01'))));
        } else {
            $revenues = collect([]);
        }

        // Grand total
        $grandTotal = $revenues->sum('total');
        $revenues->push((object)[
            'formatted_date' => 'Grand Total',
            'total' => $grandTotal
        ]);

        $first = $revenues->firstWhere('formatted_date', '!=', 'Grand Total');

        // Get the LAST row before Grand Total
        $last  = $revenues->slice(0, count($revenues)-1)->last();

        $rangeStart = $first? $first->formatted_date : '';
        $rangeEnd   = $last?  $last->formatted_date : '';

        // Correct single-date logic
        if ($rangeStart && $rangeEnd) {
            if ($rangeStart === $rangeEnd) {
                // Only one date selected
                $dateRange = $rangeStart;
            } else {
                // Real range
                $dateRange = "$rangeStart to $rangeEnd";
            }
        } else {
            $dateRange = "No Date Range";
        }

        $pdfTitle = "Revenues for All Franchises ({$serviceType} – {$dateRange})";

        // Export
        switch ($format) {
            case 'pdf':
                $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Revenues</title><style>
                    body { font-family: DejaVu Sans, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                    th { background-color: #eee; }
                    h1 { font-size: 22px; text-align: center; }
                    .total-row td { font-weight: bold; background-color: #f2f2f2; }
                </style></head><body>';

                // PDF Title
                $html .= '<h1> ' . $pdfTitle . '</h1>';

                $html .= '<table border="1"><tr><th>No</th><th>Date/Period</th><th>Total Amount</th></tr>';
                $i = 1;
                foreach ($revenues as $rev) {
                    $isTotal = $rev->formatted_date === 'Grand Total';
                    $no = $isTotal ? '' : $i;
                    $html .= '<tr'.($isTotal ? ' class="total-row"' : '').'>
                        <td>'.$no.'</td>
                        <td>'.$rev->formatted_date.'</td>
                        <td>₱'.number_format($rev->total, 2).'</td>
                    </tr>';
                    if (!$isTotal) $i++;
                }
                $html .= '</table></body></html>';

                return Pdf::loadHTML($html)->download('All_Franchises_'.$title.'_revenues.pdf');

            case 'excel':
            case 'csv':
                $filename = $format === 'excel'
                    ? 'All_Franchises_'.$title.'_revenues.xlsx'
                    : 'All_Franchises_'.$title.'_revenues.csv';
                $writerType = $format === 'excel'
                    ? \Maatwebsite\Excel\Excel::XLSX
                    : \Maatwebsite\Excel\Excel::CSV;
                return Excel::download(new RevenueExport($revenues, 'All Franchises', $title), $filename, $writerType);

            default:
                abort(404);
        }
    }
    // Exporting PDF, EXCEL, CSV ShowAll.vue End

}
