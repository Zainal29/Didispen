<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'permintaan-tukar-piket'],
            [
                'name' => 'Permintaan Tukar Piket',
                'slug' => 'permintaan-tukar-piket',
                'content' => "📋 *PERMINTAAN PENGGANTIAN JADWAL PIKET (DIDISPEN)*\n\nHalo Yth. Bapak/Ibu *{nama_guru_pengganti}*,\n\nBapak/Ibu *{nama_guru_asal}* mengajukan permohonan agar Anda berkenan menjadi Guru Pengganti untuk jadwal piket beliau pada:\n\n📅 Hari/Tanggal: *{hari}, {tanggal}*\n⏰ Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n📝 Alasan: _{alasan}_\n\nSilakan buka tautan berikut untuk meninjau dan merespon permohonan ini:\n{link_persetujuan}\n\nTerima kasih atas kerjasamanya.\n- Sistem DIDISPEN SMKN 1 Bangsri",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'hasil-tukar-piket'],
            [
                'name' => 'Hasil Persetujuan Tukar Piket',
                'slug' => 'hasil-tukar-piket',
                'content' => "📢 *STATUS PERMINTAAN PENGGANTIAN PIKET (DIDISPEN)*\n\nHalo Yth. Bapak/Ibu *{nama_guru_asal}*,\n\nPermintaan penggantian jadwal piket Anda untuk tanggal *{tanggal}* ({nama_sesi}) telah *{status_persetujuan}* oleh Bapak/Ibu *{nama_guru_pengganti}*.\n\n{catatan_tambahan}\n\nWebsite DIDISPEN:\n{link_web}\n\nTerima kasih.\n- Sistem DIDISPEN SMKN 1 Bangsri",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')
            ->whereIn('slug', ['permintaan-tukar-piket', 'hasil-tukar-piket'])
            ->delete();
    }
};
