<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            // For MySQL
            DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM('DRAFT', 'DIKIRIM', 'DITERIMA', 'DITOLAK', 'SELESAI') DEFAULT 'DRAFT'");
            return;
        }

        // For PostgreSQL - update the CHECK constraint
        DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
        DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ('DRAFT','DIKIRIM','DITERIMA','DITOLAK','SELESAI'))");
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            // For MySQL
            DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM('DRAFT', 'DIKIRIM', 'DITERIMA', 'SELESAI') DEFAULT 'DRAFT'");
            return;
        }

        // For PostgreSQL
        DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
        DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ('DRAFT','DIKIRIM','DITERIMA','SELESAI'))");
    }
};
