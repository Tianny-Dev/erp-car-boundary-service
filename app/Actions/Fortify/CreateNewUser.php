<?php

namespace App\Actions\Fortify;

use App\Enums\AccesibilityOption;
use App\Enums\IdType;
use App\Models\User;
use App\Models\UserDriver;
use App\Models\UserPassenger;
use App\Models\UserTechnician;
use App\Models\Franchise;
use App\Models\UserOwner;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
    public function create(array $input)
    {
        try {
            $userTypeId = Crypt::decryptString($input['user_type_id']);
        } catch (\Exception $e) {
            abort(403, 'Invalid user type.');
        }

        $userType = UserType::findOrFail($userTypeId);

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

        dd($input);
        
            // 1. Validation
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique(User::class),
                Rule::unique('franchises', 'email'),
                Rule::unique('branches', 'email')
            ],
            'phone' => [
                'required', 'string', 'max:20',
                Rule::unique(User::class),
                Rule::unique('franchises', 'phone'),
                Rule::unique('branches', 'phone')
            ],
            'password' => $this->passwordRules(),
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other', 'Prefer not to say'])],
            'birth_date' => ['required','date','before_or_equal:' . now()->subYears(10)->toDateString(),'after_or_equal:' . now()->subYears(100)->toDateString(),],         
            'address' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'password' => $this->passwordRules(),
            'license_number' => ['required', 'string', 'max:20'],
            'license_expiry' => ['required', 'date','after_or_equal:' . now()->toDateString()],
            'payment_option_id'=> ['required', 'exists:payment_options,id'],
            'front_license_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'back_license_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nbi_clearance' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'selfie_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'terms1' => ['required', 'accepted'],
            'terms2' => ['required', 'accepted'],
        ])->validate();

        

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
        // return redirect()->route('driver.dashboard');
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
            'status_id' => 1,
            'payment_option_id' => $input['payment_option_id'],
            'preferred_language' => $input['preferred_language'],
            'accessibility_option' => $input['accessibility_option'],
            'birth_date' => $input['birth_date'],
            'age' => $input['age'],
        ]);

        Log::info('Passenger profile created successfully', ['user_id' => $user->id]);

        return $user;
        // return redirect()->route('passenger.dashboard');
    }

    protected function createTechnician(array $input, int $userTypeId): User
    {
        Log::info('Creating technician user', ['user_type_id' => $userTypeId]);

        try {
            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
                'phone' => ['required', 'string', 'max:25', Rule::unique(User::class)],
                'gender' => ['required', 'in:Male,Female,Other'],
                'address' => ['required', 'string', 'max:255'],
                'region' => ['required', 'string', 'max:255'],
                'province' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'barangay' => ['required', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:20'],
                'password' => $this->passwordRules(),

                'expertise'=> ['nullable','string','in:Mechanical,Electrical,Battery'],
                'year_experience'=> ['required','integer'],
                'certificate_prc_no' => ['nullable','image','max:2048'],
                'professional_license' => ['nullable','image','max:2048'],
                'valid_id_type'=> ['required', new Enum(IdType::class)],
                'valid_id_number'=> ['required','string'],
                'front_valid_id_picture' => ['required', 'image', 'max:2048'],
                'back_valid_id_picture' => ['required', 'image', 'max:2048'],
                'cv_attachment' => ['required', 'image', 'max:2048'],
                'birth_date'=> ['required','date'],
                'age'=> ['required','integer','max:150'],
            ])->validate();

            Log::info('Technician validation passed', ['email' => $input['email']]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Technician validation failed', [
                'errors' => $e->errors(),
                'input_keys' => array_keys($input),
            ]);
            throw $e;
        }

        // Create User
        $user = User::create([
            'user_type_id' => $userTypeId,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'gender'=> $input['gender'],
            'address'=> $input['address'],
            'region' => $input['region'],
            'province' => $input['province'],
            'city' => $input['city'],
            'barangay' => $input['barangay'],
            'postal_code' => $input['postal_code'],
            'password' => Hash::make($input['password']),
        ]);

        // Handle file uploads
        $frontIdPath = $this->handleFileUpload($input['front_valid_id_picture'], $user->id, 'front_valid_id');
        $backIdPath = $this->handleFileUpload($input['back_valid_id_picture'], $user->id, 'back_valid_id');
        $cvPath = $this->handleFileUpload($input['cv_attachment'], $user->id, 'cv_attachment');
        $prcPath = isset($input['professional_license']) ? $this->handleFileUpload($input['professional_license'], $user->id, 'professional_license') : null;
        $certificatePath = isset($input['certificate_prc_no']) ? $this->handleFileUpload($input['certificate_prc_no'], $user->id, 'certificate_prc_no') : null;

        Log::info('File upload paths', [
            'front_id' => $frontIdPath,
            'back_id' => $backIdPath,
            'cv' => $cvPath,
            'prc' => $prcPath,
            'certificate' => $certificatePath,
        ]);

        // Create Technician record
        UserTechnician::create([
            'id' => $user->id,
            'status_id' => 6,
            'expertise'=> $input['expertise'],
            'year_experience'=> $input['year_experience'],
            'certificate_prc_no' => $certificatePath,
            'professional_license' => $prcPath,
            'valid_id_type'=> $input['valid_id_type'],
            'valid_id_number'=> $input['valid_id_number'],
            'front_valid_id_picture' => $frontIdPath,
            'back_valid_id_picture' => $backIdPath,
            'cv_attachment' => $cvPath,
            'birth_date' => $input['birth_date'],
            'age' => $input['age'],
        ]);

        return $user;
        // return redirect()->route('technician.dashboard');
    }

    protected function createOwner(array $input, int $userTypeId): User
    {
        // 1. Validation
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique(User::class),
                Rule::unique('franchises', 'email'),
                Rule::unique('branches', 'email')
            ],
            'phone' => [
                'required', 'string', 'max:20',
                Rule::unique(User::class),
                Rule::unique('franchises', 'phone'),
                Rule::unique('branches', 'phone')
            ],
            'password' => $this->passwordRules(),
            'gender' => ['required', 'string', Rule::in(['Male', 'Female', 'Other', 'Prefer not to say'])],
            'home_region' => ['required', 'string', 'max:255'],
            'home_province' => ['nullable', 'string', 'max:255'],
            'home_city' => ['required', 'string', 'max:255'],
            'home_barangay' => ['required', 'string', 'max:255'],
            'home_postal_code' => ['required', 'string', 'max:20'],
            'home_address' => ['required', 'string', 'max:255'],
            'franchise_name' => ['required', 'string', 'max:255'],
            'franchise_region' => ['required', 'string', 'max:255'],
            'franchise_province' => ['nullable', 'string', 'max:255'],
            'franchise_city' => ['required', 'string', 'max:255'],
            'franchise_barangay' => ['required', 'string', 'max:255'],
            'franchise_postal_code' => ['required', 'string', 'max:20'],
            'franchise_address' => ['required', 'string', 'max:255'],
            'payment_option_id' => ['required', Rule::exists('payment_options', 'id')],
            'valid_id_type' => ['required', 'string', Rule::in(['National ID', 'Passport', 'Driver License', 'Voter ID', 'Unified Multi-Purpose ID', 'TIN ID'])],
            'valid_id_number' => ['required', 'string', 'max:20', Rule::unique('user_owners', 'valid_id_number')],
            'front_valid_id_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'back_valid_id_picture' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'dti_certificate' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,docx,doc', 'max:5120'],
            'mayor_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,docx,doc', 'max:5120'],
            'proof_capital' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,docx,doc', 'max:5120'],
            'terms1' => ['required', 'accepted'],
            'terms2' => ['required', 'accepted'],
        ])->validate();

        Validator::make(
            ['user_type_id' => $userTypeId],
            ['user_type_id' => ['required', 'integer', Rule::exists('user_types', 'id')]]
        )->validate();

        // 2. Create Records in a Transaction
        $user = DB::transaction(function () use ($input, $userTypeId) {

            // 'pending' status ID is 6, default for owner and franchise
            $pendingStatusId = 6;

            // 2a. Store all files
            $frontIdPath = $input['front_valid_id_picture']->store('owner_ids', 'public');
            $backIdPath = $input['back_valid_id_picture']->store('owner_ids', 'public');
            $dtiPath = $input['dti_certificate']->store('franchise_documents', 'public');
            $mayorPermitPath = $input['mayor_permit']->store('franchise_documents', 'public');
            $proofAgreementPath = $input['proof_capital']->store('franchise_documents', 'public');

            // 2b. Create User
            $newUser = User::create([
                'user_type_id' => $userTypeId,
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
                'gender' => $input['gender'],
                'address' => $input['home_address'],
                'region' => $input['home_region'],
                'province' => $input['home_province'],
                'city' => $input['home_city'],
                'barangay' => $input['home_barangay'],
                'postal_code' => $input['home_postal_code'],
            ]);

            // 2c. Create UserOwner
            $userOwner = UserOwner::create([
                'id' => $newUser->id, // Use the new user's ID
                'status_id' => $pendingStatusId,
                'valid_id_type' => $input['valid_id_type'],
                'valid_id_number' => $input['valid_id_number'],
                'front_valid_id_picture' => $frontIdPath,
                'back_valid_id_picture' => $backIdPath,
            ]);

            // 2d. Create Franchise
            Franchise::create([
                'owner_id' => $userOwner->id, // Use the ID from the created owner
                'status_id' => $pendingStatusId,
                'payment_option_id' => $input['payment_option_id'],
                'name' => $input['franchise_name'],
                'email' => $input['email'], // Same as user
                'phone' => $input['phone'], // Same as user
                'address' => $input['franchise_address'],
                'region' => $input['franchise_region'],
                'province' => $input['franchise_province'],
                'city' => $input['franchise_city'],
                'barangay' => $input['franchise_barangay'],
                'postal_code' => $input['franchise_postal_code'],
                'dti_registrarion_attachment' => $dtiPath,
                'mayor_permit_attachment' => $mayorPermitPath,
                'proof_agreement_attachment' => $proofAgreementPath,
            ]);

            // Return the new user from the closure
            return $newUser;
        });

        // 3. Return the created user
        return $user;
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
