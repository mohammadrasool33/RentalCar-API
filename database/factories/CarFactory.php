<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition()
    {
        $brands = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes', 'Audi', 'Tesla', 'Hyundai', 'Nissan', 'Volkswagen'];
        $brand = $this->faker->randomElement($brands);

        $models = [
            'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Prius', 'Highlander'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey'],
            'Ford' => ['F-150', 'Escape', 'Explorer', 'Focus', 'Mustang'],
            'BMW' => ['3 Series', '5 Series', 'X3', 'X5', 'i8'],
            'Mercedes' => ['C-Class', 'E-Class', 'GLC', 'S-Class', 'AMG GT'],
            'Audi' => ['A4', 'A6', 'Q5', 'Q7', 'TT'],
            'Tesla' => ['Model 3', 'Model S', 'Model X', 'Model Y', 'Cybertruck'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade'],
            'Nissan' => ['Altima', 'Maxima', 'Rogue', 'Murano', 'Pathfinder'],
            'Volkswagen' => ['Golf', 'Jetta', 'Passat', 'Tiguan', 'Atlas'],
        ];

        $model = $this->faker->randomElement($models[$brand]);
        $year = $this->faker->numberBetween(2015, 2023);
        $dailyPrice = $this->faker->numberBetween(30, 100);

        return [
            'name' => "$brand $model $year",
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'description' => $this->faker->paragraph(2),
            'price_per_day' => $dailyPrice,
            'price_per_week' => $dailyPrice * 7 * 0.85, // 15% discount for weekly
            'price_per_month' => $dailyPrice * 30 * 0.7, // 30% discount for monthly
            'current_mileage' => $this->faker->numberBetween(5000, 80000),
            'is_available' => $this->faker->boolean(80), // 80% chance of being available
        ];
    }
} 