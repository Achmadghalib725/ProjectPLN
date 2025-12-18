<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        $hasColumn = Schema::hasColumn('surat_jalans', 'pic_tujuan_id');

        if ($hasColumn) {
            Schema::table('surat_jalans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pic_tujuan_id');
            });
        }

        Schema::table('surat_jalans', function (Blueprint $table) use ($hasColumn) {
            if (!$hasColumn) {
                $table->foreignId('pic_tujuan_id')
                    ->nullable()
                    ->after('gudang_tujuan_id')
                    ->constrained('pics')
                    ->nullOnDelete();
            } else {
                $table->foreignId('pic_tujuan_id')
                    ->nullable()
                    ->after('gudang_tujuan_id')
                    ->constrained('pics')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('surat_jalans')) {
            return;
        }

        if (!Schema::hasColumn('surat_jalans', 'pic_tujuan_id')) {
            return;
        }

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_tujuan_id');
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->foreignId('pic_tujuan_id')
                ->nullable()
                ->after('gudang_tujuan_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }
};

