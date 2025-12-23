<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surat_jalans')) {
            Schema::table('surat_jalans', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_jalans', 'gudang_tujuan_is_custom')) {
                    $table->boolean('gudang_tujuan_is_custom')->default(false)->after('gudang_tujuan_id');
                }
                if (!Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_nama')) {
                    $table->string('gudang_tujuan_custom_nama')->nullable()->after('gudang_tujuan_is_custom');
                }
                if (!Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_alamat')) {
                    $table->string('gudang_tujuan_custom_alamat')->nullable()->after('gudang_tujuan_custom_nama');
                }
                if (!Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_telepon')) {
                    $table->string('gudang_tujuan_custom_telepon')->nullable()->after('gudang_tujuan_custom_alamat');
                }
            });
        }

        if (Schema::hasTable('peminjamans')) {
            Schema::table('peminjamans', function (Blueprint $table) {
                if (!Schema::hasColumn('peminjamans', 'gudang_peminjam_is_custom')) {
                    $table->boolean('gudang_peminjam_is_custom')->default(false)->after('gudang_peminjam_id');
                }
                if (!Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_nama')) {
                    $table->string('gudang_peminjam_custom_nama')->nullable()->after('gudang_peminjam_is_custom');
                }
                if (!Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_alamat')) {
                    $table->string('gudang_peminjam_custom_alamat')->nullable()->after('gudang_peminjam_custom_nama');
                }
                if (!Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_telepon')) {
                    $table->string('gudang_peminjam_custom_telepon')->nullable()->after('gudang_peminjam_custom_alamat');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('surat_jalans')) {
            Schema::table('surat_jalans', function (Blueprint $table) {
                if (Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_telepon')) {
                    $table->dropColumn('gudang_tujuan_custom_telepon');
                }
                if (Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_alamat')) {
                    $table->dropColumn('gudang_tujuan_custom_alamat');
                }
                if (Schema::hasColumn('surat_jalans', 'gudang_tujuan_custom_nama')) {
                    $table->dropColumn('gudang_tujuan_custom_nama');
                }
                if (Schema::hasColumn('surat_jalans', 'gudang_tujuan_is_custom')) {
                    $table->dropColumn('gudang_tujuan_is_custom');
                }
            });
        }

        if (Schema::hasTable('peminjamans')) {
            Schema::table('peminjamans', function (Blueprint $table) {
                if (Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_telepon')) {
                    $table->dropColumn('gudang_peminjam_custom_telepon');
                }
                if (Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_alamat')) {
                    $table->dropColumn('gudang_peminjam_custom_alamat');
                }
                if (Schema::hasColumn('peminjamans', 'gudang_peminjam_custom_nama')) {
                    $table->dropColumn('gudang_peminjam_custom_nama');
                }
                if (Schema::hasColumn('peminjamans', 'gudang_peminjam_is_custom')) {
                    $table->dropColumn('gudang_peminjam_is_custom');
                }
            });
        }
    }
};
