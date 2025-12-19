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
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->string('nama_driver')->nullable()->after('catatan');
            $table->string('jenis_kendaraan')->nullable()->after('nama_driver');
            $table->string('nomor_plat')->nullable()->after('jenis_kendaraan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn(['nama_driver', 'jenis_kendaraan', 'nomor_plat']);
        });
    }
};
