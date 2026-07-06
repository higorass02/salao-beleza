<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildTableSqlite(nullable: true);
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable()->change();
                $table->unsignedBigInteger('service_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id')->nullable(false)->change();
                $table->unsignedBigInteger('service_id')->nullable(false)->change();
            });
        }
        // SQLite down: ignorado — sem dados de produção no SQLite
    }

    private function rebuildTableSqlite(bool $nullable): void
    {
        $nullDef = $nullable ? 'NULL' : 'NOT NULL';

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("
            CREATE TABLE __appointments_rebuild (
                id           INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                client_id    INTEGER {$nullDef},
                employee_id  INTEGER NOT NULL,
                service_id   INTEGER {$nullDef},
                starts_at    DATETIME NOT NULL,
                ends_at      DATETIME NOT NULL,
                status       VARCHAR NOT NULL DEFAULT 'scheduled',
                notes        TEXT NULL,
                created_at   DATETIME NULL,
                updated_at   DATETIME NULL,
                paid_to      DATETIME NULL,
                is_recurring TINYINT(1) NOT NULL DEFAULT 0,
                recurrence_type  VARCHAR NULL,
                recurrence_until DATE NULL,
                parent_id    INTEGER NULL
            )
        ");

        DB::statement('INSERT INTO __appointments_rebuild SELECT
            id, client_id, employee_id, service_id,
            starts_at, ends_at, status, notes, created_at, updated_at,
            paid_to, is_recurring, recurrence_type, recurrence_until, parent_id
        FROM appointments');

        DB::statement('DROP TABLE appointments');
        DB::statement('ALTER TABLE __appointments_rebuild RENAME TO appointments');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
