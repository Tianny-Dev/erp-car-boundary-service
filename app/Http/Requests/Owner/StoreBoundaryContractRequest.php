<?php

namespace App\Http\Requests\Owner;

use App\Models\BoundaryContract;
use App\Models\Status;
use App\Models\UserDriver;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreBoundaryContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Capture the contract ID from the route to allow "ignoring" itself during updates
        $contractId = $this->route('boundary_contract')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'coverage_area' => ['required', 'string', 'max:1000'],
            'contract_terms' => ['required', 'string', 'max:1000'],
            'renewal_terms' => ['required', 'string', 'max:1000'],

            'driver' => [
                'required',
                'integer',
                'exists:user_drivers,id',
                function ($attribute, $value, $fail) use ($contractId) {
                    $activeStatusId = Status::where('name', 'active')->value('id');

                    // Check if Driver has an existing Active Contract (EXCEPT this one)
                    $hasActiveContract = BoundaryContract::where('driver_id', $value)
                        ->where('status_id', $activeStatusId)
                        ->when($contractId, function ($query) use ($contractId) {
                            return $query->where('id', '!=', $contractId);
                        })
                        ->exists();

                    if ($hasActiveContract) {
                        $fail('The selected driver already has an active boundary contract.');
                        return;
                    }

                    // On EDIT: We don't strictly check if the driver is "Active" status
                    // if they are already the driver on this contract.
                    $hasActiveStatus = UserDriver::where('id', $value)
                        ->where('status_id', $activeStatusId)
                        ->exists();

                    if (!$hasActiveStatus && !$contractId) {
                        $fail('The selected driver is not active.');
                    }

                    $franchise = auth()->user()->ownerDetails?->franchises()->first();
                    $franchiseId = $franchise?->id;

                    $existsInEntity = DB::table('franchise_user_driver')
                        ->where('franchise_id', $franchiseId)
                        ->where('user_driver_id', $value)
                        ->exists();

                    if (!$existsInEntity) {
                        $fail('The selected driver does not belong to this franchise.');
                    }
                },
            ],

            'vehicle' => [
                'required',
                'integer',
                'exists:vehicles,id',
                function ($attribute, $value, $fail) use ($contractId) {
                    $availableStatusId = Status::where('name', 'available')->value('id');
                    $activeStatusId = Status::where('name', 'active')->value('id');

                    $franchise = auth()->user()->ownerDetails?->franchises()->first();
                    $franchiseId = $franchise?->id;

                    $vehicle = Vehicle::find($value);
                    if ($franchiseId && $vehicle->franchise_id != $franchiseId) {
                        $fail('The selected vehicle does not belong to this franchise.');
                        return;
                    }

                    // Check if vehicle is in another Active Contract (EXCEPT this one)
                    $hasActiveContract = BoundaryContract::where('vehicle_id', $value)
                        ->where('status_id', $activeStatusId)
                        ->when($contractId, function ($query) use ($contractId) {
                            return $query->where('id', '!=', $contractId);
                        })
                        ->exists();

                    if ($hasActiveContract) {
                        $fail('The selected vehicle is currently assigned to another active contract.');
                        return;
                    }

                    // If it's a NEW contract, status must be "available".
                    // If it's an UPDATE, status is likely "active" (occupied), which is fine.
                    if (!$contractId && $vehicle->status_id != $availableStatusId) {
                        $fail('The selected vehicle is not available.');
                    }
                },
            ],
        ];
    }
}
