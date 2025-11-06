<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            
        });

        DB::table('payment_options')->insert([
            ['id' => 1, 'name' => 'Cash'], 
            ['id' => 2, 'name' => 'Credit Card'],
            ['id' => 3, 'name' => 'Gcash'], 
            ['id' => 4, 'name' => 'Paymaya'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_options');
    }
};
