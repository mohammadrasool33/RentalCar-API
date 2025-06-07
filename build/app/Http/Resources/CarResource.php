<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'description' => $this->description,
            'imageUrl' => $this->images->isNotEmpty() ? $this->images->first()->full_url : null,
            'pricePerDay' => $this->price_per_day,
            'pricePerWeek' => $this->price_per_week,
            'pricePerMonth' => $this->price_per_month,
            'currentMileage' => $this->current_mileage,
            'isAvailable' => $this->is_available,
            'year' => $this->year,
            'serviceHistory' => $this->serviceHistory->map(function ($history) {
                return [
                    'date' => $history->date,
                    'shopName' => $history->shop_name,
                    'services' => $history->services,
                    'notes' => $history->notes
                ];
            }),
            'images' => $this->images->map(function ($image) {
                return $image->full_url;
            }),
        ];
    }
}
