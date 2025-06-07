<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\ServiceHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceHistoryFactory extends Factory
{
    protected $model = ServiceHistory::class;

    public function definition()
    {
        $car = Car::inRandomOrder()->first();
        
        if (!$car) {
            // Create a car if none exists
            $car = Car::factory()->create();
        }
        
        $shopNames = [
            'AutoCare Center',
            'Quick Service',
            'Top Mechanics',
            'Premium Auto Service',
            'Car Maintenance Pros',
            'Speedy Auto Repair',
            'Quality Car Care',
            'Auto Experts',
            'Master Mechanics'
        ];
        
        $possibleServices = [
            'oilChange',
            'tireRotation',
            'brakeCheck',
            'brakeReplacement',
            'engineDiagnostic',
            'airFilterReplacement',
            'alignmentCheck',
            'fluidTopUp',
            'batteryTest',
            'lightBulbReplacement'
        ];
        
        // Select 1-4 random services
        $services = $this->faker->randomElements(
            $possibleServices, 
            $this->faker->numberBetween(1, 4)
        );
        
        return [
            'car_id' => $car->id,
            'date' => Carbon::now()->subDays($this->faker->numberBetween(1, 180)),
            'shop_name' => $this->faker->randomElement($shopNames),
            'services' => $services,
            'notes' => $this->faker->boolean(70) ? $this->faker->paragraph(1) : null,
        ];
    }
} 