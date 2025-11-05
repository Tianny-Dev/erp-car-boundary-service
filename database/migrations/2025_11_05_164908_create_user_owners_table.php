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
        Schema::create('user_owners', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->primary();
            $table->foreignId('status_id')->after('id')->constrained('statuses')->onDelete('restrict');
            $table->enum('valid_id_type', ['National ID', 'Passport', 'Driver License', 'Voter ID', 'Unified Multi-Purpose ID', 'TIN ID'])->default('National ID');
            $table->string('valid_id_number', 20)->unique();
            $table->string('front_valid_id_picture');
            $table->string('back_valid_id_picture');
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_owners');
    }
};
