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
                'content' => "Halo Yth. Bapak/Ibu *{nama_koordinator}*,\n\nIzin mengingatkan bahwa Anda bertugas sebagai *Koordinator Guru Piket* pada hari ini:\nHari: *{hari}*, {tanggal}\nSesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n\nDaftar Petugas Piket:\n{daftar_petugas}\n\nMohon bantuan Bapak/Ibu untuk mengecek dan memastikan petugas piket telah berada di tempatnya agar pelayanan dispensasi siswa dapat berjalan tertib.\n\nTerima kasih atas kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri",
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
