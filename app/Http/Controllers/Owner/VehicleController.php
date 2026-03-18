<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        if (!$franchise) {
            abort(404, 'Franchise not found');
        }

        $vehicles = $franchise->vehicles()
            ->with('status')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'vin' => $vehicle->vin,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'color' => $vehicle->color,
                    'year' => $vehicle->year,
                    'status_id' => $vehicle->status_id,
                    'status_name' => $vehicle->status?->name,
                    'or_cr' => $vehicle->or_cr
                        ? asset('storage/vehicle_documents/' . $vehicle->or_cr)
                        : null,
                ];
            });

        return Inertia::render('owner/vehicles/Index', [
            'vehicles' => $vehicles,
            'inventories' => Inventory::where('franchise_id', $franchise->id)->get(),
        ]);
    }

    public function storeMaintenance(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'inventory_id' => 'required|exists:inventories,id',
            'quantity' => 'required|integer|min:1',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after_or_equal:maintenance_date',
            'description' => 'nullable|string',
        ]);

        $franchise = auth()->user()->ownerDetails?->franchises()->first();

        // 1. Find the vehicle and the inventory item
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $inventory = Inventory::where('id', $request->inventory_id)
                            ->where('franchise_id', $franchise->id)
                            ->firstOrFail();

        // Check stock
        if ($inventory->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Insufficient stock in inventory.');
        }

        // 2. Create the maintenance record
        Maintenance::create([
            'vehicle_id'            => $request->vehicle_id,
            'inventory_id'          => $request->inventory_id,
            'quantity'              => $request->quantity,
            'maintenance_date'      => $request->maintenance_date,
            'next_maintenance_date' => $request->next_maintenance_date,
            'description'           => $request->description,
        ]);

        // 3. Update the vehicle status to 5 (Maintenance)
        $vehicle->update([
            'status_id' => 5
        ]);

        // 4. Subtract stock
        $inventory->subtractStock($request->quantity);

        return redirect()->back()->with('success', 'Maintenance record saved and vehicle status updated!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:255|unique:vehicles',
            'vin'          => 'required|string|max:255|unique:vehicles',
            'brand'        => 'required|string|max:255',
            'model'        => 'required|string|max:255',
            'color'        => 'required|string|max:255',
            'year'         => 'required|integer|digits:4|between:1900,' . (date('Y') + 1),
            'status_id'    => 'required|exists:statuses,id',
            'or_cr'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        if (!$franchise) {
            return redirect()->back()->with('error', 'No franchise found.');
        }

        $vehicle = new Vehicle($request->except('or_cr'));
        $vehicle->franchise_id = $franchise->id;

        if ($request->hasFile('or_cr')) {
            $file = $request->file('or_cr');
            $filename = time() . '_or_cr_' . str_replace(' ', '_', $request->plate_number) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('vehicle_documents', $filename, 'public');
            $vehicle->or_cr = $filename;
        }

        $vehicle->save();

        return redirect()->back()->with('success', 'Vehicle created!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // Security check: Ensure vehicle belongs to the authenticated user's franchise
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        if ($vehicle->franchise_id !== $franchise->id) {
            abort(403);
        }

        $request->validate([
            'plate_number' => 'required|string|max:255|unique:vehicles,plate_number,' . $vehicle->id,
            'vin'          => 'required|string|max:255|unique:vehicles,vin,' . $vehicle->id,
            'brand'        => 'required|string|max:255',
            'model'        => 'required|string|max:255',
            'color'        => 'required|string|max:255',
            'year'         => 'required|integer|digits:4|between:1900,' . (date('Y') + 1),
            'status_id'    => 'required|exists:statuses,id',
            'or_cr'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('or_cr')) {
            if ($vehicle->or_cr) {
                Storage::disk('public')->delete('vehicle_documents/' . $vehicle->or_cr);
            }

            $file = $request->file('or_cr');
            $filename = time() . '_or_cr_' . $vehicle->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('vehicle_documents', $filename, 'public');
            $vehicle->or_cr = $filename;
        }

        $vehicle->update($request->only([
            'plate_number', 'vin', 'brand', 'model', 'color', 'year', 'status_id'
        ]));

        return redirect()->back()->with('success', 'Vehicle updated!');
    }

    /**
     * Get maintenance history for the vehicle.
     */
    public function maintenanceHistory(Vehicle $vehicle)
    {
        // Security check: Ensure owner owns this vehicle
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        if ($vehicle->franchise_id !== $franchise?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = $vehicle->maintenances()
            ->with('inventory')
            ->orderByDesc('maintenance_date')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'inventory_name' => $item->inventory?->name ?? 'General Maintenance',
                    'category' => $item->inventory?->category ?? 'Other',
                    'maintenance_date' => $item->maintenance_date->format('M d, Y'),
                    'next_maintenance_date' => $item->next_maintenance_date
                        ? $item->next_maintenance_date->format('M d, Y')
                        : 'N/A',
                    'specification' => $item->inventory?->specification ?? 'N/A',
                    'description' => $item->description,
                ];
            });

        return response()->json($history);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $franchise = auth()->user()->ownerDetails?->franchises()->first();
        if ($vehicle->franchise_id !== $franchise->id) {
            abort(403);
        }

        if ($vehicle->or_cr) {
            Storage::disk('public')->delete('vehicle_documents/' . $vehicle->or_cr);
        }

        $vehicle->delete();
        return redirect()->back()->with('success', 'Vehicle deleted!');
    }
}
