<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PercentageController extends Controller
{
    public function index(): Response
    {
        $franchises = Franchise::all();

        return Inertia::render('super-admin/revenues-history/Index', [
            'franchises' => $franchises,
        ]);
    }
}
