<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BoundaryContractController extends Controller
{
    public function index()
    {
        return Inertia::render('owner/boundary-contracts/Index');
    }
}
