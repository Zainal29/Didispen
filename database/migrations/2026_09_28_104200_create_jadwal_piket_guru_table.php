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
        Schema::create('jadwal_piket_guru', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jadwal_piket_id')
                ->constrained('jadwal_piket')
                ->cascadeOnDelete();

            $table->foreignId('guru_id')
                ->constrained('guru')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(['jadwal_piket_id', 'guru_id']);
            $table->index('guru_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket_guru');
    }
};
