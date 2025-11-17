<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'plate_number' => $this->plate_number,
            'vin' => $this->vin,
            'brand' => $this->brand,
            'model'=> $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'status' => $this->status->name
        ];
    }
}
