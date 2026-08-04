<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru_piket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete(); // Guru yang dijadwalkan
            
            $table->date('tanggal');
            $table->enum('shift', ['pagi', 'siang']);
            
            // ✅ FITUR BARU: Digabung dalam 1 tabel
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir', 'izin', 'sakit'])->default('hadir');
            $table->string('keterangan')->nullable(); // Alasan jika tidak hadir
            $table->foreignId('pengganti_guru_id')->nullable()->constrained('guru')->nullOnDelete(); // Guru pengganti
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_piket');
    }
};