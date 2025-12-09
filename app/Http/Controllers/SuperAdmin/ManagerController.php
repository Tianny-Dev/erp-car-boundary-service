<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserManager;
use App\Models\Status;
use App\Models\UserType;
use App\Models\User;
use App\Http\Resources\SuperAdmin\ManagerResource;
use App\Http\Requests\SuperAdmin\StoreManagerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\Gender;
use App\Enums\IdType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    public function show(UserManager $manager)
    {
        // Load relationships and return as JSON
        $manager->loadMissing(['user:id,name,email,phone,gender,address,region,city,barangay,province,postal_code']);

        return new ManagerResource($manager);
    }

    public function create(): Response
    {
        return Inertia::render('super-admin/dashboard/ManagerCreate', [
            'genderOptions' => Gender::options(),
            'idTypeOptions' => IdType::options(),
        ]);
    }

    public function store(StoreManagerRequest $request)
    {
        DB::transaction(function () use ($request) {

            $pendingStatusId = Status::where('name', 'pending')->firstOrFail()->id;
            $managerTypeId = UserType::where('name', 'manager')->firstOrFail()->id;

            // Create Base User
            $user = User::create([
                'user_type_id' => $managerTypeId,
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'phone' => $request['phone'],
                'gender' => $request['gender'],
                'address' => $request['address'],
                'region' => $request['region'],
                'province' => $request['province'] ?? null,
                'city' => $request['city'],
                'barangay' => $request['barangay'],
                'postal_code' => $request['postal_code'],
            ]);

            // Upload ID Images
            $frontIdPath = $request->file('front_valid_id_picture')->store('manager_ids', 'public');
            $backIdPath = $request->file('back_valid_id_picture')->store('manager_ids', 'public');

            // Create User Manager Record
            $user->managerDetails()->create([
                'status_id' => $pendingStatusId,
                'valid_id_type' => $request['valid_id_type'],
                'valid_id_number' => $request['valid_id_number'],
                'front_valid_id_picture' => $frontIdPath,
                'back_valid_id_picture' => $backIdPath,
            ]);

        });

        return redirect(route('super-admin.dashboard'));
    }
}
