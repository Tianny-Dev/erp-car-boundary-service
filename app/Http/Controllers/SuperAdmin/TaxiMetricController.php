<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TaxiMetric;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaxiMetricController extends Controller
{
    public function index(): Response
    {
        $taxiMetric = TaxiMetric::first();

        return Inertia::render('super-admin/finance/TaxiMetricIndex', [
            'taxiMetric' => $taxiMetric
        ]);
    }

    public function update(Request $request, TaxiMetric $taxiMetric)
    {
        $validated = $request->validate([
            'flag' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'per_minute' => [
                'required',
                'numeric',
                'min:0',
                'max:50',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'per_km' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
        ]);

        $taxiMetric->update($validated);
        return back();
    }
}
