<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueDatatableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // --- Handle Daily Period (check for 'amount' property) ---
        // use 'isset($this->amount)' as the differentiator.
        // Grouped queries will have 'total_amount' instead.
        if (isset($this->amount)) {
            return [
                'id' => $this->id,
                'invoice_no' => $this->invoice_no,
                'payment_date' => $this->payment_date ? date('F j, Y', strtotime($this->payment_date)) : null,
                'amount' => (float) $this->amount, // Use the 'amount' column
                'service_type' => $this->service_type,
                'franchise_name' => $this->whenLoaded('franchise', fn () => $this->franchise?->name),
                'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
            ];
        }

        // --- Handle Weekly/Monthly Period (it has 'total_amount') ---

        // Base data for grouped
        $data = [
            'id' => null, // No single ID
            'invoice_no' => 'N/A', // No single invoice
            'amount' => (float) $this->total_amount, // Use 'total_amount'
            'service_type' => $this->service_type,
            // Access the JOINed attributes directly.
            // They are on the model object because they were in the SELECT.
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

        return $data;
    }
}