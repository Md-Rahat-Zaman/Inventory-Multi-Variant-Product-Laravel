<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name', 150);
            $table->string('tagline')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('currency_code', 3)->default('BDT');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        DB::table('shop_settings')->insert([
            'id' => 1,
            'shop_name' => 'Inventory System',
            'currency_code' => 'BDT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
