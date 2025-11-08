<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\UserDriver;
use Illuminate\Http\RedirectResponse;

class PendingDriverController extends Controller
{
    /**
     * Accept a pending driver.
     */
    public function accept(UserDriver $userDriver): RedirectResponse
    {
        // Find the 'active' status ID
        $activeStatusId = Status::where('name', 'active')->firstOrFail()->id;

        // Update the driver
        $userDriver->update([
            'status_id' => $activeStatusId,
            'is_verified' => true,
            'hire_date' => now(), // Optional: set hire date
        ]);

        return redirect()->back()->with('success', 'Driver has been accepted.');
    }

    /**
     * Deny a pending driver.
     */
    public function deny(UserDriver $userDriver): RedirectResponse
    {
        // For "deny", you could delete the record or set a "rejected" status.
        // For this example, we'll delete the driver record.
        $userDriver->delete();

        return redirect()->back()->with('success', 'Driver has been denied and removed.');
    }
}