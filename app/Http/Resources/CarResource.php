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
          'id'=>$this->id,
            'name'=>$this->name,
            'price_daily'=>$this->daily_price,
            'price_weekly'=>$this->weekly_price,
            'price_monthly'=>$this->monthly_price,
            'is_available'=>$this->is_available,
            'year'=>$this->year,
            'brand'=>$this->brand,
            'rentals'=>RentalResource::collection($this->rentals),
            'images' => $this->images->map(function ($image) {
                return $image->full_url;  // Use accessor to return the full image URL
            }),

        ];
    }
}
