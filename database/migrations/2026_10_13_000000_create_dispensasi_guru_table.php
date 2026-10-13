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
        if (!Schema::hasTable('dispensasi_guru')) {
            Schema::create('dispensasi_guru', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dispensasi_id')->constrained('dispensasi')->cascadeOnDelete();
                $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['dispensasi_id', 'guru_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensasi_guru');
    }
};
