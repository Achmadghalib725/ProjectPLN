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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('tipe_gudang', 20)->default('mekanik')->after('gudang_id');
        });

        // Add check constraint for PostgreSQL
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_tipe_gudang_check CHECK (tipe_gudang IN ('mekanik', 'listrik'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint
        DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS stock_movements_tipe_gudang_check");

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('tipe_gudang');
        });
    }
};
