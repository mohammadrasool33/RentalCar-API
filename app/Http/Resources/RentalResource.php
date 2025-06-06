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
            'duration_type' => $this->duration_type,
            'duration_count' => $this->duration_count,
            'renter_name' => $this->renter_name,
            'renter_phone' => $this->renter_phone,
            'passport_number' => $this->passport_number,
            'pickup_location' => $this->pickup_location,
            'return_location' => $this->return_location,
            'rental_start_date' => $this->rental_start_date,
            'rental_end_date' => $this->rental_end_date,
            'return_date' => $this->return_date,
            'price_rate' => $this->price_rate,
            'total_price' => $this->total_price,
            'discount_amount' => $this->discount_amount,
            'final_price' => $this->final_price,
            'additional_charges' => $this->additional_charges,
            'final_total' => $this->final_total,
            'mileage_at_rental' => $this->mileage_at_rental,
            'mileage_at_return' => $this->mileage_at_return,
            'is_active' => $this->is_active,
            'is_paid' => $this->is_paid,
            'created_at' => $this->created_at,
            // Just return basic information for the car
            'car' => [
                'id' => $this->car->id,
                'name' => $this->car->name,
                'price_daily' => $this->car->price_per_day,
                'price_weekly' => $this->car->price_per_week,
                'price_monthly' => $this->car->price_per_month,
                'is_available' => $this->car->is_available,
                'brand' => $this->car->brand,
                'model' => $this->car->model,
            ]
        ];
    }
}

