<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Branch;
use App\Models\Revenue;
use App\Models\Expense;
use App\Models\UserManager;
use App\Http\Resources\SuperAdmin\FranchiseDatatableResource;
use App\Http\Resources\SuperAdmin\BranchDatatableResource;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(): Response
    {
        $today = Carbon::today();
        // Get today's revenues total
        $totalRevenue = Revenue::whereDate('payment_date', $today)
            ->sum('amount');
        // Get today's expenses total
        $totalExpenses = Expense::whereDate('payment_date', $today)
            ->sum('amount');
        // Get total active franchises
        $totalFranchises = Franchise::whereHas('status', function ($query) {
            $query->where('name', 'active');
        })->count();
        // Get total active branches
        $totalBranches = Branch::whereHas('status', function ($query) {
            $query->where('name', 'active');
        })->count();

        $franchises = Franchise::with([
            'owner.user:id,username',
            'status:id,name'
        ])->get();

        $branches = Branch::with([
            'manager.user:id,username',
            'status:id,name'
        ])->get();

        $pendingManagers = UserManager::with(['user:id,name'])
        ->whereHas('status', function ($query) {
            $query->where('name', 'pending');
        })
        ->get()
        ->map(function ($manager) {
            return [
                'id' => $manager->id,
                'name' => $manager->user->name,
            ];
        });

        return Inertia::render('super-admin/dashboard/Index', [
            'franchises' => FranchiseDatatableResource::collection($franchises),
            'branches' => BranchDatatableResource::collection($branches),
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'total_franchises' => $totalFranchises,
                'total_branches' => $totalBranches,
            ],
            'pendingManagers' => $pendingManagers
        ]);
    }
}