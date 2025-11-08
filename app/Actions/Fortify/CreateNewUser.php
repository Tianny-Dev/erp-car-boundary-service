<?php

namespace App\Actions\Fortify;

use App\Enums\AccesibilityOption;
use App\Models\User;
use App\Models\UserDriver;
use App\Models\UserPassenger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Laravel\Fortify\Contracts\CreatesNewUsers;


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    // public function create(array $input): User
    // {
    //     try {
    //         $userTypeId = Crypt::decryptString($input['user_type_id']);
    //     } catch (\Exception $e) {
    //         abort(403, 'Invalid user type.');
    //     }

    //     dd([$input, "User type id is $userTypeId"]);

    //     Validator::make($input, [
    //         'user_type_id' => ['required', 'exists:user_types,id'],
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => [
    //             'required',
    //             'string',
    //             'email',
    //             'max:255',
    //             Rule::unique(User::class),
    //         ],
    //         'password' => $this->passwordRules(),
    //     ])->validate();

    //     return User::create([
    //         'user_type_id' => $input['user_type_id'],
    //         'name' => $input['name'],
    //         'email' => $input['email'],
    //         'password' => $input['password'],
    //     ]);
    // }

    public function create(array $input)
    {
        try {
            $userTypeId = Crypt::decryptString($input['user_type_id']);
        } catch (\Exception $e) {
            abort(403, 'Invalid user type.');
        }

        $userType = \App\Models\UserType::findOrFail($userTypeId);

        switch ($userType->name) {
            case 'driver':
                return $this->createDriver($input, $userTypeId);
            case 'passenger':
                return $this->createPassenger($input, $userTypeId);
            case 'technician':
                return $this->createTechnician($input, $userTypeId);
            case 'owner':
                return $this->createOwner($input, $userTypeId);
            default:
                return $this->createDefault($input, $userTypeId);
        }
    }


    protected function createDriver(array $input, int $userTypeId): User
    {
        Log::info('Creating driver user', ['user_type_id' => $userTypeId]);

        try {
            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
                'phone' => ['required', 'string', 'max:25', Rule::unique(User::class)],
                'address' => ['required', 'string', 'max:255'],
                'region' => ['required', 'string', 'max:255'],
                'province' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'barangay' => ['required', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:20'],
                'password' => $this->passwordRules(),
                'payment_option_id'=> ['required', 'exists:payment_options,id'],
                'license_number' => ['required', 'string', 'max:50'],
                'license_expiry' => ['required', 'date'],
                'front_license_picture' => ['required', 'image', 'max:2048'],
                'back_license_picture' => ['required', 'image', 'max:2048'],
                'nbi_clearance' => ['required', 'image', 'max:2048'],
                'selfie_picture' => ['required', 'image', 'max:2048'],
            ])->validate();

            Log::info('Validation passed for driver', ['email' => $input['email']]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input_keys' => array_keys($input),
            ]);
            throw $e;
        }

        Log::info('Validation passed for driver', ['email' => $input['email']]);

        $user = User::create([
            'user_type_id' => $userTypeId,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'gender'=> $input['gender'],
            'address'=> $input['address'],
            'region' => $input['region'],
            'province' => $input['province'] ?? 'N/A',
            'city' => $input['city'],
            'barangay' => $input['barangay'],
            'postal_code' => $input['postal_code'],
            'password' => Hash::make($input['password']),
        ]);

        Log::info('User created', ['user_id' => $user->id]);

        $frontLicensePicturePath = $this->handleFileUpload($input['front_license_picture'], $user->id, 'front_license');
        $backLicensePicturePath = $this->handleFileUpload($input['back_license_picture'], $user->id, 'back_license');
        $nbiClearancePath = $this->handleFileUpload($input['nbi_clearance'], $user->id, 'nbi_clearance');
        $selfiePicturePath = $this->handleFileUpload($input['selfie_picture'], $user->id, 'selfie');

        Log::info('File upload paths', [
            'front' => $frontLicensePicturePath,
            'back' => $backLicensePicturePath,
            'nbi' => $nbiClearancePath,
            'selfie' => $selfiePicturePath,
        ]);

        UserDriver::create([
            'id' => $user->id,
            'status_id' => 6,
            'payment_option_id' => $input['payment_option_id'],
            'license_number' => $input['license_number'],
            'license_expiry'=> $input['license_expiry'],
            'front_license_picture' => $frontLicensePicturePath,
            'back_license_picture' => $backLicensePicturePath,
            'nbi_clearance' => $nbiClearancePath,
            'selfie_picture' => $selfiePicturePath,
        ]);

        Log::info('Driver profile created successfully', ['user_id' => $user->id]);

        return $user;
    }

    protected function createPassenger(array $input, int $userTypeId): User
    {
        Log::info('Creating passenger user', ['user_type_id' => $userTypeId]);
        try {
            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
                'phone' => ['required', 'string', 'max:25', Rule::unique(User::class)],
                'address' => ['required', 'string', 'max:255'],
                'region' => ['required', 'string', 'max:255'],
                'province' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'barangay' => ['required', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:20'],
                'password' => $this->passwordRules(),

                'payment_option_id' => ['required', 'exists:payment_options,id'],
                'preferred_language' => ['required','in:English,Filipino,Others'],
                'accessibility_option'=> ['required',new Enum(AccesibilityOption::class)],
                'birth_date'=> ['required','date'],
                'age'=> ['required','int', 'max:150'],
            ])->validate();

            Log::info('Validation passed for passenger', ['email' => $input['email']]);
        } catch (\Illuminate\Validation\ValidationException $e) {
             Log::error('Passenger validation failed', [
        'errors' => $e->errors(),
        'input_keys' => array_keys($input),
    ]);
            throw $e;
        }

        Log::info('Validation passed for passenger', ['email' => $input['email']]);

        $user = User::create([
            'user_type_id' => $userTypeId,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'gender'=> $input['gender'],
            'address'=> $input['address'],
            'region' => $input['region'],
            'province' => $input['province'] ?? 'N/A',
            'city' => $input['city'],
            'barangay' => $input['barangay'],
            'postal_code' => $input['postal_code'],
            'password' => Hash::make($input['password']),
        ]);

        Log::info('User created', ['user_id' => $user->id]);

        UserPassenger::create([
            'id' => $user->id,
            'status_id' => 6,
            'payment_option_id' => $input['payment_option_id'],
            'preferred_language' => $input['preferred_language'],
            'accessibility_option' => $input['accessibility_option'],
            'birth_date' => $input['birth_date'],
            'age' => $input['age'],
        ]);

        Log::info('Passenger profile created successfully', ['user_id' => $user->id]);

         return $user;
    }

    protected function createTechnician(array $input, int $userTypeId): User
    {

    }

    protected function createOwner(array $input, int $userTypeId): User
    {

    }

    protected function createDefault(array $input, int $userTypeId): ?User
    {
        return null;
    }

    /**
     * Save uploaded file with unique filenames (timestamp based).
     */
    private function handleFileUpload(?UploadedFile $file, int $userId, string $type): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $directory = "id/{$userId}";
        $filename = Str::uuid()."_{$type}.".$file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'public');
    }
}
