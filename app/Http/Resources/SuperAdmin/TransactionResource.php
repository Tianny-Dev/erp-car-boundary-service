<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Expense;
use Carbon\Carbon;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'             => $this->id,
            'invoice_no'     => $this->invoice_no ?? 'N/A',
            'status_name'    => $this->whenLoaded('status', fn() => $this->status->name),
            'service_type'   => $this->service_type ?? 'N/A',
            'amount'         => (float) $this->amount,
            'created_at'     => $this->created_at ? $this->created_at->format('M d, Y h:i A') : null,
            'payment_date'   => $this->payment_date ? Carbon::parse($this->payment_date)->format('M d, Y h:i A') : 'N/A',
            
            // Cleaned up relationship
            'payment_option' => $this->whenLoaded('paymentOption', fn() => $this->paymentOption->name) ?? 'N/A',
            'franchise_name' => $this->whenLoaded('franchise', fn() => $this->franchise->name) ?? 'N/A',
            'notes'          => $this->notes,

            // Revenue specific
            'driver_username' => $this->driver?->user?->username ?? 'N/A',
        ];
    }
}
