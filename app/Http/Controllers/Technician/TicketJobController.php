<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketJobController extends Controller
{
    public function index()
    {
        $franchise = $this->getFranchiseOrDefault();
        $franchiseId = $franchise?->id;

        $statuses = Status::whereIn('name', ['active', 'pending', 'completed'])
            ->get(['id', 'name']);

        return Inertia::render('technician/ticket-job/Index', [
            'franchiseExists' => (bool) $franchise,
            'statuses' => $statuses,
            'maintenance' => $this->getPaginatedMaintenances( $franchiseId),
        ]);
    }

    protected function getFranchiseOrDefault()
    {
        return auth()->user()->technicianDetails?->franchises()->first();
    }

    /**
     * Get paginated tickets/jobs list with relationships.
     */
    protected function getPaginatedMaintenances(?int $franchiseId)
    {
        $perPage = request('per_page', 10);

        $query = Maintenance::with(['status', 'franchise', 'branch', 'technician', 'vehicle.driver'])
            ->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId))
            ->orderByDesc('created_at');

        /**
         * Status Filter
         */
        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }
        }

        /**
         * Regular paginated detailed jobs
         */
        return $query->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn($job) => [
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $franchise = auth()->user()->technicianDetails?->franchises()->first();

        if (!$franchise) {
            return response()->json(['message' => 'Franchise not found'], 404);
        }

        $job = $franchise->maintenances()->find($id);
        if (!$job) {
            return response()->json(['message' => 'Maintenance Job not found'], 404);
        }

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $job->update([
            'status_id' => $request->status_id
        ]);

        return redirect()->back()->with('success', 'Maintenance Job status updated!');
    }
}
