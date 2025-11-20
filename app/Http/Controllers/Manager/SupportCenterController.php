<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportCenterController extends Controller
{
    public function index()
    {
        return Inertia::render('manager/support-center/Index');
    }
}
