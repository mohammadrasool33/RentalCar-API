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
        Schema::table('cars', function (Blueprint $table) {
            // Add new fields
            $table->string('model')->nullable()->after('brand');
            $table->text('description')->nullable()->after('model');
            $table->integer('current_mileage')->nullable()->after('monthly_price');
            
            // Rename columns
            $table->renameColumn('daily_price', 'price_per_day');
            $table->renameColumn('weekly_price', 'price_per_week');
            $table->renameColumn('monthly_price', 'price_per_month');
            $table->renameColumn('is_available', 'is_available'); // already matches conceptually
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn('model');
            $table->dropColumn('description');
            $table->dropColumn('current_mileage');
            
            // Restore original column names
            $table->renameColumn('price_per_day', 'daily_price');
            $table->renameColumn('price_per_week', 'weekly_price');
            $table->renameColumn('price_per_month', 'monthly_price');
        });
    }
};
