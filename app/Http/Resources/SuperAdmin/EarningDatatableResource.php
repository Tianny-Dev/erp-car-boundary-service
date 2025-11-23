<?php

namespace App\Http\Resources\SuperAdmin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningDatatableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Standard Fields
        $data = [
            'driver_name'    => $this->driver_name,
            'franchise_name' => $this->franchise_name ?? null,
            'branch_name'    => $this->branch_name ?? null,
            'total_amount'   => (float) $this->total_amount,
            'driver_earning' => (float) $this->driver_earning,
            'payment_date'   => 'N/A',
            'fees'           => [], // We will put dynamic fees here
        ];

        // 2. Fetch all possible fee slugs (cached or passed ideally, but this works)
        // Note: For better performance, pass the types via constructor
        $attributes = $this->resource->getAttributes();
        
        foreach ($attributes as $key => $value) {
            // Check if attribute starts with 'total_' and isn't 'total_amount'
            if (str_starts_with($key, 'total_') && $key !== 'total_amount') {
                // Remove 'total_' prefix to get the slug (e.g. 'total_markup_fee' -> 'markup_fee')
                $slug = substr($key, 6); 
                $data['fees'][$slug] = (float) $value;
            }
        }

        // 3. Date Formatting (Same as before)
        if (isset($this->month_name)) {
            $data['payment_date'] = $this->month_name;
        } elseif (isset($this->week_start)) {
            $start = Carbon::parse($this->week_start);
            $end = Carbon::parse($this->week_end);
            $data['payment_date'] = ($start->month === $end->month)
                ? $start->format('M j') . ' - ' . $end->format('j, Y')
                : $start->format('M j') . ' - ' . $end->format('M j, Y');
        } else {
            $data['payment_date'] = Carbon::parse($this->payment_date)->format('M j, Y');
        }

        return $data;
    }
}
