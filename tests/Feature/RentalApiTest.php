<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RentalApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $car;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user
        $this->user = User::factory()->create();
        
        // Create cars
        $this->car = Car::factory()->create([
            'is_available' => true,
            'price_per_day' => 50,
            'price_per_week' => 300,
            'price_per_month' => 1200
        ]);
        
        $this->secondCar = Car::factory()->create([
            'is_available' => true,
            'price_per_day' => 70,
            'price_per_week' => 420,
            'price_per_month' => 1800
        ]);
    }

    /** @test */
    public function it_can_create_rental_with_passport()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/rentals', [
                'car_id' => $this->car->id,
                'duration_type' => 'daily',
                'duration_count' => 3,
                'primary_guarantor_name' => 'John Smith',
                'primary_guarantor_phone' => '+123456789',
                'passport' => 'AB123456',
                'secondary_guarantor_name' => 'Jane Doe',
                'secondary_guarantor_phone' => '+987654321',
                'secondary_guarantor_id_type' => 'driver_license',
                'secondary_guarantor_id_number' => 'DL987654',
                'pickup_location' => 'Airport Terminal 1',
                'mileage_at_rental' => 12500,
                'is_paid' => true
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.passport', 'AB123456')
            ->assertJsonPath('data.primary_guarantor_id_type', 'passport')
            ->assertJsonPath('data.primary_guarantor_id_number', 'AB123456')
            ->assertJsonPath('data.secondary_guarantor_name', 'Jane Doe');
            
        // Check that car is now unavailable
        $this->assertDatabaseHas('cars', [
            'id' => $this->car->id,
            'is_available' => false
        ]);
    }

    /** @test */
    public function it_can_create_rental_with_legacy_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/rentals', [
                'car_id' => $this->secondCar->id,
                'duration_type' => 'weekly',
                'duration_count' => 2,
                'renter_name' => 'Mike Johnson', 
                'renter_phone' => '+1122334455',
                'passport_number' => 'CD654321',
                'pickup_location' => 'Downtown Office',
                'mileage_at_rental' => 8700,
                'is_paid' => false
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.passport', 'CD654321')
            ->assertJsonPath('data.primary_guarantor_name', 'Mike Johnson')
            ->assertJsonPath('data.primary_guarantor_phone', '+1122334455')
            ->assertJsonPath('data.primary_guarantor_id_type', 'passport');
    }

    /** @test */
    public function it_can_update_rental_with_new_passport()
    {
        // First create a rental
        $rental = Rental::factory()->create([
            'car_id' => $this->car->id,
            'primary_guarantor_name' => 'John Smith',
            'primary_guarantor_phone' => '+123456789',
            'primary_guarantor_id_type' => 'passport',
            'primary_guarantor_id_number' => 'AB123456',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/rentals/{$rental->id}", [
                'passport' => 'XY789012',
                'primary_guarantor_phone' => '+9988776655'
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('rental.passport', 'XY789012')
            ->assertJsonPath('rental.primary_guarantor_phone', '+9988776655')
            ->assertJsonPath('rental.primary_guarantor_id_type', 'passport');
    }

    /** @test */
    public function it_can_update_secondary_guarantor()
    {
        // First create a rental
        $rental = Rental::factory()->create([
            'car_id' => $this->secondCar->id,
            'primary_guarantor_name' => 'Mike Johnson',
            'primary_guarantor_phone' => '+1122334455',
            'primary_guarantor_id_type' => 'passport',
            'primary_guarantor_id_number' => 'CD654321',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/rentals/{$rental->id}", [
                'secondary_guarantor_name' => 'Robert Brown',
                'secondary_guarantor_phone' => '+5544332211',
                'secondary_guarantor_id_type' => 'national_id',
                'secondary_guarantor_id_number' => 'ID12345678'
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('rental.secondary_guarantor_name', 'Robert Brown')
            ->assertJsonPath('rental.secondary_guarantor_phone', '+5544332211')
            ->assertJsonPath('rental.secondary_guarantor_id_type', 'national_id')
            ->assertJsonPath('rental.secondary_guarantor_id_number', 'ID12345678');
    }

    /** @test */
    public function it_can_return_a_car()
    {
        // First create a rental
        $rental = Rental::factory()->create([
            'car_id' => $this->car->id,
            'primary_guarantor_name' => 'John Smith',
            'primary_guarantor_phone' => '+123456789',
            'primary_guarantor_id_type' => 'passport',
            'primary_guarantor_id_number' => 'AB123456',
            'mileage_at_rental' => 12500,
            'is_active' => true,
            'final_price' => 150
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/rentals/return', [
                'id' => $rental->id,
                'mileage_at_return' => 12890,
                'additional_charges' => 25.50,
                'return_location' => 'Airport Terminal 2',
                'comments' => 'Minor scratch on rear bumper',
                'return_service_check' => [
                    'fuel_level' => '3/4',
                    'exterior_condition' => 'good',
                    'interior_condition' => 'good'
                ]
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('rental.is_active', false)
            ->assertJsonPath('rental.mileage_at_return', 12890)
            ->assertJsonPath('rental.additional_charges', 25.50)
            ->assertJsonPath('rental.final_total', 175.50)
            ->assertJsonPath('rental.return_location', 'Airport Terminal 2');
            
        // Check that car is now available again
        $this->assertDatabaseHas('cars', [
            'id' => $this->car->id,
            'is_available' => true
        ]);
    }
} 