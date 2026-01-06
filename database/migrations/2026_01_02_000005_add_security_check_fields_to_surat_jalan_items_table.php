<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('surat_jalan_items')) {
            return;
        }

        Schema::table('surat_jalan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_jalan_items', 'checked_by_security')) {
                $table->boolean('checked_by_security')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('surat_jalan_items', 'checked_by_user_id')) {
                $table->foreignId('checked_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('checked_by_security');
            }
            if (!Schema::hasColumn('surat_jalan_items', 'checked_at')) {
                $table->timestamp('checked_at')->nullable()->after('checked_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalan_items')) {
            return;
        }

        Schema::table('surat_jalan_items', function (Blueprint $table) {
            if (Schema::hasColumn('surat_jalan_items', 'checked_at')) {
                $table->dropColumn('checked_at');
            }
            if (Schema::hasColumn('surat_jalan_items', 'checked_by_user_id')) {
                $table->dropConstrainedForeignId('checked_by_user_id');
            }
            if (Schema::hasColumn('surat_jalan_items', 'checked_by_security')) {
                $table->dropColumn('checked_by_security');
            }
        });
    }
};
