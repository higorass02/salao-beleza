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
        Schema::create('provider_daily_closings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_services', 10, 2)->default(0);
            $table->decimal('house_fee', 10, 2)->default(0);
            $table->decimal('provider_share', 10, 2)->default(0);
            $table->decimal('received_by_provider', 10, 2)->default(0);
            $table->decimal('received_by_store', 10, 2)->default(0);
            $table->decimal('net', 10, 2)->default(0);
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->unique(['date', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_daily_closings');
    }
};
