<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $fakerID = FakerFactory::create('id_ID');
        $jumlahSiswaDummy = 2; // Ubah angka ini (misal: 10, 30, atau 50). Jangan terlalu besar agar tidak timeout.

        // Ambil semua ID sekali saja di awal agar proses looping jauh lebih cepat (optimasi performa)
        $kelasIds = Kelas::pluck('id')->toArray();
        $jurusanIds = Jurusan::pluck('id')->toArray();

        if (empty($kelasIds) || empty($jurusanIds)) {
            $this->command->error('❌ Data Kelas atau Jurusan kosong! Jalankan KelasSeeder dan JurusanSeeder terlebih dahulu.');

            return;
        }

        // =====================================================================
        // 1. Buat akun khusus untuk Zainal (Siswa)
        // =====================================================================
        $userZainal = User::firstOrCreate(
            ['email' => 'zainal@gmail.com'],
            [
                'name' => 'Zainal',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => '20269999',
            ]
        );

        // Ambil kelas acak untuk Zainal, lalu ambil jurusan_id dari kelas tersebut
        $kelasUntukZainal = Kelas::inRandomOrder()->first();
        $jurusanIdUntukZainal = $kelasUntukZainal ? $kelasUntukZainal->jurusan_id : $jurusanIds[0];

        // Buat relasi ke tabel siswa jika belum ada (DITAMBAHKAN 'jurusan_id')
        Siswa::firstOrCreate(
            ['user_id' => $userZainal->id],
            [
                'kelas_id' => $kelasUntukZainal?->id ?? $kelasIds[0],
                'jurusan_id' => $jurusanIdUntukZainal, // <-- FIX: Mencegah error 1364
                'nama_lengkap' => 'Zainal',
                'tanggal_lahir' => '2008-01-01',
                'alamat' => $fakerID->address(),
                'no_telepon' => $fakerID->phoneNumber(),
            ]
        );

        // =====================================================================
        // 2. Generate Siswa dummy tambahan (Menggantikan Factory agar jurusan_id pasti terisi)
        // =====================================================================
        for ($i = 0; $i < $jumlahSiswaDummy; $i++) {
            $namaLengkap = $fakerID->name();
            $nomorInduk = $fakerID->unique()->numerify('2026####');

            // a. Buat User
            $user = User::create([
                'name' => $namaLengkap,
                'email' => $fakerID->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => $nomorInduk,
            ]);

            // b. Pilih Kelas dan Jurusan secara acak dari array yang sudah diambil di awal
            $randomKelasId = $fakerID->randomElement($kelasIds);
            $kelas = Kelas::find($randomKelasId);
            $randomJurusanId = $kelas ? $kelas->jurusan_id : $fakerID->randomElement($jurusanIds);

            // c. Buat Siswa
            Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $randomKelasId,
                'jurusan_id' => $randomJurusanId, // <-- FIX: Mencegah error 1364 pada data dummy
                'nama_lengkap' => $namaLengkap,
                'tanggal_lahir' => $fakerID->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
                'alamat' => $fakerID->address(),
                'no_telepon' => $fakerID->phoneNumber(),
            ]);
        }

        $this->command->info("✅ SiswaSeeder selesai. (Akun zainal@gmail.com & {$jumlahSiswaDummy} Siswa dummy berhasil dibuat)");
    }
}
