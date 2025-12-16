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
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('gudang_asal_id')->constrained('gudangs');
            $table->foreignId('gudang_tujuan_id')->constrained('gudangs');
            $table->enum('tipe', ['PEMINJAMAN', 'PENGEMBALIAN']);
            $table->enum('status', ['DRAFT', 'DIKIRIM', 'DITERIMA', 'SELESAI'])->default('DRAFT');
            $table->date('tanggal');
            $table->foreignId('created_by')->constrained('users'); // Operator pembuat
            $table->foreignId('ttd_pembuat_id')->nullable()->constrained('users');
            $table->timestamp('waktu_ttd_pembuat')->nullable();
            $table->text('catatan')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};
