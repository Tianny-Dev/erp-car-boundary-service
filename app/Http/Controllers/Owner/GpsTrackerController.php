<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\GpsTrackerResource;
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
        $user = $request->user()->loadMissing('ownerDetails.franchises:id,name,owner_id');
        $franchiseId = $user->ownerDetails?->franchises->first()?->id;

        $validated = $request->validate([
            'driver' => ['sometimes', 'nullable', 'array'],
        ]);

        $filters = [
            'driver' => $validated['driver'] ?? [],
            'franchise_id' => $franchiseId,
        ];

        // 1. Fetch Map Data
        $mapRoutes = $this->getOnlineDriverLocations($filters);
        $driversList = $this->getContextualDrivers($filters);

        return Inertia::render('owner/gps/GpsTracker', [
            'mapMarkers' => GpsTrackerResource::collection($mapRoutes),
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
                'franchises:id,name',
            ])

        ->whereHas('franchises', function ($q) use ($filters) {
            $q->where('franchises.id', $filters['franchise_id']);
        });

        // Filter by specific driver if selected
        $query->when(!empty($filters['driver']), function ($q) use ($filters) {
            $q->whereIn('id', $filters['driver']);
        });

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
            ->select('user_drivers.id', 'users.username')
            ->whereHas('franchises', function ($q) use ($filters) {
                $q->where('franchises.id', $filters['franchise_id']);
            })
            ->orderBy('users.username')
            ->get();

        return $query;
    }
}
