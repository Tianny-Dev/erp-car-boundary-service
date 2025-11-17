<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuspendDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            abort(404, 'Franchise not found');
        }

        $statusQuery = ['active', 'inactive', 'suspended', 'retired'];

        $drivers = $franchise->drivers()
            ->with(['user', 'status'])
            ->whereHas('status', function ($query) use ($statusQuery) {
                $query->whereIn('name', $statusQuery);
            })
            ->paginate(10)
            ->through(fn($driver) => [
                'id' => $driver->id,
                'name' => $driver->user?->name,
                'username' => $driver->user?->username,
                'email' => $driver->user?->email,
                'phone' => $driver->user?->phone,
                'region' => $driver->user?->region,
                'province' => $driver->user?->province,
                'city' => $driver->user?->city,
                'barangay' => $driver->user?->barangay,
                'status' => $driver->status?->name,
            ]);

        $statuses = Status::whereIn('name', ['pending', 'inactive', 'active', 'suspended', 'retired'])
            ->get(['id', 'name']);

        return Inertia::render('owner/suspend-driver/Index', [
            'drivers' => $drivers,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            return response()->json(['message' => 'Franchise not found'], 404);
        }

        $driver = $franchise->drivers()->find($id);
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $driver->update([
            'status_id' => $request->status_id
        ]);

        return redirect()->back()->with('success', 'Driver status updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
