<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
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
            'owner.user',
            'status'
        ])->get();

        return Inertia::render('super-admin/dashboard/Index', [
            'franchises' => $franchises,
        ]);
    }
}