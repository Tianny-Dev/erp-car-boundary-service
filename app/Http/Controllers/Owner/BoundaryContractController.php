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

        $query = BoundaryContract::with(['status', 'franchise', 'branch'])
            ->when($franchiseId, fn ($q) => $q->where('franchise_id', $franchiseId))
            ->orderByDesc('created_at');

        // Filter by status
        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }
        }

        // Filter by deposit status
        // if ($depositStatus = request('depositStatus')) {
        //     if ($depositStatus !== 'all') {
        //         $query->where('deposit_status', $depositStatus);
        //     }
        // }

        // Global search
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('coverage_area', 'like', "%{$search}%")
                ->orWhereHas('franchise', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('branch', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $contracts = $query->paginate(10)
            ->through(fn ($contract) => [
                'id' => $contract->id,
                'name' => $contract->name,
                'coverage_area' => $contract->coverage_area,
                'contract_terms' => $contract->contract_terms,
                'start_date' => $contract->start_date,
                'end_date' => $contract->end_date,
                'status' => $contract->status?->name,
                'franchise' => $contract->franchise?->name,
                'branch' => $contract->branch?->name,
                'depositStatus' => $contract->deposit_status ?? 'pending',
            ]);

        return Inertia::render('owner/boundary-contracts/Index', [
            'contracts' => $contracts,
        ]);
    }
}
