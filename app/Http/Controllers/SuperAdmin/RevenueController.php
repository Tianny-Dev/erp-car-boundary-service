<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\RevenueDatatableResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RevenueController extends Controller
{
public function index(Request $request): Response
    {
        // 1. Validate tab, default to 'franchise'
        $tab = $request->input('tab', 'franchise');
        if (! in_array($tab, ['franchise', 'branch'])) {
            $tab = 'franchise';
        }

        // 2. Start base query
        $query = Revenue::select('id', 'franchise_id', 'branch_id', 'invoice_no', 'service_type', 'amount', 'payment_date');

        // 3. Apply conditional filtering based on tab
        if ($tab === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($request->input('franchise'), fn($q) => $q->where('franchise_id', $request->input('franchise')))
                ->with('franchise:id,name');

        } elseif ($tab === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($request->input('branch'), fn($q) => $q->where('branch_id', $request->input('branch')))
                ->with('branch:id,name');
        }

        $revenues = $query->get();

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/finance/RevenueIndex', [
            'revenues' => RevenueDatatableResource::collection($revenues),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => [
                'tab' => $tab,
                'franchise' => $request->input('franchise'),
                'branch' => $request->input('branch'),
            ],
        ]);
    }
}
