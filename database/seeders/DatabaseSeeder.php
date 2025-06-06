<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Car;
use App\Models\Image;
use App\Models\Rental;
use App\Models\ServiceHistory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin and employee users
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);
        
        // Create additional random users
        User::factory(3)->create();
        
        // Create cars
        Car::factory(15)->create()->each(function ($car) {
            // Add 1-3 images per car
            $imageCount = rand(1, 3);
            Image::factory($imageCount)->create([
                'car_id' => $car->id
            ]);
            
            // Add 0-3 service history records per car
            $serviceCount = rand(0, 3);
            if ($serviceCount > 0) {
                ServiceHistory::factory($serviceCount)->create([
                    'car_id' => $car->id
                ]);
            }
        });
        
        // Create rentals (some cars might get multiple rental records)
        Rental::factory(20)->create();
    }
}
