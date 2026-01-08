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
        Schema::table('app_notifications', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('app_notifications', 'icon')) {
                $table->string('icon')->nullable()->after('message');
            }
            if (!Schema::hasColumn('app_notifications', 'url')) {
                $table->string('url')->nullable()->after('icon');
            }
            if (!Schema::hasColumn('app_notifications', 'surat_jalan_id')) {
                $table->foreignId('surat_jalan_id')->nullable()->after('url')->constrained()->onDelete('cascade');
            }

            // Add indexes for better performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'created_at']);

            if (Schema::hasColumn('app_notifications', 'surat_jalan_id')) {
                $table->dropForeign(['surat_jalan_id']);
                $table->dropColumn('surat_jalan_id');
            }
            if (Schema::hasColumn('app_notifications', 'url')) {
                $table->dropColumn('url');
            }
            if (Schema::hasColumn('app_notifications', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
