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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('user_managers')->onDelete('restrict');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->foreignId('payment_option_id')->constrained('payment_options')->onDelete('restrict');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('phone', 20)->unique();
            $table->string('address');
            $table->string('region');
            $table->string('province')->nullable();
            $table->string('city');
            $table->string('barangay');
            $table->string('postal_code', 20);
            $table->string('dti_registrarion_attachment');
            $table->string('mayor_permit_attachment');
            $table->string('proof_agreement_attachment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
