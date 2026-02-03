<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        DB::statement("UPDATE grounding_test_items SET kriteria = REPLACE(kriteria, ',', '.') WHERE kriteria LIKE '%,%'");
        DB::statement("UPDATE grounding_test_items SET hasil_uji = REPLACE(hasil_uji, ',', '.') WHERE hasil_uji LIKE '%,%'");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE grounding_test_items MODIFY kriteria DECIMAL(10,2) NOT NULL");
            DB::statement("ALTER TABLE grounding_test_items MODIFY hasil_uji DECIMAL(10,2) NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE grounding_test_items ALTER COLUMN kriteria TYPE DECIMAL(10,2) USING kriteria::numeric(10,2)");
            DB::statement("ALTER TABLE grounding_test_items ALTER COLUMN hasil_uji TYPE DECIMAL(10,2) USING hasil_uji::numeric(10,2)");
            return;
        }

        Schema::table('grounding_test_items', function (Blueprint $table) {
            $table->decimal('kriteria', 10, 2)->change();
            $table->decimal('hasil_uji', 10, 2)->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE grounding_test_items MODIFY kriteria VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE grounding_test_items MODIFY hasil_uji VARCHAR(255) NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE grounding_test_items ALTER COLUMN kriteria TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE grounding_test_items ALTER COLUMN hasil_uji TYPE VARCHAR(255)");
            return;
        }

        Schema::table('grounding_test_items', function (Blueprint $table) {
            $table->string('kriteria')->change();
            $table->string('hasil_uji')->change();
        });
    }
};
