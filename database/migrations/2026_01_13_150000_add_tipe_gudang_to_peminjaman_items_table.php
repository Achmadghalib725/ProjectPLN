<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman_items', function (Blueprint $table) {
            $table->string('tipe_gudang', 20)->default('mekanik')->after('item_id');
        });

        // Add check constraint for PostgreSQL
        DB::statement("ALTER TABLE peminjaman_items ADD CONSTRAINT peminjaman_items_tipe_gudang_check CHECK (tipe_gudang IN ('mekanik', 'listrik'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint
        DB::statement("ALTER TABLE peminjaman_items DROP CONSTRAINT IF EXISTS peminjaman_items_tipe_gudang_check");

        Schema::table('peminjaman_items', function (Blueprint $table) {
            $table->dropColumn('tipe_gudang');
        });
    }
};
