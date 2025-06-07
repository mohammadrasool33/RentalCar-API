<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    protected $model = Rental::class;

    public function definition()
    {
        $car = Car::inRandomOrder()->first();
        
        if (!$car) {
            // Create a car if none exists
            $car = Car::factory()->create();
        }
        
        // Define duration types and their corresponding durations
        $durationTypes = ['daily', 'weekly', 'monthly'];
        $durationType = $this->faker->randomElement($durationTypes);
        
        // Set duration count based on type (1-7 days, 1-4 weeks, 1-3 months)
        $durationCount = $durationType === 'daily' ? $this->faker->numberBetween(1, 7) : 
                        ($durationType === 'weekly' ? $this->faker->numberBetween(1, 4) : 
                        $this->faker->numberBetween(1, 3));

        // Set price rate based on duration type
        $priceRate = $durationType === 'daily' ? ($car->price_per_day ?? 50) : 
                    ($durationType === 'weekly' ? ($car->price_per_week ?? 300) : 
                    ($car->price_per_month ?? 1000));
        
        // Calculate total price
        $totalPrice = $priceRate * $durationCount;
        
        // Random discount (0-15% of total price)
        $discountAmount = $this->faker->randomElement([0, 0, 0, $this->faker->numberBetween(5, (int)($totalPrice * 0.15))]);
        
        // Final price after discount
        $finalPrice = $totalPrice - $discountAmount;
        
        // Calculate rental dates
        $startDate = Carbon::now()->subDays($this->faker->numberBetween(1, 60));
        $endDate = clone $startDate;
        
        if ($durationType === 'daily') {
            $endDate->addDays($durationCount);
        } elseif ($durationType === 'weekly') {
            $endDate->addWeeks($durationCount);
        } else {
            $endDate->addMonths($durationCount);
        }
        
        // Determine if the rental is active or completed
        $isActive = $this->faker->boolean(30); // 30% chance of being active
        
        // If not active, set return details
        $returnDate = null;
        $mileageAtReturn = null;
        $additionalCharges = 0;
        $finalTotal = $finalPrice;
        $returnLocation = null;
        $returnServiceCheck = null;
        
        if (!$isActive) {
            $returnDate = Carbon::parse($endDate)->subDays($this->faker->numberBetween(0, 3));
            $mileageAtReturn = ($car->current_mileage ?? 15000) + $this->faker->numberBetween(50, 1000);
            $additionalCharges = $this->faker->randomElement([0, 0, 0, $this->faker->numberBetween(10, 100)]);
            $finalTotal = $finalPrice + $additionalCharges;
            $returnLocation = $this->faker->randomElement(['Main Office', 'Airport', 'Downtown', 'Hotel Pickup']);
            
            // Sometimes add return service check
            if ($this->faker->boolean(70)) {
                $returnServiceCheck = [
                    'oilCheck' => $this->faker->boolean(90),
                    'brakeCheck' => $this->faker->boolean(90),
                    'lightCheck' => $this->faker->boolean(90),
                    'serviceNotes' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
                ];
            }
        }

        $startingMileage = $car->current_mileage ?? 15000;
        
        // Create pickup service check data about 70% of the time
        $pickupServiceCheck = $this->faker->boolean(70) ? [
            'oilCheck' => $this->faker->boolean(90),
            'brakeCheck' => $this->faker->boolean(90),
            'lightCheck' => $this->faker->boolean(90),
            'serviceNotes' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
        ] : null;

        $pickupLocation = $this->faker->randomElement(['Main Office', 'Airport', 'Downtown', 'Hotel Pickup']);
        
        // Generate passport number for primary guarantor
        $passportNumber = $this->faker->regexify('[A-Z]{2}[0-9]{6}');

        return [
            'car_id' => $car->id,
            'duration_type' => $durationType,
            'duration_count' => $durationCount,
            'primary_guarantor_name' => $this->faker->name(),
            'primary_guarantor_phone' => $this->faker->phoneNumber(),
            'primary_guarantor_id_type' => 'passport',
            'primary_guarantor_id_number' => $passportNumber,
            'secondary_guarantor_name' => $this->faker->name(),
            'secondary_guarantor_phone' => $this->faker->phoneNumber(),
            'secondary_guarantor_id_type' => $this->faker->randomElement(['passport', 'national_id', 'driver_license']),
            'secondary_guarantor_id_number' => $this->faker->regexify('[A-Z0-9]{8}'),
            'pickup_location' => $pickupLocation,
            'return_location' => $returnLocation,
            'rental_start_date' => $startDate,
            'rental_end_date' => $endDate,
            'return_date' => $returnDate,
            'price_rate' => $priceRate,
            'total_price' => $totalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'additional_charges' => $additionalCharges,
            'final_total' => $finalTotal,
            'mileage_at_rental' => $startingMileage,
            'mileage_at_return' => $mileageAtReturn,
            'is_active' => $isActive,
            'is_paid' => $this->faker->boolean(80),
            'comments' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'pickup_service_check' => $pickupServiceCheck,
            'return_service_check' => $returnServiceCheck,
        ];
    }
} 