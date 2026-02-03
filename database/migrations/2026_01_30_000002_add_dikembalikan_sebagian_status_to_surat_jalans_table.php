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

        $statuses = "'DRAFT','MENUNGGU_PERSETUJUAN','DITOLAK_PERSETUJUAN','DIKIRIM','DIPERIKSA','DIPERIKSA_PENGIRIM','DIPERIKSA_PENERIMA','DITERIMA','DIKEMBALIKAN','DIKEMBALIKAN_SEBAGIAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'";

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
            DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ({$statuses}))");
        } else {
            DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM({$statuses}) DEFAULT 'DRAFT'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        $statuses = "'DRAFT','MENUNGGU_PERSETUJUAN','DITOLAK_PERSETUJUAN','DIKIRIM','DIPERIKSA','DIPERIKSA_PENGIRIM','DIPERIKSA_PENERIMA','DITERIMA','DIKEMBALIKAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'";

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_status_check");
            DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_status_check CHECK (status IN ({$statuses}))");
        } else {
            DB::statement("ALTER TABLE surat_jalans MODIFY COLUMN status ENUM({$statuses}) DEFAULT 'DRAFT'");
        }
    }
};
