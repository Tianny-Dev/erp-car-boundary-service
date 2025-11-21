<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Revenue;
use App\Models\User;
use App\Models\PercentageType;
use Illuminate\Support\Facades\DB;

class DriverDetailsController extends Controller
{
 /**
     * Shows the detailed transactions for a specific driver and period.
     */
 public function show(Request $request)
 {

 $validated = $request->validate([
'driver_id' => ['required', 'string', 'exists:users,id'],
'payment_date' => ['required', 'string'],
 'period' => ['required', 'string', 'in:daily,weekly,monthly'],

 'tab' => ['sometimes', 'string', 'in:franchise,branch'],
 'franchise' => ['sometimes', 'nullable', 'string'],
'branch' => ['sometimes', 'nullable', 'string'],
 ]);

 $driverId = $validated['driver_id'];
$period = $validated['period'];
 $paymentDate = $validated['payment_date'];


 [$startDate, $endDate] = $this->parseDateRange($paymentDate, $period);

 $query = Revenue::query()
->with(['breakdowns.percentageType', 'driver', 'franchise', 'branch'])
 ->where('driver_id', $driverId)
 ->whereHas('status', fn ($q) => $q->where('name', 'paid'))
 ->whereBetween('payment_date', [$startDate, $endDate])
 ->where('service_type', 'Trips')
 ->orderBy('payment_date', 'asc');

 if (isset($validated['tab']) && $validated['tab'] === 'franchise' && !empty($validated['franchise']) && $validated['franchise'] !== 'all') {
 $query->where('franchise_id', $validated['franchise']);
 } elseif (isset($validated['tab']) && $validated['tab'] === 'branch' && !empty($validated['branch']) && $validated['branch'] !== 'all') {
 $query->where('branch_id', $validated['branch']);
 }

 $details = $query->get();

$driver = User::find($driverId, ['id', 'name']);


$breakdownTypes = PercentageType::pluck('name')->map(fn($name) => ucwords(str_replace('_', ' ', $name)))->toArray();



 return Inertia::render('super-admin/driver-report/Details', [
 'driver' => $driver,
'periodLabel' => $paymentDate,
 'details' => $details,
 'breakdownTypes' => $breakdownTypes,
 'filters' => $validated,
 ]);
 }

/**
     * Parses the formatted payment_date string into a database-queryable date range.
     */
private function parseDateRange(string $dateString, string $period): array
{
if ($period === 'daily') {

try {
                // Try the format: FullMonth Day, Year
$date = Carbon::createFromFormat('F j, Y', $dateString);
 } catch (\Exception $e) {
                // Fallback to general parsing if explicit format fails
$date = Carbon::parse($dateString);
 }
 return [$date->startOfDay(), $date->endOfDay()];
 }

 if ($period === 'monthly') {
 try {

 $date = Carbon::createFromFormat('F Y', $dateString);
 } catch (\Exception $e) {

 $date = Carbon::parse($dateString);
}
 return [$date->startOfMonth(), $date->endOfMonth()];
}

if ($period === 'weekly') {

 if (preg_match('/^(.+)\s-\s(.+)$/', $dateString, $matches)) { // Added capture group for end date
$parts = explode(' - ', $dateString);
 $start = Carbon::parse($parts[0]);
 $end = Carbon::parse(end($parts));
 return [$start->startOfDay(), $end->endOfDay()];
 }


 $date = Carbon::parse($dateString);
 return [$date->startOfWeek(), $date->endOfWeek()];
}

$date = Carbon::parse($dateString);
 return [$date->startOfDay(), $date->endOfDay()];
 }
}
