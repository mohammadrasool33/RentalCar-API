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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['daily', 'weekly', 'monthly']);
            $table->decimal('total_price', 8, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('km_before');
            $table->integer('km_after')->nullable();
            $table->enum('status', ['rented', 'returned'])->default('rented');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
