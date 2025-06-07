<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;

// Don't actually make database changes
DB::beginTransaction();

try {
    echo "Checking for available cars...\n";
    $car = Car::where('is_available', true)->first();
    
    if (!$car) {
        echo "Creating a test car...\n";
        $car = new Car();
        $car->name = "Test Car";
        $car->brand = "Test Brand";
        $car->model = "Test Model";
        $car->year = "2023";
        $car->price_per_day = 50;
        $car->price_per_week = 300;
        $car->price_per_month = 1200;
        $car->is_available = true;
        $car->save();
    }
    
    echo "Creating a rental with passport...\n";
    $rental = new Rental();
    $rental->car_id = $car->id;
    $rental->duration_type = 'daily';
    $rental->duration_count = 3;
    $rental->primary_guarantor_name = 'John Smith';
    $rental->primary_guarantor_phone = '+123456789';
    $rental->primary_guarantor_id_type = 'passport';
    $rental->primary_guarantor_id_number = 'AB123456';
    $rental->secondary_guarantor_name = 'Jane Doe';
    $rental->secondary_guarantor_phone = '+987654321';
    $rental->secondary_guarantor_id_type = 'driver_license';
    $rental->secondary_guarantor_id_number = 'DL987654';
    $rental->pickup_location = 'Airport Terminal 1';
    $rental->mileage_at_rental = 12500;
    $rental->rental_start_date = now();
    $rental->rental_end_date = now()->addDays(3);
    $rental->price_rate = 50;
    $rental->total_price = 150;
    $rental->discount_amount = 0;
    $rental->final_price = 150;
    $rental->final_total = 150;
    $rental->is_active = true;
    $rental->is_paid = true;
    $rental->save();
    
    echo "Rental created. Testing passport accessor...\n";
    
    // Test passport accessor
    $passport = $rental->passport;
    echo "Passport value: $passport\n";
    
    if ($passport === 'AB123456') {
        echo "✅ Passport accessor works correctly!\n";
    } else {
        echo "❌ Passport accessor returned: $passport instead of 'AB123456'\n";
    }
    
    // Test setting passport
    echo "Testing passport setter...\n";
    $rental->passport = 'XY789012';
    $rental->save();
    
    // Reload from DB
    $rental = Rental::find($rental->id);
    
    if ($rental->passport === 'XY789012' && 
        $rental->primary_guarantor_id_type === 'passport' && 
        $rental->primary_guarantor_id_number === 'XY789012') {
        echo "✅ Passport setter works correctly!\n";
    } else {
        echo "❌ Passport setter failed!\n";
        echo "passport = {$rental->passport}\n";
        echo "primary_guarantor_id_type = {$rental->primary_guarantor_id_type}\n";
        echo "primary_guarantor_id_number = {$rental->primary_guarantor_id_number}\n";
    }
    
    echo "\n--- ALL TESTS COMPLETE ---\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    // Rollback transaction so we don't affect the database
    DB::rollBack();
    echo "Database changes rolled back - no actual changes were made.\n";
} 