<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pics') || !Schema::hasColumn('pics', 'divisi') || !Schema::hasColumn('pics', 'jabatan')) {
            return;
        }

        DB::table('pics')
            ->where(function ($query) {
                $query->whereNull('jabatan')->orWhere('jabatan', '');
            })
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->update(['jabatan' => DB::raw('divisi')]);
    }

    public function down(): void
    {
        // No-op: we don't want to overwrite manual jabatan changes on rollback.
    }
};
