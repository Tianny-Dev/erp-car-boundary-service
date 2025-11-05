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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
             $table->string('name')->unique();
        });

        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'active'], 
            ['id' => 2, 'name' => 'inactive'],
            ['id' => 3, 'name' => 'suspended'], 
            ['id' => 4, 'name' => 'retired'],
            ['id' => 5, 'name' => 'maintenance'], 
            ['id' => 6, 'name' => 'pending'],
            ['id' => 7, 'name' => 'overdue'], 
            ['id' => 8, 'name' => 'paid'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
