<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('performed_at');
            $table->string('client_name');
            $table->string('service_name');
            $table->decimal('service_value', 10, 2);
            $table->decimal('provider_percentage', 5, 2)->default(0);
            $table->boolean('include_house_fee')->default(false);
            $table->enum('paid_to', ['provider', 'store'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_entries');
    }
};
