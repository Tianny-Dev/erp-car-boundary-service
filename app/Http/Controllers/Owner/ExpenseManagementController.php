<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ExpenseManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('owner/expense-management/Index');
    }
}
