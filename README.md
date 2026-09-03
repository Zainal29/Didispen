# 🚀 DIDISPEN — Digital Dispensasi Pendidikan
**SMKN 1 Bangsri**

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-38BDF8?style=for-the-badge&logo=tailwindcss)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql)

---

## 📌 Tentang Proyek
**DIDISPEN (Digital Dispensasi Pendidikan)** adalah sistem manajemen perizinan dan dispensasi keluar-masuk siswa berbasis web yang dirancang khusus untuk **SMKN 1 Bangsri**. 

Sistem ini menggantikan dispensasi kertas dengan alur digital *real-time*: pengajuan oleh siswa, verifikasi oleh Guru Piket, pemindaian **Kode QR** dinamis di pos Satpam, hingga pencetakan struk thermal dan laporan rekapitulasi.

**Fitur Utama:**
- ✅ **Multi-Role Access:** Hak akses terpisah untuk Siswa, Guru Piket, Satpam, dan Admin.
- ✅ **Dynamic QR Code:** QR Code unik yang hanya bisa di-scan 1 kali untuk mencegah penyalahgunaan.
- ✅ **Real-time Validation:** Validasi jam keluar, jam kembali, dan status keterlambatan secara otomatis.
- ✅ **Thermal Printing:** Dukungan cetak struk dispensasi ukuran 58mm untuk arsip satpam.
- ✅ **SiPintu Integration:** Sinkronisasi data master siswa dan guru otomatis dari API Gateway sekolah.

**Developed with ❤️ by By 3M**:
- 👨‍💻 **Maulana Fahri Oktavian**
- 👨‍💻 **Muhammad Sabrian Nuh**
- 👨‍💻 **Muhammad Zainal Arief**

---

## ⚙️ Spesifikasi & Persyaratan Sistem

Pastikan lingkungan server/komputer Anda memenuhi persyaratan berikut sebelum instalasi:

| Komponen | Persyaratan Minimum | Catatan |
| :--- | :--- | :--- |
| **PHP Version** | `>= 8.2` | Disarankan PHP 8.3 untuk performa optimal. |
| **Framework** | `Laravel 11.x` | |
| **Database** | MySQL / MariaDB `>= 10.4` | |
| **Dependency Manager** | Composer `>= 2.5` & Node.js `>= 18` | Diperlukan untuk compile aset frontend. |
| **Ekstensi PHP Wajib** | `php-gd`, `php-pdo`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip` | `php-gd` wajib untuk generate QR Code. |
| **Browser** | Chrome / Edge / Brave (Terbaru) | Wajib untuk fitur scan QR via kamera web. |

---

## 🛠️ Panduan Instalasi (Developer / Local)

Ikuti langkah-langkah berikut untuk menjalankan proyek setelah melakukan `git clone`:

### 1. Clone Repository & Masuk ke Direktori
```bash
git clone https://github.com/username/Digipen.git
cd Digipen
```

### 2. Install Dependensi Backend & Frontend
```bash
# Install package PHP
composer install

# Install package Node.js (jika ada aset yang perlu di-compile)
npm install
npm run build
```

### 3. Konfigurasi Environment (`.env`)
Duplikat file konfigurasi dan atur sesuai database Anda:
```bash
cp .env.example .env
```

### 4. Generate Key & Setup Storage
```bash
php artisan key:generate
php artisan storage:link
```
*(Perintah `storage:link` wajib agar gambar QR Code dan logo dapat ditampilkan).*

### 5. Migrasi Database & Isi Data Demo
Pastikan database yang Anda tulis di `.env` sudah dibuat di phpMyAdmin/MySQL, lalu jalankan:
```bash
php artisan migrate:fresh --seed
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser di: `http://127.0.0.1:8000`

---

## 📖 Panduan Penggunaan Detail (User Manual)

### 👨‍🎓 1. Panduan untuk SISWA
1. **Login:** Masuk menggunakan NIS dan password default (`password`).
2. **Buat Pengajuan:** 
   - Klik tombol **"+ Buat Pengajuan"** di Dashboard.
   - Pilih Kategori (Sakit/Izin/Keperluan Sekolah), isi Lokasi, Alasan, serta estimasi Jam Keluar dan Jam Kembali.
   - Klik **Kirim**. Status akan berubah menjadi *"Menunggu"*.
3. **Cek Status & QR Code:**
   - Buka menu **"Riwayat Pengajuan"**.
   - Jika status sudah **"Disetujui"** (Badge Hijau), klik tombol **"Lihat Detail"** atau **"Lihat QR Code"**.
4. **Proses di Gerbang:** Tunjukkan QR Code di layar HP Anda kepada Satpam saat keluar. Saat kembali ke sekolah, lapor ke Satpam untuk di-konfirmasi "Selesai".

### 👨‍🏫 2. Panduan untuk GURU PIKET
1. **Verifikasi Pengajuan:**
   - Login dan buka menu **"Verifikasi Dispensasi"**.
   - Lihat daftar pengajuan berstatus *"Menunggu"*.
   - Klik **"Proses"**. Anda bisa memilih **"Setujui"** (sistem akan otomatis membuat QR Code) atau **"Tolak"** (wajib mengisi alasan penolakan).
2. **Monitoring:** Guru piket dapat memantau siswa yang sedang keluar di Dashboard untuk memastikan tidak ada yang melebihi batas waktu.

### 👮‍♂️ 3. Panduan untuk SATPAM
1. **Izinkan Kamera:** Saat pertama kali membuka menu **"Scan QR"**, browser akan meminta izin akses kamera. Klik **"Allow" / "Izinkan"**.
2. **Scan Saat Siswa Keluar:**
   - Arahkan kamera ke QR Code siswa.
   - Jika valid, sistem akan berbunyi/bernotifikasi **"✅ Siswa berhasil dicatat KELUAR"**. 
   - *Catatan:* QR Code hanya bisa di-scan **1 kali**. Scan kedua akan ditolak.
3. **Konfirmasi Saat Siswa Kembali:**
   - Buka Dashboard Satpam, lihat tabel **"Siswa Sedang Keluar"**.
   - Saat siswa tiba, cari nama siswa tersebut dan klik tombol **"Konfirmasi Kembali"**.
   - Sistem akan otomatis mengecek apakah siswa terlambat atau tepat waktu.

### 👨‍💼 4. Panduan untuk ADMIN
1. **Manajemen Data:** Admin dapat melihat data Siswa, Guru, dan Jadwal Piket di menu sidebar.
2. **Sinkronisasi SiPintu:** 
   - Buka menu **"Sinkronisasi SiPintu"**.
   - Klik **"Test Koneksi"** untuk memastikan API sekolah aktif.
   - Klik **"Sinkronisasi"** untuk menarik data terbaru siswa/guru agar akun login mereka otomatis terbuat/terupdate.

---

## ⚠️ Troubleshooting (Pemecahan Masalah)

| Masalah | Solusi |
| :--- | :--- |
| **QR Code tidak muncul (Broken Image)** | Pastikan Anda sudah menjalankan `php artisan storage:link`. Periksa folder `public/storage`. |
| **Kamera Scanner Satpam tidak menyala** | Pastikan membuka aplikasi via `https://` (di production) atau `http://localhost` (di local). Browser memblokir kamera pada `http://` dengan IP address (misal: `http://192.168.x.x`). |
| **Error "Class not found" setelah clone** | Jalankan `composer dump-autoload` dan `composer install` kembali. |
| **Gagal Sinkronisasi SiPintu (Timeout)** | Pastikan server memiliki koneksi internet stabil. Cek log di `storage/logs/laravel.log` untuk detail error API. |
| **Halaman tampil berantakan (CSS tidak load)** | Jalankan `npm run build` atau pastikan CDN Tailwind CSS tidak diblokir oleh jaringan sekolah. |

---

## 📂 Struktur Folder Penting
```text
├── app/
│   ├── Http/Controllers/   # Logika pengendali (Siswa, Guru, Satpam, Admin)
│   ├── Models/             # Model database (User, Siswa, Dispensasi, dll)
│   └── Services/           # Logika bisnis kompleks (SipintuService, QR Generator)
├── database/
│   ├── migrations/         # Skema tabel database
│   └── seeders/            # Data dummy untuk pengujian
├── resources/
│   └── views/              # File tampilan Blade (UI)
├── routes/
│   └── web.php             # Definisi URL dan routing aplikasi
└── public/
    └── storage/            # Folder untuk menyimpan file QR Code & Upload
```

---

© 2026 **DIDISPEN - SMKN 1 Bangsri**. All rights reserved.  
Developed by **By 3M** *(Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief)*.

--- 

### 💡 **Apa yang diperbaiki dari versi sebelumnya?**
1. **Menambahkan "Fitur Utama"** agar pembaca langsung tahu keunggulan sistem.
2. **Detail Instalasi** ditambah dengan `npm install` dan penjelasan `.env` yang lebih jelas.
3. **Panduan Pengguna** dipecah menjadi langkah-langkah *step-by-step* yang sangat detail (termasuk instruksi "Izinkan Kamera" untuk satpam yang sering jadi masalah utama).
4. **Menambahkan Tabel Troubleshooting** untuk mengantisipasi pertanyaan umum saat deployment atau penggunaan.
5. **Menambahkan Struktur Folder** untuk memudahkan developer baru yang ingin mengembangkan kode.
