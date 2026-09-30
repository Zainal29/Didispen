<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $defaultContent = "Halo Pak/Bu {nama_guru},\n\nSaya {nama_siswa} dari kelas {kelas}, jurusan {jurusan}.\n\nSaya ingin menghubungi Bapak/Ibu selaku Guru Piket karena saat ini Bapak/Ibu sedang tidak berada di tempat.\n\nBerikut informasi pengajuan saya:\n\nNama: {nama_siswa}\nNIS: {nis}\nKelas: {kelas}\nJurusan: {jurusan}\n\nNomor Surat: {nomor_surat}\nKeperluan: {kategori}\nAlasan: {alasan}\nTujuan: {tujuan}\nLokasi: {lokasi}\nJam Keluar: {jam_keluar}\nJam Kembali: {jam_kembali}\n\nMohon dapat membantu menindaklanjuti pengajuan saya melalui DIDISPEN.\n\nWebsite DIDISPEN:\n{link_web}\n\nTerima kasih, Pak/Bu.";

        DB::table('whatsapp_templates')->updateOrInsert(
            ['slug' => 'hubungi-guru-piket'],
            [
                'name'       => 'Hubungi Guru Piket (oleh Siswa)',
                'slug'       => 'hubungi-guru-piket',
                'content'    => $defaultContent,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('whatsapp_templates')->where('slug', 'hubungi-guru-piket')->delete();
    }
};
