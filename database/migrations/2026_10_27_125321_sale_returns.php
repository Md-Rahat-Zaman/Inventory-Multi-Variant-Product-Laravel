<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('return_date');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
