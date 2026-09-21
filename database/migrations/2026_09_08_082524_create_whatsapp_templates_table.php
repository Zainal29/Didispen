<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Cek apakah tabel sudah ada untuk mencegah error "table already exists"
        if (!Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('content');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Siapkan data template
        $templates = [
            [
                'name' => 'Dispensasi Disetujui',
                'slug' => 'disetujui',
                'content' => "Halo *{nama_siswa}*,\n\nPengajuan dispensasi Anda dengan nomor surat *{nomor_surat}* telah *DISETUJUI* oleh Guru Piket.\n\nSilakan tunjukkan QR Code kepada Satpam saat keluar.\n\nTerima kasih.\n- Sistem DIDISPEN",
                'is_active' => true,
            ],
            [
                'name' => 'Dispensasi Ditolak',
                'slug' => 'ditolak',
                'content' => "Halo *{nama_siswa}*,\n\nMohon maaf, pengajuan dispensasi Anda (No: *{nomor_surat}*) *DITOLAK*.\n\nAlasan: {catatan}\n\nSilakan ajukan kembali dengan alasan yang lebih jelas atau hubungi Guru Piket.\n- Sistem DIDISPEN",
                'is_active' => true,
            ],
            [
                'name' => 'Siswa Keluar (Scan Satpam)',
                'slug' => 'keluar',
                'content' => "Halo *{nama_siswa}*,\n\nAnda telah tercatat *KELUAR* dari sekolah pada pukul {waktu_aktual} untuk dispensasi No: *{nomor_surat}*.\n\nJangan lupa kembali tepat waktu sesuai jadwal ({jam_kembali}).\n- Sistem DIDISPEN",
                'is_active' => true,
            ],
            [
                'name' => 'Siswa Kembali / Selesai',
                'slug' => 'kembali',
                'content' => "Halo *{nama_siswa}*,\n\nDispensasi Anda (No: *{nomor_surat}*) telah *SELESAI*.\n\nTerima kasih sudah kembali ke sekolah tepat waktu.\n- Sistem DIDISPEN",
                'is_active' => true,
            ],
            [
                'name' => 'Peringatan Terlambat',
                'slug' => 'terlambat',
                'content' => "⚠️ *PERINGATAN DISPENSASI*\n\nYth. *{nama_siswa}*,\n\nAnda telah melewati batas waktu kembali dispensasi (Terlambat *{durasi_terlambat}*).\n\nNo. Surat: {nomor_surat}\nBatas Kembali: {jam_kembali}\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n- Sistem DIDISPEN",
                'is_active' => true,
            ],
        ];

        // 3. Gunakan updateOrInsert agar TIDAK ERROR jika data sudah ada (mencegah Duplicate Entry)
        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->updateOrInsert(
                ['slug' => $template['slug']], // Kondisi pencarian (unique key)
                array_merge($template, ['updated_at' => now()]) // Data yang akan di-update atau di-insert
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
