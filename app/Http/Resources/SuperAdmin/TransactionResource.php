<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'status_name' => $this->whenLoaded('status', $this->status->name),
            'service_type' => $this->service_type,
            'payment_option' => $this->payment_option,
            'amount' => (float) $this->amount,
            'driver_name' => $this->whenLoaded('driver', $this->driver->user->name),
            'created_at' => $this->created_at ? date('M d, Y h:i A', strtotime($this->created_at)) : null,
            'payment_date' => $this->payment_date ? date('M d, Y h:i A', strtotime($this->payment_date)) : null,
            'franchise_name' => $this->whenLoaded('franchise', fn () => $this->franchise?->name),
            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
            'payment_option' => $this->whenLoaded('paymentOption', $this->paymentOption->name),
            'notes' => $this->notes ?? null,
        ];

        return $data;
    }
}
