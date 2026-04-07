<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreBoundaryContractRequest;
use App\Models\BoundaryContract;
use App\Models\Status;
use App\Models\UserDriver;
use App\Models\Vehicle;
use App\Notifications\DriverContractApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoundaryContractController extends Controller
{
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        $franchiseId = $franchise?->id;

        $query = BoundaryContract::with([
            'driver' => function ($q) {
                $q->withTrashed()->with('user');
            },
            'status',
            'franchise',
            'vehicle'
        ])
        ->when($franchiseId, fn($q) => $q->where('franchise_id', $franchiseId))
        ->orderByDesc('created_at');

        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('name', $status));
            }
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('coverage_area', 'like', "%{$search}%")
                    ->orWhereHas('franchise', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $contracts = $query->paginate(10)->through(fn($contract) => [
            'id' => $contract->id,
            'name' => $contract->name,
            'amount' => $contract->amount,
            'coverage_area' => $contract->coverage_area,
            'contract_terms' => $contract->contract_terms,
            'renewal_terms' => $contract->renewal_terms,
            'start_date' => $contract->start_date,
            'end_date' => $contract->end_date,
            // Optional: Add a "(Retired)" tag if the driver is deleted
            'driver_username' => $contract->driver?->user->username . ($contract->driver?->trashed() ? ' (Retired)' : ''),
            'driver_email' => $contract->driver?->user->email,
            'driver_phone' => $contract->driver?->user->phone,
            'status' => $contract->status?->name,
            'franchise' => $contract->franchise?->name,
            'vehicle_info' => $contract->vehicle
                ? "{$contract->vehicle->plate_number} ({$contract->vehicle->brand})"
                : 'No vehicle currently assigned',
        ]);

        return Inertia::render('owner/boundary-contracts/Index', [
            'contracts' => $contracts,
        ]);
    }

    public function create()
    {
        return Inertia::render('owner/boundary-contracts/Create', $this->getFormData());
    }

    public function store(StoreBoundaryContractRequest $request)
    {
        DB::transaction(function () use ($request) {
            $activeStatusId = Status::where('name', 'active')->firstOrFail()->id;
            $franchiseId = auth()->user()->ownerDetails?->franchises()->first()?->id;

            BoundaryContract::create([
                'status_id' => $activeStatusId,
                'franchise_id' => $franchiseId,
                'driver_id' => $request->driver,
                'vehicle_id' => $request->vehicle,
                'name' => $request->name,
                'amount' => $request->amount,
                'currency' => 'PHP',
                'coverage_area' => $request->coverage_area,
                'contract_terms' => $request->contract_terms,
                'renewal_terms' => $request->renewal_terms,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            Vehicle::where('id', $request->vehicle)->update([
                'driver_id' => $request->driver,
                'status_id' => $activeStatusId
            ]);

            $driver = UserDriver::findOrFail($request->driver);
            $driver->user->notify(new DriverContractApproved());
        });

        return to_route('owner.boundary-contracts.index');
    }

   public function edit(BoundaryContract $boundary_contract)
{
    // Check if the contract is active; redirect if not
    if ($boundary_contract->status->name !== 'active') {
        return to_route('owner.boundary-contracts.index')
            ->with('error', 'Only active contracts can be edited.');
    }

    $formData = $this->getFormData($boundary_contract);

    return Inertia::render('owner/boundary-contracts/Create', array_merge($formData, [
        'contract' => [
            'id' => $boundary_contract->id,
            'driver' => $boundary_contract->driver_id,
            'vehicle' => $boundary_contract->vehicle_id,
            'name' => $boundary_contract->name,
            'amount' => $boundary_contract->amount,
            'coverage_area' => $boundary_contract->coverage_area,
            'contract_terms' => $boundary_contract->contract_terms,
            'renewal_terms' => $boundary_contract->renewal_terms,
            'start_date' => $boundary_contract->start_date,
            'end_date' => $boundary_contract->end_date,
            'status' => $boundary_contract->status->name,
        ]
    ]));
}

public function update(StoreBoundaryContractRequest $request, BoundaryContract $boundary_contract)
{
    if ($boundary_contract->status->name !== 'active') {
        abort(403, 'Unauthorized action. This contract is no longer active.');
    }

    DB::transaction(function () use ($request, $boundary_contract) {
        // If vehicle changed, release old one
        if ($boundary_contract->vehicle_id != $request->vehicle) {
            $availableStatusId = Status::where('name', 'available')->first()?->id ?? 1;
            Vehicle::where('id', $boundary_contract->vehicle_id)->update([
                'driver_id' => null,
                'status_id' => $availableStatusId
            ]);
        }

        $boundary_contract->update([
            'driver_id' => $request->driver,
            'vehicle_id' => $request->vehicle,
            'name' => $request->name,
            'amount' => $request->amount,
            'coverage_area' => $request->coverage_area,
            'contract_terms' => $request->contract_terms,
            'renewal_terms' => $request->renewal_terms,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        $activeStatusId = Status::where('name', 'active')->firstOrFail()->id;
        Vehicle::where('id', $request->vehicle)->update([
            'driver_id' => $request->driver,
            'status_id' => $activeStatusId
        ]);
    });

    return to_route('owner.boundary-contracts.index');
}

    private function getFormData($existingContract = null)
{
    $franchise = auth()->user()->ownerDetails?->franchises()->first();
    if (!$franchise) abort(404);

    $vehicles = $franchise->vehicles()
        ->where(function($query) use ($existingContract) {
            // 1. Core Logic: Vehicle must be marked as 'available'
            // AND must not have an active contract
            $query->whereHas('status', fn($s) => $s->where('name', 'available'))
                ->whereDoesntHave('boundaryContracts', function($q) {
                    $q->whereHas('status', fn($s) => $s->where('name', 'active'));
                });

            // 2. OR if we are editing, allow the vehicle currently assigned to this contract
            // even if its status is 'active' or 'assigned'
            if ($existingContract) {
                $query->orWhere('id', $existingContract->vehicle_id);
            }
        })
        ->get()->map(fn($v) => [
            'id' => $v->id,
            'plate_number' => $v->plate_number,
            'brand' => $v->brand,
            'model' => $v->model,
            'status' => strtolower($v->status->name),
        ]);

    $drivers = $franchise->drivers()
        ->whereHas('status', fn($q) => $q->where('name', 'active'))
        ->where(function($query) use ($existingContract) {
            // Logic for drivers: Must not have an active contract
            $query->whereDoesntHave('boundaryContracts', function($q) {
                $q->whereHas('status', fn($s) => $s->where('name', 'active'));
            });

            // OR allow the driver currently assigned to this specific contract
            if ($existingContract) {
                $query->orWhere('id', $existingContract->driver_id);
            }
        })
        ->with('user')->get()->map(fn($d) => [
            'id' => $d->id,
            'username' => $d->user?->username,
        ]);

    return compact('vehicles', 'drivers');
}
}
