<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserDriver;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(): Response
    {
        // Fetch UserDrivers who have a status of 'pending'
        // We eager-load the 'user' relationship to get name/email
        $pendingDrivers = UserDriver::with('user')
            ->whereHas('status', function ($query) {
                $query->where('name', 'pending');
            })
            ->get();

        return Inertia::render('super-admin/dashboard/Index', [
            'pendingDrivers' => $pendingDrivers,
        ]);
    }
}