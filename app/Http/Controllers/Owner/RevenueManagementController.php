<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class RevenueManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('owner/revenue-management/Index');
    }
}
