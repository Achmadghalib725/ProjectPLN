<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add username column (nullable first)
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        // Step 2: Populate username from name (convert to lowercase and replace spaces with underscores)
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $username = Str::slug($user->name, '_');
            // Ensure uniqueness
            $originalUsername = $username;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $originalUsername . '_' . $counter;
                $counter++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        // Step 3: Make username unique and not nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
