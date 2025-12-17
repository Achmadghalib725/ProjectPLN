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

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `surat_jalans` MODIFY `tipe` ENUM('TRANSFER','PEMINJAMAN','PENGEMBALIAN') NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `surat_jalans` MODIFY `tipe` ENUM('PEMINJAMAN','PENGEMBALIAN') NOT NULL");
    }
};

