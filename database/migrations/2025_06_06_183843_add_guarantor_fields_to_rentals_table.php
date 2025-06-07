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
            // Primary guarantor fields (the person who is renting the car)
            $table->renameColumn('renter_name', 'primary_guarantor_name');
            $table->renameColumn('renter_phone', 'primary_guarantor_phone');
            $table->renameColumn('passport_number', 'primary_guarantor_id_number');
            
            // Add ID type field for primary guarantor
            $table->string('primary_guarantor_id_type')->default('passport')->after('primary_guarantor_phone');
            
            // Secondary guarantor fields
            $table->string('secondary_guarantor_name')->nullable()->after('primary_guarantor_id_number');
            $table->string('secondary_guarantor_phone')->nullable()->after('secondary_guarantor_name');
            $table->string('secondary_guarantor_id_type')->nullable()->after('secondary_guarantor_phone');
            $table->string('secondary_guarantor_id_number')->nullable()->after('secondary_guarantor_id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Remove secondary guarantor fields
            $table->dropColumn([
                'secondary_guarantor_name',
                'secondary_guarantor_phone',
                'secondary_guarantor_id_type',
                'secondary_guarantor_id_number',
                'primary_guarantor_id_type'
            ]);
            
            // Revert column renames
            $table->renameColumn('primary_guarantor_name', 'renter_name');
            $table->renameColumn('primary_guarantor_phone', 'renter_phone');
            $table->renameColumn('primary_guarantor_id_number', 'passport_number');
        });
    }
};
