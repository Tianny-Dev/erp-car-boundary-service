<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayOutController extends Controller
{
    public function index()
    {
        $franchise = $this->getFranchiseOrDefault();
        $franchiseId = $franchise?->id;

        return Inertia::render('owner/payout/Index', [
            'dailyEarnings' => $this->dailyEarnings($franchiseId),
        ]);
    }

    protected function getFranchiseOrDefault()
    {
        return auth()->user()->ownerDetails?->franchises()->first();
    }

    protected function dailyEarnings(?int $franchiseId): float
    {
        return $this->sumRevenueByDate($franchiseId, today());
    }

    protected function sumRevenueByDate(?int $franchiseId, $date): float
    {
        if (!$franchiseId) return 0.0;

        return Revenue::where('franchise_id', $franchiseId)
            ->whereHas('status', function ($query) {
                $query->where('name', 'paid');
            })
            ->whereDate('created_at', $date)
            ->sum('amount');
    }
}
