<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BoundaryContract;
use App\Models\Status;
use App\Models\UserDriver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DriverManagementController extends Controller
{
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            abort(404, 'Franchise not found');
        }

        $statusQuery = ['active', 'suspended', 'retired'];

        $drivers = $franchise->drivers()
            ->with(['user', 'status', 'boundaryContracts' => function($query) {
                $query->latest()->with('vehicle');
            }])
            ->whereHas('status', function ($query) use ($statusQuery) {
                $query->whereIn('name', $statusQuery);
            })
            ->paginate(10)
            ->through(function($driver) {
                $latestContract = $driver->boundaryContracts->first();
                $vehicle = $latestContract?->vehicle;

                return [
                    'id' => $driver->id,
                    'name' => $driver->user?->name,
                    'username' => $driver->user?->username,
                    'email' => $driver->user?->email,
                    'phone' => $driver->user?->phone,
                    'region' => $driver->user?->region,
                    'province' => $driver->user?->province,
                    'city' => $driver->user?->city,
                    'barangay' => $driver->user?->barangay,
                    'address' => $driver->user?->address,
                    'status' => $driver->status?->name,
                    'vehicle' => [
                        'plate_number' => $vehicle?->plate_number ?? 'No Vehicle',
                        'brand'        => $vehicle?->brand ?? 'N/A',
                        'model'        => $vehicle?->model ?? 'N/A',
                        'color'        => $vehicle?->color ?? 'N/A',
                    ],
                    'details' => [
                        'code_number'           => $driver->code_number,
                        'license_number'        => $driver->license_number,
                        'license_expiry'        => $driver->license_expiry,
                        'is_verified'           => $driver->is_verified,
                        'shift'                 => $driver->shift,
                        'hire_date'             => $driver->hire_date,
                        'front_license_picture' => $driver->front_license_picture ? asset('storage/driver_documents/' . $driver->front_license_picture) : null,
                        'back_license_picture'  => $driver->back_license_picture ? asset('storage/driver_documents/' . $driver->back_license_picture) : null,
                        'nbi_clearance'         => $driver->nbi_clearance ? asset('storage/driver_documents/' . $driver->nbi_clearance) : null,
                        'selfie_picture'        => $driver->selfie_picture ? asset('storage/driver_documents/' . $driver->selfie_picture) : null,
                    ],
                ];
            });

        $statuses = Status::whereIn('name', ['active', 'suspended', 'retired'])
            ->get(['id', 'name']);

        return Inertia::render('owner/driver-management/Index', [
            'drivers' => $drivers,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        if (!$franchise) {
            return redirect()->back()->withErrors(['message' => 'Franchise not found']);
        }

        $driver = UserDriver::with('user')->findOrFail($id);

        if (!$driver->franchises()->where('franchise_id', $franchise->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // 1. Handle Document File Updates
        $fileFields = ['front_license_picture', 'back_license_picture', 'nbi_clearance', 'selfie_picture'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($driver->$field) {
                    Storage::disk('public')->delete('driver_documents/' . $driver->$field);
                }
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . $driver->id . '.' . $file->getClientOriginalExtension();
                $file->storeAs('driver_documents', $filename, 'public');
                $driver->$field = $filename;
                $driver->save();
                return redirect()->back()->with('success', 'Document updated!');
            }
        }

        // 2. Handle Profile Updates
        if ($request->hasAny(['email', 'phone', 'license_number', 'code_number', 'region'])) {
            $userId = $driver->id;
            $request->validate([
                'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
                'phone' => ['sometimes', Rule::unique('users', 'phone')->ignore($userId)],
                'code_number' => ['sometimes', Rule::unique('user_drivers', 'code_number')->ignore($driver->id)],
                'license_number' => ['sometimes', Rule::unique('user_drivers', 'license_number')->ignore($driver->id)],
            ]);

            if ($driver->user) {
                $driver->user->update($request->only(['email', 'phone', 'region', 'province', 'city', 'barangay']));
            }
            $driver->update($request->only(['license_number', 'license_expiry', 'code_number', 'shift']));

            return redirect()->back()->with('success', 'Driver profile updated!');
        }

        // 3. Handle Status Update (Syncing with Boundary Contracts)
        if ($request->has('status_id')) {
            $request->validate(['status_id' => 'required|exists:statuses,id']);
            $newStatus = Status::findOrFail($request->status_id);

            DB::transaction(function () use ($driver, $newStatus) {
                // Always update the Driver status first
                $driver->update(['status_id' => $newStatus->id]);

                // Find the latest contract regardless of its current status
                $latestContract = BoundaryContract::where('driver_id', $driver->id)->latest()->first();

                if ($latestContract) {
                    // Sync the contract status to match the driver
                    $latestContract->update(['status_id' => $newStatus->id]);

                    if (in_array($newStatus->name, ['suspended', 'retired'])) {
                        // Release Vehicle
                        $availableStatus = Status::where('name', 'available')->first();

                        if ($latestContract->vehicle_id) {
                            Vehicle::where('id', $latestContract->vehicle_id)->update([
                                'driver_id' => null,
                                'status_id' => $availableStatus?->id ?? 1
                            ]);

                            // Important: Null the vehicle_id in the contract to indicate it's no longer active
                            $latestContract->update(['vehicle_id' => null]);
                        }
                    }
                    elseif ($newStatus->name === 'active') {
                        // If moving back to active, ensure the vehicle logic is handled
                        // Note: Re-assigning a vehicle usually happens via a dedicated assignment,
                        // but this ensures the contract record matches the driver status.
                        $occupiedStatus = Status::where('name', 'occupied')->orWhere('name', 'active')->first();

                        if ($latestContract->vehicle_id) {
                            Vehicle::where('id', $latestContract->vehicle_id)->update([
                                'driver_id' => $driver->id,
                                'status_id' => $occupiedStatus?->id ?? 2
                            ]);
                        }
                    }
                }
            });

            return redirect()->back()->with('success', 'Driver and contract statuses updated.');
        }

        return redirect()->back();
    }

    public function destroy(string $id)
{
    // Find the driver (this will only find non-deleted drivers by default)
    $driver = UserDriver::findOrFail($id);

    $ownerFranchises = auth()->user()->ownerDetails->franchises->pluck('id');
    $retiredStatus = Status::where('name', 'retired')->first();
    $availableStatus = Status::where('name', 'available')->first();

    DB::transaction(function () use ($driver, $ownerFranchises, $retiredStatus, $availableStatus) {

        Vehicle::where('driver_id', $driver->id)->update([
            'driver_id' => null,
            'status_id' => $availableStatus?->id ?? 1
        ]);

        BoundaryContract::where('driver_id', $driver->id)->update([
            'status_id' => $retiredStatus?->id ?? 6,
            'vehicle_id' => null
        ]);

        $driver->franchises()->detach($ownerFranchises);

        $driver->update([
            'status_id' => $retiredStatus?->id ?? 6,
            'is_verified' => false
        ]);

        $driver->delete();
    });

    return back()->with('success', 'Driver has been moved to trash/retired.');
}
}
