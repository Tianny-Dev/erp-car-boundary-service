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
        Schema::create('taxi_metrics', function (Blueprint $table) {
            $table->id();
            $table->decimal('flag', 10, 2);
            $table->decimal('per_minute', 10, 2);
            $table->decimal('per_km', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxi_metrics');
    }
};
