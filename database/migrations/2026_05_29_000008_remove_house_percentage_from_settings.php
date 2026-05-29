<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'house_percentage')->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'        => 'house_percentage',
            'value'      => '40',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
