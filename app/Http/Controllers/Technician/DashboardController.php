<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $franchise = $this->getFranchiseOrDefault();
        $franchiseId = $franchise?->id;

        $year = now()->year;

        return Inertia::render('technician/dashboard/Index', [
            'franchiseExists' => (bool) $franchise,
            'maintenance' => $this->getMaintenanceJobsSummary(null, $franchiseId),
        ]);
    }

    protected function getFranchiseOrDefault()
    {
        return auth()->user()->technicianDetails?->franchises()->first();
    }

    protected function getMaintenanceJobsSummary($search = null, ?int $franchiseId)
    {
        $jobsQuery = Maintenance::with(['vehicle.driver'])
            ->where('franchise_id', $franchiseId)
            // ->whereHas('technician', function ($query) use ($franchiseId) {
            //     $query->where('franchise_id', $franchiseId);
            // })
            ->orderBy('maintenance_date', 'desc');

        if ($search) {
            $jobsQuery->where(function ($q) use ($search) {
                $q->whereHas('vehicle', fn($v) =>
                    $v->where('plate_number', 'like', "%{$search}%")
                )
                ->orWhereHas('vehicle.driver', fn($d) =>
                    $d->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                );
            });
        }

        return $jobsQuery->paginate(10)->through(fn($job) => [
            'id'                   => $job->id,
            'maintenance_type'     => $job->maintenance_type,
            'description'          => $job->description,
            'maintenance_date'     => $job->maintenance_date,
            'next_maintenance_date'=> $job->next_maintenance_date,
            'status'               => $job->status?->name,

            'vehicle_plate'        => $job->vehicle?->plate_number,
            'driver_name'          => $job->vehicle?->driver?->user->name,
            'driver_email'         => $job->vehicle?->driver?->user->email,
            'driver_phone'         => $job->vehicle?->driver?->user->phone,

            'technician'           => $job->technician?->user->name,

            'franchise_id'         => $job->franchise_id,
            'branch_id'            => $job->branch_id,
            'created_at'           => $job->created_at?->toDateString(),
        ]);
    }
}
