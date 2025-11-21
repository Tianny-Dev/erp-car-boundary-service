<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\Gender;
use App\Enums\IdType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // --- Branch Details ---
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'email' => ['required', 'email', 'max:255', 'unique:branches,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:branches,phone'],
            'payment_option_id' => ['required', 'exists:payment_options,id'],
            
            // Branch Address
            'address' => ['required', 'string'],
            'region' => ['required', 'string'],
            'province' => ['nullable', 'string'], // Nullable because NCR has no province
            'city' => ['required', 'string'],
            'barangay' => ['required', 'string'],
            'postal_code' => ['required', 'string', 'max:20'],

            // Branch Files
            'dti_registration_attachment' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
            'mayor_permit_attachment' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
            'proof_agreement_attachment' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],

            // --- Manager Toggle ---
            'has_manager' => ['required', 'boolean'],

            // --- Manager Details (Required if has_manager is true) ---
            'manager.first_name' => ['exclude_unless:has_manager,true', 'required', 'string', 'max:255'],
            'manager.last_name' => ['exclude_unless:has_manager,true', 'required', 'string', 'max:255'],
            'manager.email' => ['exclude_unless:has_manager,true', 'required', 'email', 'unique:users,email'],
            'manager.password' => ['exclude_unless:has_manager,true', 'required', 'min:8', 'confirmed'],
            'manager.phone' => ['exclude_unless:has_manager,true', 'required', 'string', 'unique:users,phone'],
            'manager.gender' => ['exclude_unless:has_manager,true', 'required', new Enum(Gender::class)],
            
            // Manager Address
            'manager.address' => ['exclude_unless:has_manager,true', 'required', 'string'],
            'manager.region' => ['exclude_unless:has_manager,true', 'required', 'string'],
            'manager.province' => ['exclude_unless:has_manager,true', 'nullable', 'string'],
            'manager.city' => ['exclude_unless:has_manager,true', 'required', 'string'],
            'manager.barangay' => ['exclude_unless:has_manager,true', 'required', 'string'],
            'manager.postal_code' => ['exclude_unless:has_manager,true', 'required', 'string'],

            // Manager ID Details
            'manager.valid_id_type' => ['exclude_unless:has_manager,true', 'required', new Enum(IdType::class)],
            'manager.valid_id_number' => ['exclude_unless:has_manager,true', 'required', 'string', 'unique:user_managers,valid_id_number'],
            'manager.front_valid_id_picture' => ['exclude_unless:has_manager,true', 'required', 'file', 'mimes:jpg,png', 'max:5120'],
            'manager.back_valid_id_picture' => ['exclude_unless:has_manager,true', 'required', 'file', 'mimes:jpg,png', 'max:5120'],
        ];
    }
}