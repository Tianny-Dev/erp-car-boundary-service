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
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            
        });

        DB::table('user_types')->insert([
            ['id' => 1, 'name' => 'super_admin'], 
            ['id' => 2, 'name' => 'owner'],
            ['id' => 3, 'name' => 'manager'],
            ['id' => 4, 'name' => 'driver'],
            ['id' => 5, 'name' => 'technician'],
            ['id' => 6, 'name' => 'passenger'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};
