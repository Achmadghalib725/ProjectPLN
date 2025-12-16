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
        Schema::create('peminjaman_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamans')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('jumlah_dipinjam');
            $table->integer('jumlah_diterima')->nullable();
            $table->integer('jumlah_dikembalikan')->nullable();
            $table->enum('kondisi_kembali', ['BAIK', 'RUSAK', 'HILANG'])->nullable();
            $table->text('catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_items');
    }
};
