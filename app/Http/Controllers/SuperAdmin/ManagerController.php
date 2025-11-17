<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserManager;
use App\Http\Resources\SuperAdmin\ManagerResource;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function show(UserManager $manager)
    {
        // Load relationships and return as JSON
        $manager->loadMissing(['user:id,name,email,phone,gender,address,region,city,barangay,province,postal_code']);

        return new ManagerResource($manager);
    }
}
