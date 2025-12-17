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
            'franchise' => ['sometimes', 'nullable', 'array'], 
            'branch' => ['sometimes', 'nullable', 'array'],
            'driver' => ['sometimes', 'nullable', 'array'],
        ]);

        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? [],
            'branch' => $validated['branch'] ?? [],
            'driver' => $validated['driver'] ?? [],
        ];

        // 1. Fetch Map Data
        $mapRoutes = $this->getOnlineDriverLocations($filters);
        $driversList = $this->getContextualDrivers($filters);

        return Inertia::render('super-admin/fleet/GpsTracker', [
            'mapMarkers' => GpsTrackerResource::collection($mapRoutes),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'drivers' => fn () => $driversList,
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
                'user:id,username',
                'vehicles:id,driver_id,plate_number',  
            ]);

        // Filter by specific driver if selected
        $query->when(!empty($filters['driver']), function ($q) use ($filters) {
            $q->whereIn('id', $filters['driver']);
        });

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereHas('franchises', function ($q) use ($filters) {
                $q->when($filters['franchise'], fn ($subQ) =>
                    $subQ->whereIn('franchises.id', $filters['franchise'])
                );
            });

            // Eager load franchises to make name available in the resource
            $query->with('franchises:id,name');

        } elseif ($filters['tab'] === 'branch') {
            $query->whereHas('branches', function ($q) use ($filters) {
                $q->when($filters['branch'], fn ($subQ) =>
                    $subQ->whereIn('branches.id', $filters['branch'])
                );
            });

            // Eager load branches to make name available in the resource
            $query->with('branches:id,name');
        }

        return $query->get();
    }

    /**
     * Efficiently fetches drivers based on the current view context
     */
    private function getContextualDrivers(array $filters)
    {
        // Start with UserDriver and join the base User table to get username
        $query = UserDriver::query()
            ->join('users', 'user_drivers.id', '=', 'users.id')
            ->select('user_drivers.id', 'users.username');

        if ($filters['tab'] === 'franchise') {
            if (!empty($filters['franchise']) && $filters['franchise'] !== 'all') {
                // Get drivers strictly belonging to this franchise
                $query->whereHas('franchises', function ($q) use ($filters) {
                    $q->where('franchises.id', $filters['franchise']);
                });
            } else {
                // Get ALL drivers that belong to ANY franchise
                $query->has('franchises');
            }
        } elseif ($filters['tab'] === 'branch') {
            if (!empty($filters['branch']) && $filters['branch'] !== 'all') {
                // Get drivers strictly belonging to this branch
                $query->whereHas('branches', function ($q) use ($filters) {
                    $q->where('branches.id', $filters['branch']);
                });
            } else {
                // Get ALL drivers that belong to ANY branch
                $query->has('branches');
            }
        }

        return $query->orderBy('users.username')->get();
    }
}
