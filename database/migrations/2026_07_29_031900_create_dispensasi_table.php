<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dispensasi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            // ✅ FIX REPLAYABILITY: sebelumnya constrained('guru_piket') padahal
            // tabel guru_piket tidak pernah ada di migrations (fresh install gagal).
            // Bentuk final sesuai simplify_guru_piket_to_guru_id: nullable FK ke guru.
            $table->foreignId('guru_id')->nullable()
                ->constrained('guru')->nullOnDelete();
            
            // ✅ DISINKRONKAN DENGAN CONTROLLER
            $table->enum('kategori', ['sakit', 'izin', 'keperluan_sekolah', 'lainnya']);
            
            $table->text('alasan');
            $table->string('tujuan', 255);
            $table->string('lokasi', 255)->nullable();
            
            // ✅ DIUBAH MENJADI STRING (VARCHAR) AGAR BISA MENYIMPAN "Jam Pelajaran ke-X"
            $table->string('jam_keluar', 50); 
            $table->string('jam_kembali', 50);
            
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            
            // ✅ DITAMBAHKAN KOLOM BUKTI FILE YANG SEBELUMNYA HILANG
            $table->string('bukti_file')->nullable(); 
            
            $table->integer('print_count')->default(0);
            $table->integer('max_print_limit')->default(3);
            $table->dateTime('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensasi');
    }
};