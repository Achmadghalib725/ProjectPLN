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
            return;
        }

        DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_tipe_check");
        DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_tipe_check CHECK (tipe IN ('TRANSFER','PEMINJAMAN','PENGEMBALIAN'))");
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE surat_jalans DROP CONSTRAINT IF EXISTS surat_jalans_tipe_check");
        DB::statement("ALTER TABLE surat_jalans ADD CONSTRAINT surat_jalans_tipe_check CHECK (tipe IN ('PEMINJAMAN','PENGEMBALIAN'))");
    }
};

