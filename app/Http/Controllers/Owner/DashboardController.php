<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BoundaryContract;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\UserDriver;
use App\Models\Vehicle;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $franchise = $this->getFranchiseOrDefault();
        $franchiseId = $franchise?->id;

        $year = Carbon::now()->year;

        // Get daily revenue
        $revenues = Revenue::where('franchise_id', $franchiseId)
            ->whereYear('payment_date', $year)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as Revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get daily expenses
        $expenses = Expense::where('franchise_id', $franchiseId)
            ->whereYear('payment_date', $year)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as Expenses')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Merge dates and create array
        $dates = $revenues->pluck('date')->merge($expenses->pluck('date'))->unique()->sort();
        $revenueExpensesData = $dates->map(function ($date) use ($revenues, $expenses) {
            return [
                'date' => $date,
                'Revenue' => $revenues->firstWhere('date', $date)?->Revenue ?? 0,
                'Expenses' => $expenses->firstWhere('date', $date)?->Expenses ?? 0,
            ];
        })->values()->toArray();

        $years = range($year - 7, $year);
        $netProfitData = collect($years)->map(function ($y) use ($franchiseId) {
            $yearRevenue = Revenue::where('franchise_id', $franchiseId)
                ->whereYear('payment_date', $y)
                ->sum('amount');
            $yearExpense = Expense::where('franchise_id', $franchiseId)
                ->whereYear('payment_date', $y)
                ->sum('amount');

            return [
                'year' => $y,
                'Growth Rate' => $yearRevenue - $yearExpense,
            ];
        })->toArray();

        $pendingBoundaryDueCount = BoundaryContract::where('franchise_id', $franchiseId)
            ->whereHas('status', fn($q) => $q->where('name', 'pending'))
            ->count();

        return Inertia::render('owner/dashboard/Index', [
            'activeVehicles' => $this->countVehicles($franchiseId, 1),
            'pendingVehicles' => $this->countVehicles($franchiseId, 6),
            'activeDrivers' => $this->countDrivers($franchiseId, 1),
            'pendingDrivers' => $this->countDrivers($franchiseId, 6),
            'dailyEarnings' => $this->dailyEarnings($franchiseId),
            'pendingBoundaryDueCount' => $pendingBoundaryDueCount,
            'yesterdayEarnings' => $this->yesterdayEarnings($franchiseId),
            'vehiclesUnderMaintenance' => $this->countVehicles($franchiseId, 5),
            'franchiseExists' => (bool) $franchise,
            'revenueExpensesData' => $revenueExpensesData,
            'netProfitData' => $netProfitData,
        ]);
    }

    /**
     * Return the franchise if exists, otherwise null.
     */
    protected function getFranchiseOrDefault()
    {
        return auth()->user()->ownerDetails?->franchises()->first();
    }

    /**
     * Count vehicles by franchise and status.
     */
    protected function countVehicles(?int $franchiseId, int $statusId): int
    {
        if (!$franchiseId) return 0;

        return Vehicle::where('franchise_id', $franchiseId)
            ->where('status_id', $statusId)
            ->count();
    }

    /**
     * Count drivers by franchise and status.
     */
    protected function countDrivers(?int $franchiseId, int $statusId): int
    {
        if (!$franchiseId) return 0;

        return UserDriver::whereHas('franchises', fn($q) => $q->where('franchise_id', $franchiseId))
            ->where('status_id', $statusId)
            ->count();
    }

    /**
     * Calculate daily earnings for a franchise.
     */
    protected function dailyEarnings(?int $franchiseId): float
    {
        if (!$franchiseId) return 0.0;

        return Revenue::where('franchise_id', $franchiseId)
            ->whereDate('created_at', today())
            ->sum('amount') ?? 0.0;
    }

    /**
     * Calculate revenue from yesterday.
     */
    protected function yesterdayEarnings(?int $franchiseId): float
    {
        if (!$franchiseId) return 0.0;

        return Revenue::where('franchise_id', $franchiseId)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('amount') ?? 0.0;
    }
}
