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
            // Hash SHA256 dari data dokumen saat ditandatangani
            $table->string('signature_hash_pembuat', 64)->nullable()->after('waktu_ttd_pembuat');
            $table->json('signature_metadata_pembuat')->nullable()->after('signature_hash_pembuat');

            $table->string('signature_hash_penerima', 64)->nullable()->after('waktu_ttd_penerima');
            $table->json('signature_metadata_penerima')->nullable()->after('signature_hash_penerima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn([
                'signature_hash_pembuat',
                'signature_metadata_pembuat',
                'signature_hash_penerima',
                'signature_metadata_penerima',
            ]);
        });
    }
};
