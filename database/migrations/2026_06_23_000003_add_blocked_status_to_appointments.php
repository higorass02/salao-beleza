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
            // SQLite: recriar com a nova definição de status (sem CHECK via enum)
            $this->rebuildStatusSqlite();
        } else {
            // MySQL: alterar enum para incluir 'blocked'
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','done','canceled','blocked') NOT NULL DEFAULT 'scheduled'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','done','canceled') NOT NULL DEFAULT 'scheduled'");
        }
    }

    private function rebuildStatusSqlite(): void
    {
        // Remove o CHECK constraint do enum ao recriar a tabela
        // (a migração anterior já removeu o NOT NULL de client_id/service_id)
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement("
            CREATE TABLE __appointments_status_rebuild (
                id               INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                client_id        INTEGER NULL,
                employee_id      INTEGER NOT NULL,
                service_id       INTEGER NULL,
                starts_at        DATETIME NOT NULL,
                ends_at          DATETIME NOT NULL,
                status           VARCHAR NOT NULL DEFAULT 'scheduled',
                notes            TEXT NULL,
                created_at       DATETIME NULL,
                updated_at       DATETIME NULL,
                paid_to          DATETIME NULL,
                is_recurring     TINYINT(1) NOT NULL DEFAULT 0,
                recurrence_type  VARCHAR NULL,
                recurrence_until DATE NULL,
                parent_id        INTEGER NULL
            )
        ");

        DB::statement('INSERT INTO __appointments_status_rebuild SELECT
            id, client_id, employee_id, service_id,
            starts_at, ends_at, status, notes, created_at, updated_at,
            paid_to, is_recurring, recurrence_type, recurrence_until, parent_id
        FROM appointments');

        DB::statement('DROP TABLE appointments');
        DB::statement('ALTER TABLE __appointments_status_rebuild RENAME TO appointments');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
