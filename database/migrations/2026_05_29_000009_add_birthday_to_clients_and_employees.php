<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->tinyInteger('birth_day')->unsigned()->nullable()->after('notes');
            $table->tinyInteger('birth_month')->unsigned()->nullable()->after('birth_day');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->tinyInteger('birth_day')->unsigned()->nullable()->after('active');
            $table->tinyInteger('birth_month')->unsigned()->nullable()->after('birth_day');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['birth_day', 'birth_month']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['birth_day', 'birth_month']);
        });
    }
};
