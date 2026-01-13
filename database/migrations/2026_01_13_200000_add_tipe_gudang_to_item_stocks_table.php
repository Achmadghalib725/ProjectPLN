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
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->string('tipe_gudang', 20)->default('mekanik')->after('gudang_id');
        });

        // Add check constraint for PostgreSQL
        DB::statement("ALTER TABLE item_stocks ADD CONSTRAINT item_stocks_tipe_gudang_check CHECK (tipe_gudang IN ('mekanik', 'listrik'))");

        // Drop existing unique constraint if any
        try {
            DB::statement("ALTER TABLE item_stocks DROP CONSTRAINT IF EXISTS item_stocks_item_id_gudang_id_unique");
        } catch (\Exception $e) {
            // Ignore if not exists
        }

        // Add new unique constraint including tipe_gudang
        // So same item can exist in both mekanik and listrik for same warehouse
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->unique(['item_id', 'gudang_id', 'tipe_gudang'], 'item_stocks_item_gudang_tipe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new unique constraint
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->dropUnique('item_stocks_item_gudang_tipe_unique');
        });

        // Drop check constraint
        DB::statement("ALTER TABLE item_stocks DROP CONSTRAINT IF EXISTS item_stocks_tipe_gudang_check");

        Schema::table('item_stocks', function (Blueprint $table) {
            $table->dropColumn('tipe_gudang');
        });
    }
};
