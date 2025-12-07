<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\RouteMapResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RouteController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
        ];

        // 1. Fetch Map Data (Latest successful trip per driver today)
        $mapRoutes = $this->getLatestDriverRoutes($filters);

        return Inertia::render('super-admin/fleet/RouteIndex', [
            'mapMarkers' => RouteMapResource::collection($mapRoutes),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => $filters,
        ]);
    }

    private function getLatestDriverRoutes(array $filters)
    {
        // Subquery: Find the MAX(id) for every driver for TODAY where trip ended
        $latestIds = Route::selectRaw('MAX(id)')
            ->whereDate('end_trip', now()) // Filter for Today
            ->whereHas('status', fn ($q) => $q->where('name', 'end_trip'))
            ->groupBy('driver_id');

        // Main Query: Fetch details for those specific IDs
        $query = Route::whereIn('id', $latestIds)
            ->with([
                'driver:id',
                'driver.user:id,name',
                'vehicle:id,plate_number',
                'revenue:id,franchise_id,branch_id,payment_date'
            ]);

        // Apply Franchise/Branch filters to the main query to ensure data security
        if ($filters['tab'] === 'franchise') {
            $query->whereHas('revenue.franchise', function ($q) use ($filters) {
                $q->when($filters['franchise'], fn($sub) => $sub->where('id', $filters['franchise']));
            });
        } elseif ($filters['tab'] === 'branch') {
            $query->whereHas('revenue.branch', function ($q) use ($filters) {
                $q->when($filters['branch'], fn($sub) => $sub->where('id', $filters['branch']));
            });
        }

        return $query->get();
    }
}