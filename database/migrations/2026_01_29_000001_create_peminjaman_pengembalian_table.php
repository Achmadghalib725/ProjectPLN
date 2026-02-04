<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Junction table untuk mendukung multiple pengembalian (partial returns).
     * Satu peminjaman dapat memiliki banyak surat jalan pengembalian.
     */
    public function up(): void
    {
        Schema::create('peminjaman_pengembalian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamans')->cascadeOnDelete();
            $table->foreignId('surat_jalan_id')->constrained('surat_jalans')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['peminjaman_id', 'surat_jalan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_pengembalian');
    }
};
