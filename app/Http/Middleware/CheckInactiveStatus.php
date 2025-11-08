<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInactiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Redirect guests to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Determine the status from related models
        $statusModel = $user->driverDetails?->status
                     ?? $user->technicianDetails?->status
                     ?? $user->managerDetails?->status
                     ?? $user->ownerDetails?->status;
                     // ?? $user->passengerDetails?->status; // optional

        // If user is active, redirect them to their dashboard
        if ($statusModel && $statusModel->name === 'active') {
            $route = match ($user->userType->name) {
                'driver' => route('driver.dashboard'),
                'passenger' => route('passenger.dashboard'),
                'technician' => route('technician.dashboard'),
                'owner' => route('owner.dashboard'),
                'super_admin' => route('super-admin.dashboard'),
                default => route('home'),
            };

            return $request->wantsJson()
                ? response()->json(['redirect' => $route])
                : redirect($route);
        }

        return $next($request);
    }
}
