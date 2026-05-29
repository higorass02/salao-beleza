<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('provider_percentage', 5, 2)->default(0)->after('active');
            $table->boolean('include_house_fee')->default(false)->after('provider_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['provider_percentage', 'include_house_fee']);
        });
    }
};
