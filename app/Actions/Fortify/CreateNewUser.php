<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserDriver;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

    public function create(array $input): User
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

            // default:
            //     return $this->createDefault($input, $userTypeId);
        }
    }

    protected function createDriver(array $input, int $userTypeId): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string','15', Rule::unique(User::class)],
            'license_number' => ['required', 'string', 'max:50'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'user_type_id' => $userTypeId,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => Hash::make($input['password']),
        ]);

        // Create related profile for driver
        UserDriver::create([
            'user_id' => $user->id,
            'license_number' => $input['license_number'],
        ]);

        return $user;
    }

    protected function createPassenger(array $input, int $userTypeId): User
    {
        // Validator::make($input, [
        //     'name'=> ['required', 'string',''],
        //     'email'=> ['required', 'string','email','', Rule::Rule::unique(User::class)],
    }

    protected function createTechnician(array $input, int $userTypeId): User
    {

    }

    protected function createOwner(array $input, int $userTypeId): User
    {

    }
}
