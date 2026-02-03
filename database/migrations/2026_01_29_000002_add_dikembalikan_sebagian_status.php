<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambah status DIKEMBALIKAN_SEBAGIAN untuk mendukung pengembalian parsial.
     * Juga migrasi data existing ke junction table dan set jumlah_dikembalikan.
     */
    public function up(): void
    {
        // 1. Add DIKEMBALIKAN_SEBAGIAN status to peminjamans
        if (Schema::hasTable('peminjamans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
                DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DIKEMBALIKAN_SEBAGIAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'))");
            } else {
                DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DIKEMBALIKAN_SEBAGIAN', 'DITOLAK', 'MENUNGGU_DIKEMBALIKAN', 'SELESAI') DEFAULT 'DIAJUKAN'");
            }
        }

        // 2. Migrate existing surat_jalan_kembali_id to junction table
        if (Schema::hasTable('peminjaman_pengembalian') && Schema::hasTable('peminjamans')) {
            DB::table('peminjamans')
                ->whereNotNull('surat_jalan_kembali_id')
                ->orderBy('id')
                ->chunk(100, function ($peminjamans) {
                    foreach ($peminjamans as $peminjaman) {
                        // Cek apakah surat jalan tersebut sudah ada relasi di junction table
                        $exists = DB::table('peminjaman_pengembalian')
                            ->where('peminjaman_id', $peminjaman->id)
                            ->where('surat_jalan_id', $peminjaman->surat_jalan_kembali_id)
                            ->exists();

                        if (!$exists) {
                            DB::table('peminjaman_pengembalian')->insert([
                                'peminjaman_id' => $peminjaman->id,
                                'surat_jalan_id' => $peminjaman->surat_jalan_kembali_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
        }

        // 3. Set jumlah_dikembalikan = jumlah_dipinjam for completed peminjamans
        if (Schema::hasTable('peminjaman_items') && Schema::hasTable('peminjamans')) {
            DB::table('peminjaman_items')
                ->join('peminjamans', 'peminjaman_items.peminjaman_id', '=', 'peminjamans.id')
                ->where('peminjamans.status', 'SELESAI')
                ->whereNull('peminjaman_items.jumlah_dikembalikan')
                ->update([
                    'peminjaman_items.jumlah_dikembalikan' => DB::raw('peminjaman_items.jumlah_dipinjam'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum (remove DIKEMBALIKAN_SEBAGIAN)
        if (Schema::hasTable('peminjamans')) {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE peminjamans DROP CONSTRAINT IF EXISTS peminjamans_status_check");
                DB::statement("ALTER TABLE peminjamans ADD CONSTRAINT peminjamans_status_check CHECK (status IN ('DIAJUKAN','DIKIRIM','DIPERIKSA','DITERIMA','DIKEMBALIKAN','DITOLAK','MENUNGGU_DIKEMBALIKAN','SELESAI'))");
            } else {
                // First, update any DIKEMBALIKAN_SEBAGIAN to DITERIMA (or appropriate fallback)
                DB::table('peminjamans')
                    ->where('status', 'DIKEMBALIKAN_SEBAGIAN')
                    ->update(['status' => 'DITERIMA']);

                DB::statement("ALTER TABLE peminjamans MODIFY COLUMN status ENUM('DIAJUKAN', 'DIKIRIM', 'DIPERIKSA', 'DITERIMA', 'DIKEMBALIKAN', 'DITOLAK', 'MENUNGGU_DIKEMBALIKAN', 'SELESAI') DEFAULT 'DIAJUKAN'");
            }
        }

        // Note: We don't remove data from junction table on rollback
        // as it doesn't break anything to have that data there
    }
};
