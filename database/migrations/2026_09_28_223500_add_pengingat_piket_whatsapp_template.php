<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'pengingat-piket'],
            [
                'name' => 'Pengingat Jadwal Guru Piket',
                'slug' => 'pengingat-piket',
                'content' => "Halo Yth. Bapak/Ibu *{nama_guru}*,\n\nKami mengingatkan bahwa Anda memiliki jadwal piket di sekolah pada:\nHari: *{hari}*\nSesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\nKoordinator: {koordinator}\n\nMohon untuk hadir tepat waktu dan bertugas di pos piket untuk memantau kehadiran serta dispensasi siswa.\n\nTerima kasih atas dedikasi dan kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')->where('slug', 'pengingat-piket')->delete();
    }
};
