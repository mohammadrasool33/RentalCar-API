<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        $car = Car::inRandomOrder()->first();
        
        if (!$car) {
            // Create a car if none exists
            $car = Car::factory()->create();
        }
        
        // Sample image paths to simulate stored images
        $sampleImages = [
            'cars/car_default_1.jpg',
            'cars/car_default_2.jpg',
            'cars/car_default_3.jpg',
            'cars/car_default_4.jpg',
            'cars/car_default_5.jpg',
            'cars/sedan_1.jpg',
            'cars/suv_1.jpg',
            'cars/truck_1.jpg',
            'cars/luxury_1.jpg',
        ];
        
        return [
            'car_id' => $car->id,
            'path' => $this->faker->randomElement($sampleImages),
        ];
    }
} 