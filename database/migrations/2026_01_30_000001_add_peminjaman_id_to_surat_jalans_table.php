<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (!Schema::hasColumn('surat_jalans', 'peminjaman_id')) {
            Schema::table('surat_jalans', function (Blueprint $table) {
                $table->foreignId('peminjaman_id')
                    ->nullable()
                    ->constrained('peminjamans')
                    ->nullOnDelete()
                    ->after('gudang_tujuan_id');
            });
        }

        if (Schema::hasTable('peminjamans') && Schema::hasColumn('peminjamans', 'surat_jalan_kembali_id')) {
            DB::table('peminjamans')
                ->select('id', 'surat_jalan_kembali_id')
                ->whereNotNull('surat_jalan_kembali_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('surat_jalans')
                            ->where('id', $row->surat_jalan_kembali_id)
                            ->update(['peminjaman_id' => $row->id]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans') || !Schema::hasColumn('surat_jalans', 'peminjaman_id')) {
            return;
        }

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropForeign(['peminjaman_id']);
            $table->dropColumn('peminjaman_id');
        });
    }
};
