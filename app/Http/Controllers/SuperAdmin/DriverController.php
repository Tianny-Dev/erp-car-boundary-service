<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(): Response
    {
        

        return Inertia::render('super-admin/fleet/DriverManagement',);
    }
}
