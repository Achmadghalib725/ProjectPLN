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
        Schema::create('surat_jalan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_jalan_id')->constrained('surat_jalans')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalan_items');
    }
};
