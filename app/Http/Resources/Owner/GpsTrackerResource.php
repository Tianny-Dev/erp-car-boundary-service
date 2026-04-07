<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsTrackerResource extends JsonResource
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
            'username' => $this->whenLoaded('user', fn() => $this->user->username),
            'plate_number' => $this->whenLoaded('vehicles', function () {
                return $this->vehicles->first()?->plate_number;
            }),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'isOnline' => (bool) $this->is_online,
            'franchise_name' => $this->whenLoaded('franchises', function() {
                return $this->franchises->first()?->name;
            }),
        ];
    }
}
