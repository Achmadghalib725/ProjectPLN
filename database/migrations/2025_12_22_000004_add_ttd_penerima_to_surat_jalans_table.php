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
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        Schema::table('surat_jalans', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_jalans', 'ttd_penerima_id')) {
                $table->foreignId('ttd_penerima_id')->nullable()->constrained('users')->after('ttd_pembuat_id');
            }
            if (!Schema::hasColumn('surat_jalans', 'waktu_ttd_penerima')) {
                $table->timestamp('waktu_ttd_penerima')->nullable()->after('waktu_ttd_pembuat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        Schema::table('surat_jalans', function (Blueprint $table) {
            if (Schema::hasColumn('surat_jalans', 'ttd_penerima_id')) {
                $table->dropConstrainedForeignId('ttd_penerima_id');
            }
            if (Schema::hasColumn('surat_jalans', 'waktu_ttd_penerima')) {
                $table->dropColumn('waktu_ttd_penerima');
            }
        });
    }
};
