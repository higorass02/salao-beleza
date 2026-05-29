<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_closings', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique();
            $table->date('week_end');
            $table->decimal('total_value', 10, 2)->default(0);
            $table->decimal('provider_total', 10, 2)->default(0);
            $table->decimal('store_total', 10, 2)->default(0);
            $table->decimal('house_fee_total', 10, 2)->default(0);
            $table->json('days_summary')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_closings');
    }
};
