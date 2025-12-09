<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\Gender;
use App\Enums\IdType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class StoreManagerRequest extends FormRequest
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
            // --- Manager Details (Required if has_manager is true) ---
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('franchises', 'email'),
                Rule::unique('branches', 'email')
            ],
            'phone' => ['required', 'string', 'max:255',
                Rule::unique('users', 'phone'),
                Rule::unique('franchises', 'phone'),
                Rule::unique('branches', 'phone')
            ],
            'password' => ['required', 'min:8', 'confirmed'],
            'gender' => ['required', new Enum(Gender::class)],
            
            // Manager Address
            'address' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255', 'required_unless:region,NCR'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],

            // Manager ID Details
            'valid_id_type' => ['required', 'string', new Enum(IdType::class)],
            'valid_id_number' => ['required', 'string', 'max:20', Rule::unique('user_owners', 'valid_id_number')],
            'front_valid_id_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'back_valid_id_picture' => ['required','file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
