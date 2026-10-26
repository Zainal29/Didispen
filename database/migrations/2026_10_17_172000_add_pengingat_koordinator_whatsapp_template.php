<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'pengingat-koordinator'],
            [
                'name' => 'Pengingat Koordinator Guru Piket',
                'slug' => 'pengingat-koordinator',
                'content' => "Halo Yth. Bapak/Ibu *{nama_koordinator}*,\n\nIzin mengingatkan bahwa Anda bertugas sebagai *Koordinator Guru Piket* pada hari ini:\nHari: *{hari}*, {tanggal}\nWaktu Piket: *{jam_mulai} - {jam_selesai} WIB* ({nama_sesi})\n\nDaftar Petugas Piket Hari Ini (Sesi 1 s.d. Selesai):\n{daftar_petugas}\n\nMohon bantuan Bapak/Ibu untuk mengecek dan memastikan petugas piket di setiap sesi telah berada di tempatnya agar pelayanan dispensasi siswa dapat berjalan tertib dari awal hingga selesai KBM.\n\nTerima kasih atas kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')->where('slug', 'pengingat-koordinator')->delete();
    }
};