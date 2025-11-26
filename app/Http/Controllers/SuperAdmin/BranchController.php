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
use Illuminate\Validation\Rule;
use App\Enums\Gender;
use App\Enums\IdType;
use App\Models\PaymentOption;
use App\Models\Status;
use App\Models\User;
use App\Models\UserType;


class BranchController extends Controller
{
    public function verification(Request $request): Response
    {
        // 1. Validate all filters
        $validated = $request->validate([
            'tab' => ['sometimes', 'string', Rule::in(['franchise', 'branch'])],
            'franchise' => ['sometimes', 'nullable', 'string'],
            'branch' => ['sometimes', 'nullable', 'string'],
            'service' => ['sometimes', 'string', Rule::in(['Trips', 'Boundary'])],
            'period' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        // 2. Set defaults
        $filters = [
            'tab' => $validated['tab'] ?? 'franchise',
            'franchise' => $validated['franchise'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'service' => $validated['service'] ?? 'Trips',
            'period' => $validated['period'] ?? 'daily',
        ];

        // 3. Build and execute query
        $query = $this->buildBaseQuery($filters);
        $revenues = $this->applyPeriodGrouping($query, $filters['period'], $filters['tab']);

        // 4. Return all data to Inertia
        return Inertia::render('super-admin/finance/RevenueIndex', [
            'revenues' => RevenueDatatableResource::collection($revenues),
            'franchises' => fn () => Franchise::select('id', 'name')->get(),
            'branches' => fn () => Branch::select('id', 'name')->get(),
            'filters' => $filters,
        ]);
        
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters, ?int $year = null, ?array $months = null): Builder
    {
        $query = Revenue::query()
            // Base filters: "paid" status and non-null payment_date
            ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
            ->whereNotNull('payment_date')
            ->where('service_type', $filters['service']);

            // --- Apply date constraints for export only ---
            if ($year) {
                $query->whereYear('payment_date', $year);
            }
            if (! empty($months)) {
                $query->whereIn(DB::raw('MONTH(payment_date)'), $months);
            }

        // Apply tab-specific filtering
        if ($filters['tab'] === 'franchise') {
            $query->whereNotNull('franchise_id')
                ->when($filters['franchise'], fn ($q) => $q->where('franchise_id', $filters['franchise']));

        } elseif ($filters['tab'] === 'branch') {
            $query->whereNotNull('branch_id')
                ->when($filters['branch'], fn ($q) => $q->where('branch_id', $filters['branch']));
        }

        return $query;
    }

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
                    'name' => $managerData['name'],
                    'email' => $managerData['email'],
                    'password' => Hash::make($managerData['password']),
                    'phone' => $managerData['phone'],
                    'gender' => $managerData['gender'],
                    'address' => $managerData['address'],
                    'region' => $managerData['region'],
                    'province' => $managerData['province'] ?? null,
                    'city' => $managerData['city'],
                    'barangay' => $managerData['barangay'],
                    'postal_code' => $managerData['postal_code'],
                ]);

                // Upload ID Images
                $frontIdPath = $request->file('manager.front_valid_id_picture')->store('owner_ids', 'public');
                $backIdPath = $request->file('manager.back_valid_id_picture')->store('owner_ids', 'public');

                // Create User Manager Record
                $user->managerDetails()->create([
                    'status_id' => $activeStatusId, // Set to Active
                    'valid_id_type' => $managerData['valid_id_type'],
                    'valid_id_number' => $managerData['valid_id_number'],
                    'front_valid_id_picture' => $frontIdPath,
                    'back_valid_id_picture' => $backIdPath,
                ]);

                $managerId = $user->id;
            }

            // 3. Upload Branch Files
            $dtiPath = $request->file('dti_certificate')->store('branch_documents', 'public');
            $mayorPath = $request->file('mayor_permit')->store('branch_documents', 'public');
            $proofPath = $request->file('proof_capital')->store('branch_documents', 'public');

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
                'province' => $request->province ?? null,
                'city' => $request->city,
                'barangay' => $request->barangay,
                'postal_code' => $request->postal_code,
                'dti_registration_attachment' => $dtiPath,
                'mayor_permit_attachment' => $mayorPath,
                'proof_agreement_attachment' => $proofPath,
            ]);
        });

        return redirect(route('super-admin.dashboard'));
    }
}
