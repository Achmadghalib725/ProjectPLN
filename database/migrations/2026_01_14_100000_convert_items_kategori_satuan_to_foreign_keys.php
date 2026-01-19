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
        // Step 1: Sync existing kategori dari items ke item_categories
        $kategoris = DB::table('items')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');

        foreach ($kategoris as $kategori) {
            $kategoriLower = strtolower(trim($kategori));
            $exists = DB::table('item_categories')
                ->whereRaw('LOWER(nama) = ?', [$kategoriLower])
                ->exists();

            if (!$exists && $kategoriLower) {
                DB::table('item_categories')->insert([
                    'nama' => $kategoriLower,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Step 2: Sync existing satuan dari items ke item_units
        $satuans = DB::table('items')
            ->whereNotNull('satuan')
            ->distinct()
            ->pluck('satuan');

        foreach ($satuans as $satuan) {
            $satuanLower = strtolower(trim($satuan));
            $exists = DB::table('item_units')
                ->whereRaw('LOWER(nama) = ?', [$satuanLower])
                ->exists();

            if (!$exists && $satuanLower) {
                DB::table('item_units')->insert([
                    'nama' => $satuanLower,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Step 3: Add foreign key columns
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->after('nama');
            $table->unsignedBigInteger('satuan_id')->nullable()->after('kategori_id');
        });

        // Step 4: Migrate data from text to foreign keys
        $items = DB::table('items')->get();
        foreach ($items as $item) {
            $kategoriId = null;
            $satuanId = null;

            if ($item->kategori) {
                $kategori = DB::table('item_categories')
                    ->whereRaw('LOWER(nama) = ?', [strtolower(trim($item->kategori))])
                    ->first();
                $kategoriId = $kategori?->id;
            }

            if ($item->satuan) {
                $satuan = DB::table('item_units')
                    ->whereRaw('LOWER(nama) = ?', [strtolower(trim($item->satuan))])
                    ->first();
                $satuanId = $satuan?->id;
            }

            DB::table('items')
                ->where('id', $item->id)
                ->update([
                    'kategori_id' => $kategoriId,
                    'satuan_id' => $satuanId,
                ]);
        }

        // Step 5: Add foreign key constraints
        Schema::table('items', function (Blueprint $table) {
            $table->foreign('kategori_id')
                ->references('id')
                ->on('item_categories')
                ->onDelete('set null');

            $table->foreign('satuan_id')
                ->references('id')
                ->on('item_units')
                ->onDelete('set null');
        });

        // Step 6: Drop old text columns
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'satuan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back text columns
        Schema::table('items', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
            $table->string('satuan')->nullable()->after('kategori');
        });

        // Step 2: Migrate data back from FK to text
        $items = DB::table('items')
            ->leftJoin('item_categories', 'items.kategori_id', '=', 'item_categories.id')
            ->leftJoin('item_units', 'items.satuan_id', '=', 'item_units.id')
            ->select('items.id', 'item_categories.nama as kategori_nama', 'item_units.nama as satuan_nama')
            ->get();

        foreach ($items as $item) {
            DB::table('items')
                ->where('id', $item->id)
                ->update([
                    'kategori' => $item->kategori_nama,
                    'satuan' => $item->satuan_nama,
                ]);
        }

        // Step 3: Drop foreign key constraints and columns
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropForeign(['satuan_id']);
            $table->dropColumn(['kategori_id', 'satuan_id']);
        });
    }
};
