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
        Schema::create('driver_franchise', function (Blueprint $table) {
            // composite primary key instead of auto-increment
            $table->foreignId('driver_id')->constrained('user_drivers')->onDelete('cascade');
            $table->foreignId('franchise_id')->constrained('franchises')->onDelete('cascade');

            // Set composite primary key
            $table->primary(['driver_id', 'franchise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_franchise');
    }
};
