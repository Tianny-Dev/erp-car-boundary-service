<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Revenue;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $franchises = Franchise::all();

        return Inertia::render('super-admin/transaction/Index', [
            'franchises' => $franchises,
        ]);
    }

    public function show($id): Response
    {
        // Get the franchise info
        $franchise = Franchise::findOrFail($id);

        // Get all revenues where franchise_id = id
        $revenues = Revenue::where('franchise_id', $id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return Inertia::render('super-admin/transaction/Show', [
            'franchise' => $franchise,
            'revenues' => $revenues,
        ]);
    }
}
