<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ExpenseManagementController extends Controller
{
    public function index(Request $request)
    {
        $franchiseId = $this->getFranchiseId();
        $timePeriod = $request->input('timePeriod', 'all');
        $search = $request->input('search');

        return Inertia::render('owner/expense-management/Index', [
            'expenses' => $this->getPaginatedExpense($franchiseId, $timePeriod, $search),
            'expenseTrendData' => $this->getExpenseTrendData($franchiseId),
        ]);
    }

    protected function getFranchiseId(): ?int
    {
        return auth()->user()->ownerDetails?->franchises()->first()?->id;
    }

    protected function getPaginatedExpense(?int $franchiseId, string $timePeriod = 'all', ?string $search = null)
    {
        $perPage = request('per_page', 10);

        $query = Expense::query()->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId));

        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // --- Daily View ---
        if ($timePeriod === 'daily') {
            return $query->selectRaw("DATE(created_at) as date_label, SUM(amount) as total")
                ->groupBy('date_label')
                ->orderByDesc('date_label')
                ->paginate($perPage)
                ->through(fn($row) => [
                    'display_date' => Carbon::parse($row->date_label)->format('F j, Y'),
                    'total' => (float) $row->total
                ]);
        }

        // --- Weekly View ---
        if ($timePeriod === 'weekly') {
            return $query->selectRaw("YEARWEEK(created_at, 1) as year_week, MIN(DATE(created_at)) as week_start, MAX(DATE(created_at)) as week_end, SUM(amount) as total")
                ->groupBy('year_week')
                ->orderByDesc('week_start')
                ->paginate($perPage)
                ->through(fn($row) => [
                    'display_date' => Carbon::parse($row->week_start)->format('M j') . ' - ' . Carbon::parse($row->week_end)->format('M j, Y'),
                    'total' => (float) $row->total
                ]);
        }

        // --- Monthly View ---
        if ($timePeriod === 'monthly') {
            return $query->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_sort, DATE_FORMAT(created_at, '%M %Y') as month_name, SUM(amount) as total")
                ->groupBy('month_sort', 'month_name')
                ->orderByDesc('month_sort')
                ->paginate($perPage)
                ->through(fn($row) => [
                    'display_date' => $row->month_name,
                    'total' => (float) $row->total
                ]);
        }

        // --- Yearly View ---
        if ($timePeriod === 'yearly') {
            return $query->selectRaw("YEAR(created_at) as year_label, SUM(amount) as total")
                ->groupBy('year_label')
                ->orderByDesc('year_label')
                ->paginate($perPage)
                ->through(fn($row) => [
                    'display_date' => (string) $row->year_label,
                    'total' => (float) $row->total
                ]);
        }

        // --- All Time (Detailed View) ---
        return $query->with(['franchise'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn($expense) => [
                'id' => $expense->id,
                'invoice_no' => $expense->invoice_no,
                'amount' => (float) $expense->amount,
                'currency' => $expense->currency ?? '₱',
                'notes' => $expense->notes ?? '—',
                'franchise' => $expense->franchise?->name ?? '—',
                'created_at' => $expense->created_at->format('F j, Y'), // Format: March 21, 2026
            ]);
    }

    protected function getExpenseTrendData(?int $franchiseId): array
    {
        return Expense::where('franchise_id', $franchiseId)
            ->selectRaw('YEAR(created_at) as year, SUM(amount) as expense')
            ->groupBy('year')->orderBy('year')->get()
            ->map(fn($item) => ['year' => (int) $item->year, 'expense' => (float) $item->expense])
            ->toArray();
    }
}
