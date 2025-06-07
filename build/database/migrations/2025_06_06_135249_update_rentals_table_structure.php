<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('customer_name', 'renter_name');
            $table->renameColumn('customer_phone_number', 'renter_phone');
            $table->renameColumn('km_before', 'mileage_at_rental');
            $table->renameColumn('km_after', 'mileage_at_return');
            $table->renameColumn('start_date', 'rental_start_date');
            $table->renameColumn('end_date', 'rental_end_date');
            $table->renameColumn('total_price', 'total_price'); // Will keep as is
            
            // Add new fields
            $table->string('passport_number')->nullable()->after('renter_phone');
            $table->string('pickup_location')->nullable()->after('passport_number');
            $table->string('return_location')->nullable()->after('pickup_location');
            $table->dateTime('return_date')->nullable()->after('rental_end_date');
            
            // Duration-related fields
            $table->renameColumn('plan', 'duration_type'); // daily, weekly, monthly
            $table->integer('duration_count')->default(1)->after('duration_type');
            $table->decimal('price_rate', 8, 2)->after('duration_count'); // The rate per duration unit
            
            // Financial fields
            $table->decimal('discount_amount', 8, 2)->default(0)->after('total_price');
            $table->decimal('final_price', 8, 2)->after('discount_amount'); // total_price - discount_amount
            $table->decimal('additional_charges', 8, 2)->default(0)->after('final_price');
            $table->decimal('final_total', 8, 2)->after('additional_charges'); // final_price + additional_charges
            
            // Change status to two boolean fields
            $table->boolean('is_active')->default(true)->after('final_total');
            $table->boolean('is_paid')->default(false)->after('is_active');
            $table->text('comments')->nullable()->after('is_paid');
            
            // Service check fields as JSON
            $table->json('pickup_service_check')->nullable()->after('comments');
            $table->json('return_service_check')->nullable()->after('pickup_service_check');
            
            // Drop the existing status column as we're replacing it
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Add back the status column
            $table->enum('status', ['rented', 'returned'])->default('rented');
            
            // Drop new columns
            $table->dropColumn([
                'passport_number', 'pickup_location', 'return_location', 'return_date',
                'duration_count', 'price_rate', 'discount_amount', 'final_price',
                'additional_charges', 'final_total', 'is_active', 'is_paid',
                'comments', 'pickup_service_check', 'return_service_check'
            ]);
            
            // Revert column renames
            $table->renameColumn('renter_name', 'customer_name');
            $table->renameColumn('renter_phone', 'customer_phone_number');
            $table->renameColumn('mileage_at_rental', 'km_before');
            $table->renameColumn('mileage_at_return', 'km_after');
            $table->renameColumn('rental_start_date', 'start_date');
            $table->renameColumn('rental_end_date', 'end_date');
            $table->renameColumn('duration_type', 'plan');
        });
    }
};
