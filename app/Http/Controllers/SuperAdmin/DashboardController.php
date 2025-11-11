<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\UserOwner;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(): Response
    {
        $franchises = Franchise::with([
            'owner.user:id,name',
            'status'
        ])->get();
        
        $franchises = $franchises->map(function ($franchise) {
            return [
                'id' => $franchise->id,
                'name' => $franchise->name,
                'email' => $franchise->email,
                'phone' => $franchise->phone,
                'status_name' => $franchise->status->name ?? null,
                'owner_id' => $franchise->owner_id,
                'owner_name' => $franchise->owner->user->name ?? null
            ];
        });

        return Inertia::render('super-admin/dashboard/Index', [
            'franchises' => $franchises,
        ]);
    }

    public function showFranchise(Franchise $franchise)
    {
        // Load all details for the modal and return as JSON
        $franchise->load(['status', 'owner.user', 'owner.status']);

        dd($franchise);
        return response()->json($franchise);
    }

    public function showOwner(UserOwner $owner)
    {
        // Load relationships and return as JSON
        $owner->load(['user', 'status']);
        
        dd($owner);
        return response()->json($owner);
    }

    public function acceptFranchise(Franchise $franchise)
    {
        // 1. Find the 'Active' status ID.
        // This is safer than hard-coding an ID.
        $activeStatus = Status::where('name', 'Active')->firstOrFail();

        // 2. Use a database transaction to ensure both updates succeed or fail together.
     
        DB::transaction(function () use ($franchise, $activeStatus) {
            // Update franchise status
            $franchise->status_id = $activeStatus->id;
            $franchise->save();

            // Update the owner's status
            // We can access the owner via the loaded relationship
            if ($franchise->owner) {
                $franchise->owner->status_id = $activeStatus->id;
                $franchise->owner->save();
            }
        });
    
        // 3. Redirect back with a success message
        return back()->with('success', 'Franchise and owner have been activated.');
    }
}