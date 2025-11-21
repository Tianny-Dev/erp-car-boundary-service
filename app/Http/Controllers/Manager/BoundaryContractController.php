<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\BoundaryContract;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoundaryContractController extends Controller
{
    public function index()
    {
        $branch = auth()->user()->managerDetails?->branches()->first();
        $branchId = $branch?->id;

        $query = BoundaryContract::with(['status', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at');

        // Filter by status
        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }
        }

        // Global search
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('coverage_area', 'like', "%{$search}%")
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
                'branch' => $contract->branch?->name,
                'depositStatus' => $contract->deposit_status ?? 'pending',
            ]);

        return Inertia::render('manager/boundary-contracts/Index', [
            'contracts' => $contracts,
        ]);
    }
}
