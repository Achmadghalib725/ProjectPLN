<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            if (!Schema::hasColumn('surat_jalans', 'qr_token')) {
                $table->string('qr_token', 64)->nullable()->unique()->after('nomor');
            }
        });

        if (!Schema::hasColumn('surat_jalans', 'qr_token')) {
            return;
        }

        DB::table('surat_jalans')
            ->select('id')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $token = $this->generateUniqueToken();
                    DB::table('surat_jalans')
                        ->where('id', $row->id)
                        ->update(['qr_token' => $token]);
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
            if (Schema::hasColumn('surat_jalans', 'qr_token')) {
                $table->dropUnique(['qr_token']);
                $table->dropColumn('qr_token');
            }
        });
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(40);
        } while (DB::table('surat_jalans')->where('qr_token', $token)->exists());

        return $token;
    }
};
