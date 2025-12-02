<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Status;
use App\Models\BoundaryContract;
use App\Models\Vehicle;
use App\Models\UserDriver;

class StoreBoundaryContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'coverage_area' => ['required', 'string', 'max:1000'],
            'contract_terms' => ['required', 'string', 'max:1000'],
            'renewal_terms' => ['required', 'string', 'max:1000'],
            
            // LOGIC: Ensure exactly one is present (Franchise XOR Branch)
            'franchise_id' => [
                'nullable',
                'integer',
                'required_without:branch_id', // Required if branch is empty
                'prohibited_unless:branch_id,null', // Forbidden if branch has value
                'exists:franchises,id'
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'required_without:franchise_id', // Required if franchise is empty
                'prohibited_unless:franchise_id,null', // Forbidden if franchise has value
                'exists:branches,id'
            ],

            // LOGIC: Driver validation
            'driver_id' => [
                'required',
                'integer',
                'exists:user_drivers,id',
                // Custom rule to check if driver is Active AND has no active contracts
                function ($attribute, $value, $fail) {
                    $activeStatusId = Status::where('name', 'active')->value('id');

                    // 1. Check if Driver has an existing Active Contract
                    $hasActiveContract = BoundaryContract::where('driver_id', $value)
                        ->where('status_id', $activeStatusId)
                        ->exists();

                    if ($hasActiveContract) {
                        $fail('The selected driver already has an active boundary contract.');
                    }

                    $hasActiveStatus = UserDriver::where('id', $value)
                        ->where('status_id', $activeStatusId)
                        ->exists();

                    if (!$hasActiveStatus) {
                        $fail('The selected driver is not active.');
                    }
                    
                    // 2. Optional: Verify Driver belongs to the selected Franchise/Branch
                    $existsInEntity = false;
                    if ($this->franchise_id) {
                         $existsInEntity = DB::table('franchise_user_driver')
                            ->where('franchise_id', $this->franchise_id)
                            ->where('user_driver_id', $value)
                            ->exists();
                    } elseif ($this->branch_id) {
                        $existsInEntity = DB::table('branch_user_driver')
                            ->where('branch_id', $this->branch_id)
                            ->where('user_driver_id', $value)
                            ->exists();
                    }

                    if (!$existsInEntity) {
                        $fail('The selected driver does not belong to the selected franchise or branch.');
                    }
                },
            ],

            // LOGIC: Vehicle validation
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
                // Custom rule: Available Status + Belongs to Entity + No Active Contract
                function ($attribute, $value, $fail) {
                    $availableStatusId = Status::where('name', 'available')->value('id');
                    $activeStatusId = Status::where('name', 'active')->value('id');

                    // 1. Check ownership
                    $vehicle = Vehicle::find($value);
                    if ($this->franchise_id && $vehicle->franchise_id != $this->franchise_id) {
                        $fail('The selected vehicle does not belong to this franchise.');
                        return;
                    }
                    if ($this->branch_id && $vehicle->branch_id != $this->branch_id) {
                        $fail('The selected vehicle does not belong to this branch.');
                        return;
                    }

                    // 2. Check Availability (Is it already in an active contract?)
                    $hasActiveContract = BoundaryContract::where('vehicle_id', $value)
                        ->where('status_id', $activeStatusId)
                        ->exists();

                    if ($hasActiveContract) {
                        $fail('The selected vehicle is currently assigned to another active contract.');
                    }

                    $hasAvailableStatus = Vehicle::where('id', $value)
                        ->where('status_id', $availableStatusId)
                        ->exists();

                    if (!$hasAvailableStatus) {
                        $fail('The selected vehicle is not available.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'franchise_id.prohibited_unless' => 'You cannot select both a Franchise and a Branch.',
            'branch_id.prohibited_unless' => 'You cannot select both a Franchise and a Branch.',
        ];
    }
}
