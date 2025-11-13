<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Http\Resources\SuperAdmin\BranchResource;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function show(Branch $branch)
    {
        $branch->loadMissing(['status:id,name']);

       return new BranchResource($branch);
    }
}
