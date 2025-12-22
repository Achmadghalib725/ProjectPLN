<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('peminjamans')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            // For MySQL - add DIPERIKSA and DITOLAK status
            DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'SELESAI') DEFAULT 'DIAJUKAN'");
            return;
        }

        // For PostgreSQL - update the CHECK constraint
        DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
        DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','SELESAI'))");
    }

    public function down(): void
    {
        if (!Schema::hasTable('peminjamans')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            // For MySQL - revert to previous status values
            DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DITERIMA', 'DIKEMBALIKAN', 'SELESAI') DEFAULT 'DIAJUKAN'");
            return;
        }

        // For PostgreSQL
        DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
        DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DITERIMA','DIKEMBALIKAN','SELESAI'))");
    }
};
