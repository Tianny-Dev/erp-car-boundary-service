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
        Schema::create('user_passengers', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('users')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->foreignId('payment_option_id')->constrained('payment_options')->onDelete('restrict');
            $table->enum('preferred_language', ['English', 'Filipino', 'Others'])->default('English');
            $table->enum('accssibility_option', ['Normal', 'Wheelchair Access', 'Pet-Friendly Ride'])->default('Normal');
            $table->date('birth_date');
            $table->unsignedTinyInteger('age');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_passengers');
    }
};
