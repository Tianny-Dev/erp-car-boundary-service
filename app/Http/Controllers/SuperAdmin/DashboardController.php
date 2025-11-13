<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Branch;
use App\Http\Resources\SuperAdmin\FranchiseDatatableResource;
use App\Http\Resources\SuperAdmin\BranchDatatableResource;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(): Response
    {
        $franchises = Franchise::with([
            'owner.user:id,name',
            'status:id,name'
        ])->get();

        $branches = Branch::with([
            'manager.user:id,name',
            'status:id,name'
        ])->get();

        return Inertia::render('super-admin/dashboard/Index', [
            'franchises' => FranchiseDatatableResource::collection($franchises),
            'branches' => BranchDatatableResource::collection($branches),
        ]);
    }
}