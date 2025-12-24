<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->string('pic_tujuan_custom_nama', 255)->nullable()->after('pic_tujuan_id');
            $table->string('pic_tujuan_custom_jabatan', 255)->nullable()->after('pic_tujuan_custom_nama');
            $table->string('pic_tujuan_custom_no_hp', 50)->nullable()->after('pic_tujuan_custom_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn(['pic_tujuan_custom_nama', 'pic_tujuan_custom_jabatan', 'pic_tujuan_custom_no_hp']);
        });
    }
};
