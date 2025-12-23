<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_jalans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
                DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ('DRAFT','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'))");
            } else {
                DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM('DRAFT', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'MENUNGGU_DIKEMBALIKAN', 'SELESAI') DEFAULT 'DRAFT'");
            }
        }

        if (Schema::hasTable('peminjamans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
                DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'))");
            } else {
                DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'MENUNGGU_DIKEMBALIKAN', 'SELESAI') DEFAULT 'DIAJUKAN'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('surat_jalans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
                DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ('DRAFT','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','SELESAI'))");
            } else {
                DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM('DRAFT', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'SELESAI') DEFAULT 'DRAFT'");
            }
        }

        if (Schema::hasTable('peminjamans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
                DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','SELESAI'))");
            } else {
                DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'SELESAI') DEFAULT 'DIAJUKAN'");
            }
        }
    }
};
