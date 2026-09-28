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
        Schema::create('jadwal_piket', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->date('tanggal_mulai_berlaku');
            $table->date('tanggal_selesai_berlaku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hari', 'jam_mulai', 'jam_selesai']);
            $table->index(['tanggal_mulai_berlaku', 'tanggal_selesai_berlaku']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket');
    }
};
