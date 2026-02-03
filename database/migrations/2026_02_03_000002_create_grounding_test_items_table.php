<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grounding_test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grounding_test_id')->constrained('grounding_tests')->cascadeOnDelete();
            $table->string('titik_ukur');
            $table->decimal('kriteria', 10, 2);
            $table->decimal('hasil_uji', 10, 2);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grounding_test_items');
    }
};
