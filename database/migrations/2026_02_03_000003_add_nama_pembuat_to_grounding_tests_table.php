<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grounding_tests', function (Blueprint $table) {
            $table->string('nama_pembuat', 100)->nullable()->after('nomor');
        });
    }

    public function down(): void
    {
        Schema::table('grounding_tests', function (Blueprint $table) {
            $table->dropColumn('nama_pembuat');
        });
    }
};
