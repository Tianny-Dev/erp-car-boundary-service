<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Http\Resources\SuperAdmin\BranchResource;
use App\Http\Requests\SuperAdmin\StoreBranchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\Gender;
use App\Enums\IdType;
use App\Models\PaymentOption;
use App\Models\Status;
use App\Models\User;
use App\Models\UserType;


class BranchController extends Controller
{
    public function show(Branch $branch)
    {
        $branch->loadMissing(['status:id,name']);

       return new BranchResource($branch);
    }

    public function create(): Response
    {
        return Inertia::render('super-admin/dashboard/BranchCreate', [
            'paymentOptions' => PaymentOption::select('id', 'name')->get(),
            'genderOptions' => Gender::options(),
            'idTypeOptions' => IdType::options(),
        ]);
    }

    public function store(StoreBranchRequest $request)
    {
        dd($request->all());
        DB::transaction(function () use ($request) {
            // 1. Get IDs for Status and UserType
            $activeStatusId = Status::where('name', 'active')->firstOrFail()->id;
            
            $managerId = null;

            // 2. Create Manager if requested
            if ($request->boolean('has_manager')) {
                $managerTypeId = UserType::where('name', 'manager')->firstOrFail()->id;
                $managerData = $request->input('manager');

                // Create Base User
                $user = User::create([
                    'user_type_id' => $managerTypeId,
                    'name' => $managerData['first_name'] . ' ' . $managerData['last_name'],
                    'email' => $managerData['email'],
                    'password' => Hash::make($managerData['password']),
                    'phone' => $managerData['phone'],
                    'gender' => $managerData['gender'],
                    'address' => $managerData['address'],
                    'region' => $managerData['region'],
                    'province' => $managerData['province'],
                    'city' => $managerData['city'],
                    'barangay' => $managerData['barangay'],
                    'postal_code' => $managerData['postal_code'],
                ]);

                // Upload ID Images
                $frontIdPath = $request->file('manager.front_valid_id_picture')->store('managers/ids', 'public');
                $backIdPath = $request->file('manager.back_valid_id_picture')->store('managers/ids', 'public');

                // Create User Manager Record
                $user->userManager()->create([
                    'status_id' => $activeStatusId, // Set to Active
                    'valid_id_type' => $managerData['valid_id_type'],
                    'valid_id_number' => $managerData['valid_id_number'],
                    'front_valid_id_picture' => $frontIdPath,
                    'back_valid_id_picture' => $backIdPath,
                ]);

                $managerId = $user->id;
            }

            // 3. Upload Branch Files
            $dtiPath = $request->file('dti_registration_attachment')->store('branches/documents', 'public');
            $mayorPath = $request->file('mayor_permit_attachment')->store('branches/documents', 'public');
            $proofPath = $request->file('proof_agreement_attachment')->store('branches/documents', 'public');

            // 4. Create Branch
            Branch::create([
                'manager_id' => $managerId,
                'status_id' => $activeStatusId, // Set to Active
                'payment_option_id' => $request->payment_option_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'barangay' => $request->barangay,
                'postal_code' => $request->postal_code,
                'dti_registration_attachment' => $dtiPath,
                'mayor_permit_attachment' => $mayorPath,
                'proof_agreement_attachment' => $proofPath,
            ]);
        });

        return redirect(route('super-admin.dashboard'))->with('success', 'Branch created successfully!');
    }
}
