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
        Schema::table('cash_entries', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('date')
                  ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_entries', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Employee::class);
            $table->dropColumn('employee_id');
        });
    }
};
