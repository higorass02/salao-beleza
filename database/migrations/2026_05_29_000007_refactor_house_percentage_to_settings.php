<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('provider_percentage');
        });

        Schema::table('cash_entries', function (Blueprint $table) {
            $table->dropColumn('provider_percentage');
        });

        DB::table('settings')->insertOrIgnore([
            'key'        => 'house_percentage',
            'value'      => '40',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('provider_percentage', 5, 2)->default(0)->after('active');
        });

        Schema::table('cash_entries', function (Blueprint $table) {
            $table->decimal('provider_percentage', 5, 2)->default(0);
        });

        DB::table('settings')->where('key', 'house_percentage')->delete();
    }
};
