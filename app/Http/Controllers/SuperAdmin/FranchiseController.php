<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuperAdmin\FranchiseResource;
use Illuminate\Http\Request;
use App\Notifications\AcceptFranchiseApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FranchiseController extends Controller
{
    public function show(Franchise $franchise)
    {
        $franchise->loadMissing(['status:id,name']);

       return new FranchiseResource($franchise);
    }

    public function accept(Franchise $franchise)
    {
        $activeStatus = Status::where('name', 'active')->firstOrFail();

        DB::transaction(function () use ($franchise, $activeStatus) {
            // Update franchise status
            $franchise->status_id = $activeStatus->id;
            $franchise->save();

            // Update the owner's status
            $franchise->owner->status_id = $activeStatus->id;
            $franchise->owner->save();
            
            $franchise->owner->user->notify(new AcceptFranchiseApplication());
        });
        
        return back();
    }

    public function uploadContract(Request $request, Franchise $franchise)
    {
        $request->validate([
            'contract_attachment' => 'required|file|mimes:pdf,doc,docx,odt,rtf,txt,xls,xlsx|max:10240', 
        ]);

        $file = $request->file('contract_attachment');

        if ($franchise->contract_attachment) {
            Storage::delete($franchise->contract_attachment);
        }

        $path = $file->store('franchise_contracts', 'public');

        $franchise->contract_attachment = $path;
        $franchise->save();

        return back();
    }
}
