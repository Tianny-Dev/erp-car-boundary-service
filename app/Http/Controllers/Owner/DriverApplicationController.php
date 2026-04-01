<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriver;
use Faker\Factory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class DriverApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            abort(404, 'Franchise not found');
        }

        $driversQuery = User::with('driverDetails.status')
            ->whereHas('userType', fn ($q) =>
                $q->where('name', 'driver')
            )
            ->whereHas('driverDetails', fn ($q) =>
                $q->whereHas('status', fn ($s) =>
                    $s->whereIn('name', ['pending', 'inactive'])
                )
            );

        // Global search
        if ($search = request('search')) {
            $driversQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $drivers = $driversQuery->paginate(10)->through(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'region' => $user->region,
            'province' => $user->province,
            'city' => $user->city,
            'barangay' => $user->barangay,
            'address' => $user->address,
            'status' => $user->driverDetails?->status?->name,

            'details' => [
                'license_number'  => $user->driverDetails?->license_number,
                'code_number'  => $user->driverDetails?->code_number,
                'license_expiry'  => $user->driverDetails?->license_expiry,
                'is_verified'     => $user->driverDetails?->is_verified,
                'shift'           => $user->driverDetails?->shift,
                'hire_date'       => $user->driverDetails?->hire_date,

                'front_license_picture' => $user->driverDetails?->front_license_picture
                    ? asset('storage/driver_documents/' . $user->driverDetails->front_license_picture)
                    : null,

                'back_license_picture' => $user->driverDetails?->back_license_picture
                    ? asset('storage/driver_documents/' . $user->driverDetails->back_license_picture)
                    : null,

                'nbi_clearance' => $user->driverDetails?->nbi_clearance
                    ? asset('storage/driver_documents/' . $user->driverDetails->nbi_clearance)
                    : null,

                'selfie_picture' => $user->driverDetails?->selfie_picture
                    ? asset('storage/driver_documents/' . $user->driverDetails->selfie_picture)
                    : null,
            ],
        ]);

        return Inertia::render('owner/driver-application/Index', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $driver = UserDriver::findOrFail($id);
        $ownerFranchises = auth()->user()->ownerDetails->franchises->pluck('id');
        $action = $request->input('action');

        // Select only the first franchise of the owner to attach
        $targetFranchiseId = $ownerFranchises->first();

        if ($action === 'approve') {
            try {
                DB::transaction(function () use ($driver, $targetFranchiseId) {
                    // 1. Check if driver is already assigned (Double Check)
                    if ($driver->franchises()->exists()) {
                        throw new \Exception('This driver has already been accepted by another franchise.');
                    }

                    // 2. Update Driver Info
                    $driver->status_id = 1; // Active
                    $driver->is_verified = true;

                    if (empty($driver->code_number)) {
                        $faker = Factory::create();
                        do {
                            $code = $faker->bothify('??-####');
                        } while (UserDriver::where('code_number', $code)->exists());
                        $driver->code_number = $code;
                    }
                    $driver->save();

                    // 3. Attach to Franchise (This will fail if unique constraint is triggered)
                    $driver->franchises()->attach($targetFranchiseId);
                });
            } catch (QueryException $e) {
                // Handle Database unique constraint failure
                return back()->withErrors(['error' => 'This driver was already taken by another franchise just now.']);
            } catch (\Exception $e) {
                // Handle the manual exception
                return back()->withErrors(['error' => $e->getMessage()]);
            }

        } elseif ($action === 'deny') {
            $driver->status_id = 18; // Deny
            $driver->franchises()->detach($ownerFranchises);
            $driver->save();
        }

        return back()->with('success', 'Driver application processed.');
    }
}
