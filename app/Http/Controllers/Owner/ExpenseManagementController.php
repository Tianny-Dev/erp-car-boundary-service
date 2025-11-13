<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Inertia\Inertia;

class ExpenseManagementController extends Controller
{
    public function index()
    {
        $franchiseId = $this->getFranchiseId();

        return Inertia::render('owner/expense-management/Index', [
            'expenseTypeBreakdownData' => $this->getExpenseBreakdownByExpenseType($franchiseId),
            'expenseByPaymentOption' => $this->getExpenseByPaymentOption($franchiseId),
            'expenses' => $this->getPaginatedExpense($franchiseId),
            'expenseTrendData' => $this->getExpenseTrendData($franchiseId),
        ]);
    }

    /**
     * Get the authenticated owner's franchise ID or null.
     */
    protected function getFranchiseId(): ?int
    {
        return auth()->user()->ownerDetails?->franchises()->first()?->id;
    }

    /**
     * Get expense breakdown by expense type.
     */
    protected function getExpenseBreakdownByExpenseType(?int $franchiseId): array
    {
        return Expense::where('franchise_id', $franchiseId)
            ->selectRaw('expense_type as name, SUM(amount) as total')
            ->groupBy('expense_type')
            ->get()
            ->toArray();
    }

    /**
     * Get expense breakdown by payment option.
     */
    protected function getExpenseByPaymentOption(?int $franchiseId): array
    {
        return Expense::where('franchise_id', $franchiseId)
            ->join('payment_options', 'expenses.payment_option_id', '=', 'payment_options.id')
            ->selectRaw('payment_options.name, SUM(expenses.amount) as total')
            ->groupBy('payment_options.name')
            ->get()
            ->toArray();
    }

    /**
     * Get paginated expenses list with relationships.
     */
    protected function getPaginatedExpense(?int $franchiseId)
    {
        return Expense::with(['status', 'franchise', 'branch', 'paymentOption'])
            ->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId))
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
    protected function getExpenseTrendData(?int $franchiseId): array
    {
        return Expense::where('franchise_id', $franchiseId)
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
