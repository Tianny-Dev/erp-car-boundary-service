<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\GpsTrackerResource;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\UserDriver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GpsTrackerController extends Controller
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

        // 1. Fetch Map Data
        $mapRoutes = $this->getOnlineDriverLocations($filters);

        return Inertia::render('super-admin/fleet/GpsTracker', [
            'mapMarkers' => GpsTrackerResource::collection($mapRoutes),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => $filters,
        ]);
    }

    private function getOnlineDriverLocations(array $filters)
    {
        $query = UserDriver::query()
            ->whereHas('status', fn ($q) => $q->where('name', 'active'))
            ->whereHas('vehicles', fn ($q) => $q->whereHas('status', fn ($subQ) => $subQ->where('name', 'active')))
            ->where(function ($q) {
                $q->whereNotNull('longitude')
                ->whereNotNull('latitude');
            })
            ->with([
                'user:id,name',
                'vehicles:id,driver_id,plate_number',  
            ]);

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereHas('franchises', function ($q) use ($filters) {
                $q->when($filters['franchise'], fn ($subQ) =>
                    $subQ->where('franchises.id', $filters['franchise'])
                );
            });

            // Eager load franchises to make name available in the resource
            $query->with('franchises:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereHas('branches', function ($q) use ($filters) {
                $q->when($filters['branch'], fn ($subQ) =>
                    $subQ->where('branches.id', $filters['branch'])
                );
            });

            // Eager load branches to make name available in the resource
            $query->with('branches:id,name');
        }

        return $query->get();
    }
}
