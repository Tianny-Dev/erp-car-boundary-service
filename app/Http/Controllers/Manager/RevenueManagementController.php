<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevenueManagementController extends Controller
{
    public function index()
    {
        $branchId = $this->getbranchId();

        return Inertia::render('manager/revenue-management/Index', [
            'revenueServiceTypeBreakdownData' => $this->getRevenueBreakdownByServiceType($branchId),
            'revenueByPaymentOption' => $this->getRevenueByPaymentOption($branchId),
            'revenues' => $this->getPaginatedRevenues($branchId),
            'revenueTrendData' => $this->getRevenueTrendData($branchId),
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
     * Get revenue breakdown by service type.
     */
    protected function getRevenueBreakdownByServiceType(?int $branchId): array
    {
        return Revenue::where('branch_id', $branchId)
            ->selectRaw('service_type as name, SUM(amount) as total')
            ->groupBy('service_type')
            ->get()
            ->toArray();
    }

    /**
     * Get revenue breakdown by payment option.
     */
    protected function getRevenueByPaymentOption(?int $branchId): array
    {
        return Revenue::where('branch_id', $branchId)
            ->join('payment_options', 'revenues.payment_option_id', '=', 'payment_options.id')
            ->selectRaw('payment_options.name, SUM(revenues.amount) as total')
            ->groupBy('payment_options.name')
            ->get()
            ->toArray();
    }

    /**
     * Get paginated revenue list with relationships.
     */
    protected function getPaginatedRevenues(?int $branchId)
    {
        return Revenue::with(['status', 'franchise', 'branch', 'paymentOption'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn($revenue) => [
                'id' => $revenue->id,
                'invoice_no' => $revenue->invoice_no,
                'amount' => $revenue->amount,
                'currency' => $revenue->currency,
                'service_type' => $revenue->service_type,
                'payment_date' => $revenue->payment_date,
                'notes' => $revenue->notes,
                'status' => $revenue->status?->name,
                'franchise' => $revenue->franchise?->name,
                'branch' => $revenue->branch?->name,
                'payment_option' => $revenue->paymentOption?->name,
            ]);
    }

    /**
     * Get yearly revenue trend data.
     */
    protected function getRevenueTrendData(?int $branchId): array
    {
        return Revenue::where('branch_id', $branchId)
            ->selectRaw('YEAR(payment_date) as year, SUM(amount) as revenue')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(fn($item) => [
                'year' => (int) $item->year,
                'revenue' => (float) $item->revenue,
            ])
            ->toArray();
    }
}
