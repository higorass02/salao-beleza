<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('total_value', 10, 2)->default(0);
            $table->decimal('provider_total', 10, 2)->default(0);
            $table->decimal('store_total', 10, 2)->default(0);
            $table->decimal('house_fee_total', 10, 2)->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
