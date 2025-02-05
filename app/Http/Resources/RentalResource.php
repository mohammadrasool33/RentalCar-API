<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
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
            'car_id' => $this->car_id,
            'plan' => $this->plan,
            'total_price' => $this->total_price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'km_before' => $this->km_before,
            'km_after' => $this->km_after,
            'status' => $this->status,
            'created_at' => $this->created_at,
            // Just return basic information for the car instead of CarResource
            'car' => [
                'id' => $this->car->id,
                'name' => $this->car->name,
                'price_daily' => $this->car->daily_price,
                'is_available' => $this->car->is_available,
                'brand' => $this->car->brand,
            ]
        ];
    }
}

