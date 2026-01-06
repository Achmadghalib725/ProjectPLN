<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pics')) {
            return;
        }

        Schema::table('pics', function (Blueprint $table) {
            if (!Schema::hasColumn('pics', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('gudang_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pics') || !Schema::hasColumn('pics', 'user_id')) {
            return;
        }

        Schema::table('pics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
