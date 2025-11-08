<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $statusModel = null;

        if ($user->driverDetails) {
            $statusModel = $user->driverDetails->status;
        } elseif ($user->technicianDetails) {
            $statusModel = $user->technicianDetails->status;
        } elseif ($user->passengerDetails) {
            $statusModel = $user->passengerDetails->status;
        } elseif ($user->managerDetails) {
            $statusModel = $user->managerDetails->status;
        }
        // elseif ($user->ownerDetails) {
        //     $statusModel = $user->ownerDetails->status;
        // }

        // If no related model or status, block access
        if (!$statusModel || $statusModel->name !== 'active') {
            return redirect()->route('inactive');
        }

        return $next($request);
    }
}
