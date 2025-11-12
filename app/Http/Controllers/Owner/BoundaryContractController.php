<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BoundaryContract;
use Inertia\Inertia;

class BoundaryContractController extends Controller
{
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        $franchiseId = $franchise?->id;

        $contracts = BoundaryContract::with(['status', 'franchise', 'branch'])
            ->when($franchiseId, fn ($q) => $q->where('franchise_id', $franchiseId))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'name' => $contract->name,
                    'coverage_area' => $contract->coverage_area,
                    'contract_terms' => $contract->contract_terms,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'status' => $contract->status?->name,
                    'franchise' => $contract->franchise?->name,
                    'branch' => $contract->branch?->name,
                ];
            });

        return Inertia::render('owner/boundary-contracts/Index', [
            'contracts' => $contracts,
        ]);
    }
}
