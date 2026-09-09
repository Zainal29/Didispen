<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Dispensasi Disetujui"
            $table->string('slug')->unique(); // Contoh: "disetujui"
            $table->text('content'); // Isi pesan dengan variabel {nama_siswa}, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data default agar sistem tidak error setelah migration
        DB::table('whatsapp_templates')->insert([
            [
                'name' => 'Dispensasi Disetujui',
                'slug' => 'disetujui',
                'content' => "Halo *{nama_siswa}*,\n\nPengajuan dispensasi Anda dengan nomor surat *{nomor_surat}* telah *DISETUJUI* oleh Guru Piket.\n\nSilakan tunjukkan QR Code kepada Satpam saat keluar.\n\nTerima kasih.\n- Sistem DIDISPEN",
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Dispensasi Ditolak',
                'slug' => 'ditolak',
                'content' => "Halo *{nama_siswa}*,\n\nMohon maaf, pengajuan dispensasi Anda (No: *{nomor_surat}*) *DITOLAK*.\n\nAlasan: {catatan}\n\nSilakan ajukan kembali dengan alasan yang lebih jelas atau hubungi Guru Piket.\n- Sistem DIDISPEN",
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Siswa Keluar (Scan Satpam)',
                'slug' => 'keluar',
                'content' => "Halo *{nama_siswa}*,\n\nAnda telah tercatat *KELUAR* dari sekolah pada pukul {waktu_aktual} untuk dispensasi No: *{nomor_surat}*.\n\nJangan lupa kembali tepat waktu sesuai jadwal ({jam_kembali}).\n- Sistem DIDISPEN",
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Siswa Kembali / Selesai',
                'slug' => 'kembali',
                'content' => "Halo *{nama_siswa}*,\n\nDispensasi Anda (No: *{nomor_surat}*) telah *SELESAI*.\n\nTerima kasih sudah kembali ke sekolah tepat waktu.\n- Sistem DIDISPEN",
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Peringatan Terlambat',
                'slug' => 'terlambat',
                'content' => "⚠️ *PERINGATAN DISPENSASI*\n\nYth. *{nama_siswa}*,\n\nAnda telah melewati batas waktu kembali dispensasi (Terlambat *{durasi_terlambat}*).\n\nNo. Surat: {nomor_surat}\nBatas Kembali: {jam_kembali}\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n- Sistem DIDISPEN",
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
