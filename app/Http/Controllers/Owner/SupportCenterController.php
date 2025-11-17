<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SupportCenterController extends Controller
{
    public function index()
    {
        return Inertia::render('owner/support-center/Index');
    }
}
