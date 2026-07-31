<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru_checklog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->text('alasan');
            $table->string('tujuan');
            $table->string('lokasi')->nullable();
            $table->dateTime('jam_keluar');
            $table->dateTime('jam_kembali')->nullable();
            $table->enum('status', ['keluar', 'selesai'])->default('keluar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_checklog');
    }
};