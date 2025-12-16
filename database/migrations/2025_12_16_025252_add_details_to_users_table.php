<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operator_gudang', 'security'])->default('operator_gudang')->after('email');
            $table->foreignId('gudang_id')->nullable()->constrained('gudangs')->nullOnDelete()->after('role');
            $table->string('ttd_path')->nullable()->after('password');
            $table->string('jabatan')->nullable();
            $table->string('no_hp')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
