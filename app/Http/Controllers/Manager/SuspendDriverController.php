<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuspendDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branch = auth()->user()->managerDetails?->branches()->first();

        if (!$branch) {
            abort(404, 'Branch not found');
        }

        $statusQuery = ['active', 'inactive', 'suspended', 'retired'];

        $drivers = $branch->drivers()
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

        $statuses = Status::whereIn('name', ['active', 'suspended'])
            ->get(['id', 'name']);

        return Inertia::render('manager/suspend-driver/Index', [
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
        $branch = auth()->user()->managerDetails?->branches()->first();

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $driver = $branch->drivers()->find($id);
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
