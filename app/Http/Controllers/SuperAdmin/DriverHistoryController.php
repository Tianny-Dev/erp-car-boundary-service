<?php


namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\DriverResource;
use App\Models\Franchise;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\UserDriver;

class DriverHistoryController extends Controller
{
    public function index(): Response
    {
        $franchises = Franchise::all();

        return Inertia::render('super-admin/driverlist/Index', [
            'franchises' => $franchises,
        ]);
    }

    public function show($id)
    {
        // get drivers where franchise_id = $id
        $drivers = UserDriver::whereIn('id', function ($query) use ($id) {
            $query->select('user_driver_id')
                ->from('franchise_user_driver')
                ->where('franchise_id', $id);
        })
        ->with(['user:id,name,email,phone,address']) // your relationship
        ->get();

        return Inertia::render('super-admin/driverlist/Show', [
            'drivers' => $drivers,
            'franchiseId' => $id,
        ]);
    }


}
