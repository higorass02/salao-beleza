<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Admin pode desativar notificações para este funcionário
            $table->boolean('notify_appointments')->default(true)->after('charges_house_fee');
        });

        Schema::table('users', function (Blueprint $table) {
            // Colaborador pode desativar suas próprias notificações
            $table->boolean('notifications_enabled')->default(true)->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('notify_appointments');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notifications_enabled');
        });
    }
};
