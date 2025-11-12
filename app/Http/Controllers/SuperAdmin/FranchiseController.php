<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuperAdmin\FranchiseResource;
use Illuminate\Http\Request;

class FranchiseController extends Controller
{
    public function show(Franchise $franchise)
    {
        $franchise->loadMissing(['status:id,name']);

       return new FranchiseResource($franchise);
    }

    public function accept(Franchise $franchise)
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
