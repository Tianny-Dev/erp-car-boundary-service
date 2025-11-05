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
        Schema::create('user_drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->primary();
            $table->foreignId('status_id')->after('id')->constrained('statuses')->onDelete('restrict');
            $table->foreignId('payment_option_id')->after('id')->constrained('payment_options')->onDelete('restrict');
            $table->string('license_number', 20)->unique();
            $table->boolean('is_verified')->default(false);
            $table->date('license_expiry');
            $table->string('front_license_picture');
            $table->string('back_license_picture');
            $table->string('nbi_clearance');
            $table->string('selfie_picture');
            $table->date('hire_date');
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_drivers');
    }
};
