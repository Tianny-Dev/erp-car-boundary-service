<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->name,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'address' => $this->user->address,
            'region' => $this->user->region,
            'city' => $this->user->city,
            'barangay' => $this->user->barangay, 
            'province' => $this->user->province ? $this->user->province : null,
            'postal_code' => $this->user->postal_code,
            'license_number' => $this->license_number,
            'license_expiry' => $this->license_expiry,
            'front_license_picture' => $this->front_license_picture ? Storage::url($this->front_license_picture) : null,
            'back_license_picture' => $this->back_license_picture ? Storage::url($this->back_license_picture) : null,
            'nbi_clearance' => $this->nbi_clearance ? Storage::url($this->nbi_clearance) : null,
            'selfie_picture' => $this->selfie_picture ? Storage::url($this->selfie_picture) : null,
            'shift' => $this->shift,
            'hire_date' => $this->hire_date ? $this->hire_date->format('F d, Y') : null,
            'created_at' => $this->created_at->format('F d, Y'),
        ];
    }
}
