# 🚀 DIDISPEN — Digital Dispensasi Pendidikan
**SMKN 1 Bangsri**

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-38BDF8?style=for-the-badge&logo=tailwindcss)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql)

---

## 📌 Tentang Proyek
**DIDISPEN (Digital Dispensasi Pendidikan)** adalah sistem manajemen perizinan dan dispensasi keluar-masuk siswa berbasis web yang dirancang khusus untuk **SMKN 1 Bangsri**. 

Sistem ini mengintegrasikan alur dispensasi secara *real-time* dari pengajuan oleh siswa, verifikasi oleh Guru Piket Utama, pemindaian **Kode QR** di pos Satpam gerbang sekolah, hingga pencetakan laporan rekapitulasi.

**Developed with ❤️ by By 3M**:
- 👨‍💻 **Maulana Fahri Oktavian**
- 👨‍💻 **Muhammad Sabrian Nuh**
- 👨‍💻 **Muhammad Zainal Arief**

---

## ⚙️ Spesifikasi & Persyaratan Sistem

Sebelum melakukan installasi, pastikan lingkungan server/komputer Anda memenuhi persyaratan berikut:

| Komponen | Persyaratan Minimum |
| :--- | :--- |
| **PHP Version** | `>= 8.2` |
| **Framework** | `Laravel 11.x` |
| **Database** | MySQL / MariaDB `>= 10.4` |
| **Dependency Manager** | Composer `>= 2.5` |
| **Web Server** | Nginx / Apache / Artisan Serve |
| **Ekstensi PHP Wajib** | `php-gd` (untuk QR Code), `php-pdo`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip` |

### 📦 Package Dependencies Utama
- `simplesoftwareio/simple-qrcode` — Generasi Kode QR Dispensasi (SVG)
- `barryvdh/laravel-dompdf` — Generasi Laporan Cetak PDF
- `Alpine.js` & `TailwindCSS` — Interaktivitas UI & Layout Responsif
- `SweetAlert2` — Notifikasi & Alert Interaktif

---

## 🛠️ Panduan Instalasi (Setelah Clone / Download)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek setelah berhasil melakukan `git clone` atau mengunduh source code:

### 1. Clone / Download Repository
```bash
git clone https://github.com/username/Digipen.git
cd Digipen
```

### 2. Install Dependensi Composer
```bash
composer install
```

### 3. Konfigurasi File Environment `.env`
Duplikat file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env` dan atur koneksi database sesuai server Anda:
```env
APP_NAME=DIDISPEN
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=didispen
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Buat Storage Link (Public Storage)
Diperlukan untuk membaca logo, QR Code, dan file upload:
```bash
php artisan storage:link
```

### 6. Jalankan Migrasi Database & Seeder Data Demo
Pastikan database dengan nama yang ada di `.env` (contoh: `didispen`) sudah dibuat di MySQL/PHPMyAdmin, lalu jalankan:
```bash
php artisan migrate:fresh --seed
```

### 7. Optimasi & Refresh Autoload Composer
```bash
composer dump-autoload
php artisan optimize:clear
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser di: `http://127.0.0.1:8000` atau `http://localhost:8000`

---

## 🔑 Kredensial Akun Login Demo

Setelah menjalankan `php artisan db:seed`, Anda dapat menggunakan akun demo berikut untuk masuk ke sistem:

| Peran (Role) | Email Login | Password Default | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| 👨‍🎓 **Siswa** | `zainal@gmail.com` | `password` | Buat Pengajuan, Cek QR Code, Notifikasi |
| 👨‍🏫 **Guru Piket** | `gurupiket@smkn1bangsri.sch.id` | `gurupiket2026` | Verifikasi (Setujui/Tolak), Export Laporan |
| 👮 **Satpam** | `satpam@smkn1bangsri.sch.id` | `password` | Scan QR Code Gerbang, Konfirmasi Kembali |
| 👨‍💼 **Admin** | `admin@sch.id` | `password` | Kelola Master Data Siswa/Guru, Jadwal Piket |

---

## 📖 Panduan Penggunaan Singkat

### 👨‍🎓 1. Panduan untuk SISWA
1. **Buat Pengajuan**: Login &rarr; Klik tombol **"+" (Buat Pengajuan)** &rarr; Isi Kategori, Lokasi, Alasan, Jam Keluar & Kembali &rarr; Kirim.
2. **Lihat Kode QR**: Buka menu **"Riwayat"** &rarr; Jika status **Disetujui** (Hijau), klik pengajuan untuk melihat Kode QR Aktif.
3. **Di Pos Satpam**: Tunjukkan Kode QR ke Satpam saat keluar gerbang. Saat kembali ke sekolah, laporkan diri ke Satpam untuk dikonfirmasi **Selesai**.

### 👨‍🏫 2. Panduan untuk GURU PIKET
1. **Memverifikasi Pengajuan**: Login &rarr; Buka menu **"Verifikasi"** &rarr; Klik **"Proses"** &rarr; Klik **"Setujui"** (terbit QR) atau **"Tolak"** (isi alasan).
2. **Cetak Laporan**: Menu **"Laporan"** &rarr; Atur Filter Tanggal/Status &rarr; Klik **"Export PDF"** / **"Export Excel"**.

### 👮‍♂️ 3. Panduan untuk SATPAM
1. **Scan QR Siswa Keluar**: Buka menu **"Scan QR"** &rarr; Arahkan kamera ke QR Code siswa &rarr; Sistem mencatat siswa keluar (QR hanya bisa di-scan 1x).
2. **Konfirmasi Siswa Kembali**: Buka Dashboard Satpam &rarr; Tabel **"Siswa Sedang Keluar"** &rarr; Klik **"Konfirmasi Kembali"**.

---

© 2026 **DIDISPEN - SMKN 1 Bangsri**. All rights reserved.  
Developed by **By 3M** (Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief).
