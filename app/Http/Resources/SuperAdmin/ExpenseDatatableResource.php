<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseDatatableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Base data for grouped
        $data = [
            'amount' => (float) $this->total_amount, // Use 'total_amount'
            'franchise_name' => $this->franchise_name ?? null,
            'branch_name' => $this->branch_name ?? null,
            'payment_date' => 'N/A', // Default
            
        ];

        // Handle Monthly
        if (isset($this->month_name)) {
            $data['payment_date'] = $this->month_name; // e.g., "November 2025"
        }
        // Handle Weekly
        elseif (isset($this->week_start)) {
            $startF = date('F', strtotime($this->week_start));
            $startJ = date('j', strtotime($this->week_start));
            $endF = date('F', strtotime($this->week_end));
            $endJ = date('j', strtotime($this->week_end));
            $endY = date('Y', strtotime($this->week_end));

            $dateRange = $startF === $endF
                ? "{$startF} {$startJ} - {$endJ}, {$endY}"
                : date('M j', strtotime($this->week_start)).' - '.date('M j, Y', strtotime($this->week_end));

            $data['payment_date'] = $dateRange;
        }
        elseif (isset($this->payment_date)) {
            $data['payment_date'] = date('F j, Y', strtotime($this->payment_date));
        }

        return $data;
    }
}
