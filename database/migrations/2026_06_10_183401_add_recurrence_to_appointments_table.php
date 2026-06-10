<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('notes');
            $table->enum('recurrence_type', ['weekly', 'biweekly'])->nullable()->after('is_recurring');
            $table->date('recurrence_until')->nullable()->after('recurrence_type');
            $table->unsignedBigInteger('parent_id')->nullable()->after('recurrence_until');
            $table->foreign('parent_id')->references('id')->on('appointments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['is_recurring', 'recurrence_type', 'recurrence_until', 'parent_id']);
        });
    }
};
