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
        $data = [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'payment_date' => $this->payment_date ? date('F j, Y', strtotime($this->payment_date)) : null,
            'amount' => $this->amount,
            'service_type' => $this->service_type,
            'franchise_name' => $this->whenLoaded('franchise', fn () => $this->franchise?->name),
            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
        ];

        return $data;
    }
}
