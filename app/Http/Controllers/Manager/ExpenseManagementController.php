<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseManagementController extends Controller
{
    public function index()
    {
        $branchId = $this->getbranchId();

        return Inertia::render('manager/expense-management/Index', [
            'expenseTypeBreakdownData' => $this->getExpenseBreakdownByExpenseType($branchId),
            'expenseByPaymentOption' => $this->getExpenseByPaymentOption($branchId),
            'expenses' => $this->getPaginatedExpense($branchId),
            'expenseTrendData' => $this->getExpenseTrendData($branchId),
        ]);
    }

    /**
     * Get the authenticated manager's branch ID or null.
     */
    protected function getbranchId(): ?int
    {
        return auth()->user()->managerDetails?->branches()->first()?->id;
    }

    /**
     * Get expense breakdown by expense type.
     */
    protected function getExpenseBreakdownByExpenseType(?int $branchId): array
    {
        return Expense::where('branch_id', $branchId)
            ->selectRaw('expense_type as name, SUM(amount) as total')
            ->groupBy('expense_type')
            ->get()
            ->toArray();
    }

    /**
     * Get expense breakdown by payment option.
     */
    protected function getExpenseByPaymentOption(?int $branchId): array
    {
        return Expense::where('branch_id', $branchId)
            ->join('payment_options', 'expenses.payment_option_id', '=', 'payment_options.id')
            ->selectRaw('payment_options.name, SUM(expenses.amount) as total')
            ->groupBy('payment_options.name')
            ->get()
            ->toArray();
    }

    /**
     * Get paginated expenses list with relationships.
     */
    protected function getPaginatedExpense(?int $branchId)
    {
        return Expense::with(['status', 'franchise', 'branch', 'paymentOption'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn($expense) => [
                'id' => $expense->id,
                'invoice_no' => $expense->invoice_no,
                'amount' => $expense->amount,
                'currency' => $expense->currency,
                'expense_type' => $expense->expense_type,
                'payment_date' => $expense->payment_date,
                'notes' => $expense->notes,
                'status' => $expense->status?->name,
                'franchise' => $expense->franchise?->name,
                'branch' => $expense->branch?->name,
                'payment_option' => $expense->paymentOption?->name,
            ]);
    }

    /**
     * Get yearly expense trend data.
     */
    protected function getExpenseTrendData(?int $branchId): array
    {
        return Expense::where('branch_id', $branchId)
            ->selectRaw('YEAR(payment_date) as year, SUM(amount) as expense')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(fn($item) => [
                'year' => (int) $item->year,
                'expense' => (float) $item->expense,
            ])
            ->toArray();
    }
}
