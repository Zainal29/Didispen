<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'pengingat-satpam'],
            [
                'name' => 'Pengingat Satpam Standby Gerbang',
                'slug' => 'pengingat-satpam',
                'content' => "🛡️ *PENGINGAT SIAGA POS GERBANG (DIDISPEN)*\n\nHalo Petugas Satpam, mohon perhatian & kesiapsiagaan di *Pos Gerbang Utama*:\n\n⏰ *Sesi Guru Piket Telah Dimulai:*\n- Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n- Guru Piket Bertugas:\n{daftar_petugas}\n- Koordinator Piket: {koordinator}\n\n📋 *Instruksi Operasional Pos Satpam:*\n1. Standby di Pos Gerbang untuk memantau siswa keluar dan masuk area sekolah.\n2. Pastikan setiap siswa membawa Surat Dispensasi Digital resmi dan lakukan *Scan QR Code Keluar / Masuk*.\n3. Jika ada siswa tanpa izin atau bermasalah, segera koordinasikan dengan Guru Piket yang bertugas di atas.\n\n🔗 *Buka Kamera Scanner Gerbang:*\n{link_scan_satpam}\n\nSelamat bertugas!\n- Sistem DIDISPEN SMKN 1 Bangsri",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')->where('slug', 'pengingat-satpam')->delete();
    }
};
