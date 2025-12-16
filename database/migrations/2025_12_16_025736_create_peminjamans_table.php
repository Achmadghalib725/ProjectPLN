<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('gudang_peminjam_id')->constrained('gudangs');
            $table->foreignId('gudang_pemilik_id')->constrained('gudangs');
            $table->enum('status', ['DIAJUKAN', 'DIKIRIM', 'DITERIMA', 'DIKEMBALIKAN', 'SELESAI'])->default('DIAJUKAN');

            // Referensi Surat Jalan (Nullable karena dibuat bertahap)
            $table->foreignId('surat_jalan_kirim_id')->nullable()->constrained('surat_jalans');
            $table->foreignId('surat_jalan_kembali_id')->nullable()->constrained('surat_jalans');

            // Tracking Waktu
            $table->timestamp('waktu_pengajuan')->useCurrent();
            $table->timestamp('waktu_kirim')->nullable();
            $table->timestamp('waktu_diterima')->nullable();
            $table->timestamp('waktu_pengembalian')->nullable();
            $table->timestamp('waktu_selesai')->nullable();

            $table->integer('durasi_jam')->nullable();
            $table->integer('durasi_hari')->nullable();

            // Tanda Tangan Digital
            $table->foreignId('ttd_pengirim_id')->nullable()->constrained('users');
            $table->foreignId('ttd_penerima_id')->nullable()->constrained('users');
            $table->foreignId('ttd_pengembalian_id')->nullable()->constrained('users');
            $table->foreignId('ttd_terima_kembali_id')->nullable()->constrained('users');

            $table->timestamp('waktu_ttd_pengirim')->nullable();
            $table->timestamp('waktu_ttd_penerima')->nullable();
            $table->timestamp('waktu_ttd_pengembalian')->nullable();
            $table->timestamp('waktu_ttd_terima_kembali')->nullable();

            $table->text('catatan_pengiriman')->nullable();
            $table->text('catatan_penerimaan')->nullable();
            $table->text('catatan_pengembalian')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
