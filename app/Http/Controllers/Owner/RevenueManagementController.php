<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use Inertia\Inertia;

class RevenueManagementController extends Controller
{
    public function index()
    {
        $franchiseId = $this->getFranchiseId();

        return Inertia::render('owner/revenue-management/Index', [
            'revenueServiceTypeBreakdownData' => $this->getRevenueBreakdownByServiceType($franchiseId),
            'revenueByPaymentOption' => $this->getRevenueByPaymentOption($franchiseId),
            'revenues' => $this->getPaginatedRevenues($franchiseId),
            'revenueTrendData' => $this->getRevenueTrendData($franchiseId),
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
     * Get revenue breakdown by service type.
     */
    protected function getRevenueBreakdownByServiceType(?int $franchiseId): array
    {
        return Revenue::where('franchise_id', $franchiseId)
            ->selectRaw('service_type as name, SUM(amount) as total')
            ->groupBy('service_type')
            ->get()
            ->toArray();
    }

    /**
     * Get revenue breakdown by payment option.
     */
    protected function getRevenueByPaymentOption(?int $franchiseId): array
    {
        return Revenue::where('franchise_id', $franchiseId)
            ->join('payment_options', 'revenues.payment_option_id', '=', 'payment_options.id')
            ->selectRaw('payment_options.name, SUM(revenues.amount) as total')
            ->groupBy('payment_options.name')
            ->get()
            ->toArray();
    }

    /**
     * Get paginated revenue list with relationships.
     */
    protected function getPaginatedRevenues(?int $franchiseId)
    {
        return Revenue::with(['status', 'franchise', 'branch', 'paymentOption'])
            ->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId))
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
    protected function getRevenueTrendData(?int $franchiseId): array
    {
        return Revenue::where('franchise_id', $franchiseId)
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
