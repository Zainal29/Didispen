# 💻 Daftar Software & Teknologi Sistem DIDISPEN
### **Digital Dispensasi Pendidikan — SMK Negeri 1 Bangsri**
> **Dokumen Teknis Stack & Software**: Disusun untuk dokumentasi arsitektur sistem dan persiapan presentasi teknis / Uji Kompetensi Keahlian (UKK).

---

## 📌 Ringkasan Arsitektur Sistem
Aplikasi **DIDISPEN** dibangun menggunakan arsitektur **MVC (Model-View-Controller)** berbasis framework **Laravel 11.x**. Sistem mengintegrasikan antarmuka responsif ramah seluler (*mobile-first*), otomasi validasi alur perizinan dengan *State Machine*, komunikasi nirkabel perangkat keras (*Web Bluetooth* & *Webcam API*), integrasi komunikasi resmi *WhatsApp Deep-Link (`wa.me`)*, serta integrasi identitas terpadu *SSO SiPintu*.

Berikut adalah rincian seluruh perangkat lunak, teknologi, bahasa pemrograman, pustaka (*library*), serta protokol yang digunakan di dalam sistem ini beserta **pengertian** dan **fungsinya**.

---

## 1. ⚙️ Backend & Server-Side Development 

### 1.1. PHP (Hypertext Preprocessor) — Versi 8.3
* **Pengertian**: Bahasa pemrograman *server-side scripting* open-source yang dirancang khusus untuk pengembangan web dinamis.
* **Fungsi di Sistem**:
  * Menjadi bahasa pemrograman inti yang mengeksekusi seluruh logika bisnis, pemrosesan data, autentikasi, serta interaksi dengan basis data.
  * Memanfaatkan fitur PHP 8 modern seperti *typed properties*, *match expression*, *nullsafe operator*, dan performa eksekusi JIT compiler yang cepat.

### 1.2. Laravel Framework — Versi 11.x
* **Pengertian**: Framework aplikasi web berbasis PHP yang menggunakan arsitektur MVC (Model-View-Controller) dengan sintaks yang elegan dan ekspresif.
* **Fungsi di Sistem**:
  * **Routing & Middleware**: Mengatur rute URL aplikasi dan mengamankan akses berdasarkan role (*Multi-Role Access Control*: Siswa, Guru Piket, Satpam, Admin).
  * **Controller & Service Layer**: Memisahkan logika alur perizinan, verifikasi, kalkulasi jam KBM, dan deteksi keterlambatan (*overdue*).
  * **Security**: Memberikan proteksi bawaan terhadap serangan CSRF (*Cross-Site Request Forgery*), SQL Injection (via PDO binding), dan XSS (*Cross-Site Scripting*).

### 1.3. Composer
* **Pengertian**: Alat manajemen dependensi (*dependency manager*) standar industri untuk bahasa pemrograman PHP.
* **Fungsi di Sistem**:
  * Mengunduh, memperbarui, dan mengelola paket/pustaka pihak ketiga (seperti dompdf, simple-qrcode, pest).
  * Mengatur *autoloading* kelas secara otomatis sesuai standar PSR-4.

### 1.4. Artisan CLI
* **Pengertian**: Antarmuka baris perintah (*Command Line Interface*) bawaan framework Laravel.
* **Fungsi di Sistem**:
  * Menjalankan migrasi basis data (`php artisan migrate`), seeder data uji coba (`php artisan db:seed`), membuat controller/model, serta membersihkan cache sistem (`php artisan optimize:clear`).
  * Membuat symbolic link publik untuk folder storage file unggahan (`php artisan storage:link`).

---

## 2. 🗄️ Basis Data (Database & Data Layer)

### 2.1. MySQL / MariaDB (Versi >= 10.4 / 8.0)
* **Pengertian**: Sistem manajemen basis data relasional (RDBMS) berbasis SQL (*Structured Query Language*) yang bersifat open-source dan mendukung transaksi data terstruktur (ACID).
* **Fungsi di Sistem**:
  * Menyimpan seluruh data operasional sekolah: tabel pengguna (*users*), siswa, guru, jadwal piket, permohonan dispensasi, log audit, hingga riwayat scanning pos gerbang.
  * Menjaga integritas data antar-entitas menggunakan *Foreign Key Constraints* dengan mesin penyimpanan InnoDB.

### 2.2. Eloquent ORM (Object-Relational Mapping)
* **Pengertian**: Fitur bawaan Laravel yang memetakan tabel database ke dalam bentuk objek/kelas PHP.
* **Fungsi di Sistem**:
  * Mempermudah manipulasi dan kueri data tanpa menulis SQL mentah secara manual (`Dispensasi::with('siswa', 'guru')->where('status', 'menunggu')->get()`).
  * Mengelola relasi antar-tabel (*One-to-One*, *One-to-Many*, *BelongsTo*).

---

## 3. 🎨 Frontend & Antarmuka Pengguna (UI/UX)

### 3.1. Blade Templating Engine
* **Pengertian**: Mesin template bawaan Laravel yang memungkinkan penulisan kode HTML yang disisipi sintaks PHP ringan secara aman dan modular.
* **Fungsi di Sistem**:
  * Menyediakan sistem layout induk (*master layout*), komponen UI yang dapat dipakai ulang (*reusable components*), dan pewarisan tampilan (*view inheritance*) untuk dashboard setiap role.

### 3.2. Tailwind CSS (Versi 3 / 4)
* **Pengertian**: Framework CSS berbasis *utility-first* yang menyediakan sekumpulan kelas siap pakai untuk mendesain tampilan web secara cepat tanpa meninggalkan file HTML.
* **Fungsi di Sistem**:
  * Merancang antarmuka yang modern, bersih, konsisten, serta sepenuhnya responsif (*mobile-friendly*) untuk perangkat HP siswa, tablet guru, dan laptop pos satpam.

### 3.3. Alpine.js (Versi 3.x)
* **Pengertian**: Framework JavaScript yang sangat ringan (*micro-framework*) untuk menambahkan perilaku reaktif langsung di dalam tag HTML.
* **Fungsi di Sistem**:
  * Mengelola status interaktif antarmuka secara instan: buka/tutup modal dialog, tab navigasi, dropdown menu, preview gambar sebelum diunggah, dan indikator status *loading* tombol submit form.

### 3.4. Vite
* **Pengertian**: Perangkat *build tool* dan modul bundler frontend modern generasi baru yang sangat cepat dengan dukungan *Hot Module Replacement* (HMR).
* **Fungsi di Sistem**:
  * Mengompilasi dan mengoptimasi aset frontend (CSS Tailwind dan file JavaScript) agar ukuran berkas menjadi kecil dan waktu muat halaman sangat cepat.

### 3.5. Font Awesome 6
* **Pengertian**: Pustaka ikon grafis vektor berbasis web yang sangat populer.
* **Fungsi di Sistem**:
  * Menyediakan simbol visual dan ikon indikator intuitif di seluruh dashboard (ikon status disetujui, ditolak, ikon printer, bluetooth, scanner, dsb.).

### 3.6. SweetAlert2
* **Pengertian**: Pustaka JavaScript untuk menampilkan kotak dialog/pop-up peringatan interaktif, elegan, dan dapat dikustomisasi.
* **Fungsi di Sistem**:
  * Menggantikan fungsi alert/confirm bawaan browser yang kaku.
  * Memberikan konfirmasi aksi penting seperti konfirmasi persetujuan izin, penolakan dispensasi, notifikasi sukses simpan, dan konfirmasi kepulangan siswa di pos satpam.

### 3.7. Chart.js
* **Pengertian**: Pustaka JavaScript open-source untuk merender grafik visual berbasis elemen `<canvas>`.
* **Fungsi di Sistem**:
  * Menampilkan grafik statistik frekuensi dispensasi siswa (berdasarkan kategori izin, tren mingguan/bulanan, dan data per jurusan/kelas) pada dashboard Administrator.

---

## 4. 📷 Perangkat Keras & Web API Browser (Hardware Integration)

### 4.1. Web Bluetooth API & Protokol ESC/POS
* **Pengertian**: 
  * **Web Bluetooth API**: Standar API browser modern yang memungkinkan halaman web berkomunikasi secara langsung dan nirkabel dengan perangkat Bluetooth Low Energy (BLE).
  * **ESC/POS**: Standar bahasa perintah (*command set*) yang diciptakan oleh Epson untuk mengontrol printer kasir (POS/Thermal printer).
* **Fungsi di Sistem**:
  * Memungkinkan Guru Piket mencetak struk fisik dispensasi ukuran 58mm langsung dari browser Google Chrome/Edge ke printer thermal kasir portabel tanpa harus menginstal driver printer tambahan di laptop atau HP.

### 4.2. WebRTC / HTML5 MediaDevices API (`navigator.mediaDevices.getUserMedia`)
* **Pengertian**: API peramban web standar untuk mengakses perangkat keras input multimedia seperti webcam laptop atau kamera ponsel secara aman (*secure context / HTTPS*).
* **Fungsi di Sistem**:
  * Mengambil foto selfie siswa secara langsung saat pengisian form dispensasi sebagai verifikasi identitas (anti-joki).
  * Menangkap foto fisik siswa di pos gerbang oleh Satpam sebagai bukti otentik saat siswa meninggalkan sekolah.

### 4.3. Html5-QRCode Scanner Library
* **Pengertian**: Pustaka JavaScript lintas peramban (*cross-browser*) untuk mendeteksi dan mendekode kode QR dan barcode secara langsung melalui aliran video kamera web.
* **Fungsi di Sistem**:
  * Mengaktifkan kamera pada dashboard Satpam untuk memindai token QR 64-karakter pada ponsel siswa atau struk kertas saat proses Check-Out (keluar) dan Check-In (kembali).

---

## 5. 📦 Pustaka Khusus (PHP Libraries & Packages)

### 5.1. Barryvdh Laravel-Dompdf (`barryvdh/laravel-dompdf`)
* **Pengertian**: Paket pembungkus (*wrapper*) untuk pustaka Dompdf di Laravel yang merender kode HTML/CSS menjadi dokumen berformat PDF.
* **Fungsi di Sistem**:
  * Menghasilkan dokumen resmi Surat Dispensasi digital.
  * Menghasilkan layout struk thermal 58mm versi PDF (sebagai alternatif jika perangkat tidak memiliki koneksi Bluetooth).
  * Mencetak rekapitulasi laporan bulanan dispensasi oleh Administrator.

### 5.2. Simple Software IO Simple QrCode (`simplesoftwareio/simple-qrcode`)
* **Pengertian**: Paket Laravel untuk menghasilkan gambar kode QR (*Quick Response*) berkualitas tinggi dalam format SVG maupun PNG berbasis pustaka BaconQrCode dan ekstensi PHP-GD.
* **Fungsi di Sistem**:
  * Membuat QR Code unik berisikan token acak terenkripsi 64-karakter secara otomatis begitu pengajuan dispensasi disetujui oleh Guru Piket.

---

## 6. 🌐 Integrasi Komunikasi & Layanan Eksternal (Communication & External Services)

### 6.1. WhatsApp Deep-Link Protocol (`wa.me` / Click to Chat API) & Dynamic Template Engine
* **Pengertian**: Protokol tautan langsung (*URL Scheme / Universal Link*) resmi dari WhatsApp (`https://wa.me/<nomor>?text=<pesan_terenkode>`) yang dikombinasikan dengan mesin template pesan dinamis di database. Sistem **menggunakan integrasi resmi `wa.me` secara langsung (bukan WhatsApp bot pihak ketiga seperti Fonnte/Wablas)**.
* **Fungsi di Sistem**:
  * **Komunikasi Cepat, Aman, & Anti-Banned**: Menghubungkan pengguna (Siswa, Guru Piket, Satpam) langsung ke aplikasi WhatsApp resmi secara legal tanpa risiko nomor sekolah terblokir (*zero risk banned*).
  * **Dynamic Message Templating**: Mengisi format pesan secara otomatis dari template database (`WhatsappTemplate`) menggunakan placeholder dinamis seperti `{nama_siswa}`, `{nama_guru}`, `{nomor_surat}`, `{jam_keluar}`, dan `{jam_kembali}`.
  * **Tanpa Biaya Layanan (Zero Cost)**: Tidak memerlukan biaya langganan API gateway atau server bot pihak ketiga yang harus selalu online.
  * **Tombol Cepat Koordinasi Antar-Role**:
    * **Siswa $\rightarrow$ Guru Piket**: Siswa dapat langsung menekan tombol *"Hubungi Guru Piket"* di dashboard untuk konfirmasi izin ke ruang piket (tersedia dalam 3 menit pertama).
    * **Satpam $\rightarrow$ Siswa**: Satpam dapat langsung mengirim peringatan keterlambatan (*overdue alert*) ke siswa yang belum kembali melewati jam izin.
    * **Satpam $\rightarrow$ Guru Piket**: Satpam dapat melakukan koordinasi perizinan gerbang langsung ke Guru Piket yang sedang bertugas hari ini.
    * **Admin $\rightarrow$ Guru Piket**: Admin dapat mengirim pengingat jadwal piket harian kepada guru bersangkutan.
  * **Format Nomor Internasional Otomatis**: Helper sistem (`WhatsappMessageService`) secara otomatis mengonversi nomor lokal HP Indonesia (seperti `08...` atau `8...`) menjadi format internasional standar (`628...`).

### 6.2. SiPintu Identity Gateway (SSO & Master Data API)
* **Pengertian**: Portal identitas dan API internal sekolah SMK Negeri 1 Bangsri untuk sentralisasi data siswa dan guru.
* **Fungsi di Sistem**:
  * **Single Sign-On (SSO)**: Memungkinkan siswa dan guru masuk ke DIDISPEN menggunakan akun resmi sekolah tanpa harus registrasi ulang.
  * **Sinkronisasi Data**: Menyelaraskan master data siswa, NIS, kelas, jurusan, serta data guru secara terpadu.
  * **Fallback Login**: Mekanisme cerdas di mana jika login lokal gagal, sistem mengontak server SiPintu untuk memvalidasi kredensial dan memperbarui password lokal secara otomatis.

---

## 7. 🛠️ Alat Pengembangan, Pengujian, & Lingkungan (Dev Tools)

### 7.1. Git & GitHub
* **Pengertian**: Sistem pengendali versi terdistribusi (*Version Control System*) dan platform kolaborasi repositori kode.
* **Fungsi di Sistem**:
  * Mencatat seluruh riwayat perubahan kode sumber tim pengembang (By 3M), kolaborasi fitur melalui branch, dan pencadangan kode jarak jauh (*remote backup*).

### 7.2. Pest PHP & PHPUnit
* **Pengertian**: Kerangka kerja pengujian kode otomatis (*Automated Testing Framework*) untuk bahasa pemrograman PHP.
* **Fungsi di Sistem**:
  * Menjalankan pengujian fitur (*Feature Test*) dan unit (*Unit Test*) untuk memvalidasi alur bisnis, perhitungan jam izin, dan autentikasi agar bebas dari bug sebelum dirilis.

### 7.3. Laravel Pint & Laravel Pail
* **Pengertian**:
  * **Laravel Pint**: Linter kode berbasis PHP-CS-Fixer yang memastikan gaya penulisan kode seragam dan sesuai standar PSR-12.
  * **Laravel Pail**: Alat tailing log interaktif di terminal untuk memantau error log aplikasi secara *real-time*.

### 7.4. Concurrently (NPM Package)
* **Pengertian**: Utilitas Node.js yang memungkinkan eksekusi beberapa perintah terminal secara bersamaan dalam satu jendela konsol.
* **Fungsi di Sistem**:
  * Digunakan pada perintah `npm run dev` untuk menjalankan server PHP artisan, antrean queue listener, log pail, dan Vite build secara simultan.

---

## 8. 🗺️ Pemetaan Alur Fitur ke Kode Program (Code Mapping by Role)

Bagian ini memetakan setiap aksi operasional pengguna (*Siswa*, *Guru Piket*, *Satpam*, dan *Admin*) secara langsung ke berkas kode program yang mengeksekusinya (Route, Controller, Method, Model, Service, dan View):

---

### 8.1. 👨‍🎓 Alur Pengajuan Siswa (Siswa Mengajukan Dispensasi)
* **Alur Pengguna**:
  1. Siswa membuka menu buat pengajuan $\rightarrow$ mengisi alasan, tujuan, rentang jam KBM, dan mengambil foto selfie verifikasi wajah via kamera.
  2. Siswa menekan tombol **"Kirim Pengajuan"**.
  3. Status tercatat sebagai `menunggu`, nomor surat unik diterbitkan, dan tombol cepat WhatsApp `wa.me` ke Guru Piket aktif selama 3 menit.
* **Lokasi Kode Program**:
  * **Route Web**: [routes/web.php](file:///home/zainal/Documents/Digipen/routes/web.php#L186-L209) (`Route::prefix('siswa')`)
    * `GET /siswa/pengajuan/buat` $\rightarrow$ `name('siswa.pengajuan.create')`
    * `POST /siswa/pengajuan` $\rightarrow$ `name('siswa.pengajuan.store')`
    * `GET /siswa/pengajuan/{dispensasi}` $\rightarrow$ `name('siswa.pengajuan.show')`
    * `GET /siswa/pengajuan/{dispensasi}/hubungi-guru-piket` $\rightarrow$ `name('siswa.pengajuan.hubungi-guru-piket')`
  * **Controller**: [app/Http/Controllers/Siswa/PengajuanController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Siswa/PengajuanController.php)
    * `create()`: Memeriksa profil siswa, mengambil pengaturan jam KBM dan jadwal pelajaran hari ini dari helper.
    * `store()`: Validasi form, validasi jam KBM (`DispensasiTimeHelper::isWithinDispensasiTime()`), penyimpanan foto selfie ke disk `public/foto_verifikasi`, dan pembuatan record `Dispensasi::create()`.
    * `hubungiGuruPiket()`: Membuat tautan WhatsApp `wa.me` otomatis melalui `WhatsappMessageService`.
  * **Model**: [app/Models/Dispensasi.php](file:///home/zainal/Documents/Digipen/app/Models/Dispensasi.php) (`generateNomorSurat()`, relasi `siswa()`, `guru()`).
  * **Helper & Service**:
    * [app/Helpers/TimeHelper.php](file:///home/zainal/Documents/Digipen/app/Helpers/TimeHelper.php) & [app/Helpers/DispensasiTimeHelper.php](file:///home/zainal/Documents/Digipen/app/Helpers/DispensasiTimeHelper.php): Validasi jam KBM dan jadwal pelajaran.
    * [app/Services/AuditLogService.php](file:///home/zainal/Documents/Digipen/app/Services/AuditLogService.php): Mencatat aktivitas pengajuan ke audit log.
  * **Tampilan (View)**:
    * Form Input: [resources/views/siswa/pengajuan/create.blade.php](file:///home/zainal/Documents/Digipen/resources/views/siswa/pengajuan/create.blade.php) (terintegrasi Alpine.js & Web Camera API).
    * Status & Tiket: [resources/views/siswa/pengajuan/show.blade.php](file:///home/zainal/Documents/Digipen/resources/views/siswa/pengajuan/show.blade.php).

---

### 8.2. 👨‍🏫 Alur Persetujuan Guru Piket (Approval & Cetak Struk)
* **Alur Pengguna**:
  1. Guru Piket melihat antrean dispensasi masuk berstatus `menunggu`.
  2. Guru membuka detail pengajuan: memeriksa identitas, alasan, jam keluar, dan foto selfie wajah siswa.
  3. Guru menekan tombol **"Setujui"** (atau **"Tolak"** dengan menyertakan alasan penolakan).
  4. Begitu disetujui, sistem menerbitkan **QR Code Token 64-Karakter** dinamis dan Guru dapat mencetak bukti fisik ke **Printer Thermal Bluetooth 58mm** atau mengunduh **PDF**.
* **Lokasi Kode Program**:
  * **Route Web**: [routes/web.php](file:///home/zainal/Documents/Digipen/routes/web.php#L119-L181) (`Route::prefix('guru')`)
    * `GET /guru/pengajuan` $\rightarrow$ `name('guru.pengajuan.index')`
    * `GET /guru/pengajuan/{dispensasi}` $\rightarrow$ `name('guru.pengajuan.show')`
    * `POST /guru/pengajuan/{dispensasi}/approve` $\rightarrow$ `name('guru.pengajuan.approve')`
    * `POST /guru/pengajuan/{dispensasi}/reject` $\rightarrow$ `name('guru.pengajuan.reject')`
    * `GET /guru/pengajuan/{dispensasi}/cetak-struk` $\rightarrow$ `name('guru.cetak-struk')`
    * `GET /guru/pengajuan/{dispensasi}/cetak-pdf` $\rightarrow$ `name('guru.cetak-pdf')`
  * **Controller**: [app/Http/Controllers/Guru/PengajuanController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Guru/PengajuanController.php)
    * `approve()`: Mengunci data dengan `lockForUpdate()`, memperbarui status menjadi `'disetujui'`, mengisi `guru_id`, stempel waktu `approved_at`, memanggil `$this->generateQRCode()`, dan mengirim notifikasi internal ke siswa.
    * `reject()`: Memperbarui status menjadi `'ditolak'`, stempel waktu `rejected_at`, mencatat `catatan_admin` (alasan penolakan), dan mengirim notifikasi penolakan ke siswa.
    * `generateQRCode()`: Memanggil library SimpleQrCode untuk membuat file PNG kode QR berisikan token 64 karakter di storage publik.
  * **Controller Cetak**: [app/Http/Controllers/Guru/CetakStrukController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Guru/CetakStrukController.php)
    * `index()`: Menampilkan antarmuka layout struk thermal 58mm.
    * `exportPdf()`: Mengonversi template struk menjadi dokumen PDF ukuran 58mm menggunakan `Barryvdh\DomPDF`.
  * **Komponen Web Bluetooth**: [resources/views/guru/partials/bluetooth-printer.blade.php](file:///home/zainal/Documents/Digipen/resources/views/guru/partials/bluetooth-printer.blade.php) (mengirim *raw byte stream ESC/POS* via Web Bluetooth API).
  * **Tampilan (View)**:
    * Detail & Tombol Aksi: [resources/views/guru/pengajuan/show.blade.php](file:///home/zainal/Documents/Digipen/resources/views/guru/pengajuan/show.blade.php)
    * Layout Struk: [resources/views/guru/cetak-struk.blade.php](file:///home/zainal/Documents/Digipen/resources/views/guru/cetak-struk.blade.php)

---

### 8.3. 👮‍♂️ Alur Pos Satpam (Pemindaian QR & Konfirmasi Gerbang)
* **Alur Pengguna**:
  1. Satpam membuka menu **"Scan QR Dispensasi"** (browser meminta izin kamera).
  2. Kamera memindai QR Code dari layar HP siswa atau struk kertas.
  3. Layar scanner menampilkan modal preview: data siswa, alasan, jam batas kembali, dan foto selfie verifikasi siswa.
  4. Satpam mengambil foto fisik siswa di pos gerbang dan menekan **"Konfirmasi Keluar"** (Check-Out) $\rightarrow$ status berubah `keluar`.
  5. Saat siswa kembali ke sekolah, Satpam memindai kembali QR $\rightarrow$ jika tepat waktu status berubah `selesai`, jika terlambat status ditandai **Overdue** dengan kalkulasi total menit keterlambatan.
* **Lokasi Kode Program**:
  * **Route Web**: [routes/web.php](file:///home/zainal/Documents/Digipen/routes/web.php#L214-L229) (`Route::prefix('satpam')`)
    * `GET /satpam/scan` $\rightarrow$ `name('satpam.scan')`
    * `POST /satpam/scan/verify` $\rightarrow$ `name('satpam.scan.verify')`
    * `POST /satpam/konfirmasi/{dispensasi}/keluar` $\rightarrow$ `name('satpam.konfirmasi.keluar')`
    * `POST /satpam/konfirmasi/{dispensasi}/kembali` $\rightarrow$ `name('satpam.konfirmasi.kembali')`
  * **Controller**: [app/Http/Controllers/Satpam/ScanController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Satpam/ScanController.php)
    * `index()`: Membuka view scanner kamera.
    * `verify()`: Menerima data payload string QR dari kamera, memvalidasi mode `check` (preview modal) atau eksekusi `processKeluar` / `processKembali`.
  * **Controller Cadangan**: [app/Http/Controllers/Satpam/DashboardController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Satpam/DashboardController.php) (untuk konfirmasi manual jika kamera bermasalah).
  * **Service Inti**: [app/Services/QRScanService.php](file:///home/zainal/Documents/Digipen/app/Services/QRScanService.php)
    * `parseQRData()`: Mencari record `Dispensasi` berdasarkan QR token 64-karakter atau ID perizinan.
    * `processKeluar()`: Memvalidasi status `'disetujui'`, mengubah status menjadi `'keluar'`, mengisi `waktu_keluar_aktual`, dan menyimpan foto gerbang Satpam.
    * `processKembali()`: Memvalidasi status `'keluar'`, mengubah status menjadi `'selesai'`, mengisi `waktu_masuk_aktual`, menghitung selisih keterlambatan menit (*overdue calculation*).
  * **Pustaka Client**: Library JS `html5-qrcode` pada [resources/views/satpam/scan.blade.php](file:///home/zainal/Documents/Digipen/resources/views/satpam/scan.blade.php).

---

### 8.4. 👨‍💼 Alur Administrator (Pusat Kendali Sistem)
* **Alur Pengguna**:
  1. Mengelola Master Data pengguna (Siswa, Guru, Satpam, Kelas, Jurusan).
  2. Mengatur jadwal harian Guru Piket & pembagian sesi jam kerja.
  3. Memantau seluruh rekaman perizinan & audit log keamanan.
  4. Melakukan sinkronisasi data dengan SiPintu Gateway sekolah.
  5. Mengunduh rekapitulasi laporan dispensasi (PDF & Excel).
  6. Mengatur template pesan tautan dinamis WhatsApp `wa.me`.
* **Lokasi Kode Program**:
  * **Route Web**: [routes/web.php](file:///home/zainal/Documents/Digipen/routes/web.php#L73-L113) (`Route::prefix('admin')`)
  * **Daftar Controller Admin**:
    * **Dashboard & Statistik**: [app/Http/Controllers/Admin/DashboardController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/DashboardController.php) $\rightarrow$ merender chart analitik via Chart.js.
    * **Master Data**:
      * Siswa: [app/Http/Controllers/Admin/SiswaController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/SiswaController.php)
      * Guru: [app/Http/Controllers/Admin/GuruController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/GuruController.php)
      * Satpam: [app/Http/Controllers/Admin/SatpamController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/SatpamController.php)
    * **Jadwal Guru Piket**: [app/Http/Controllers/Admin/JadwalPiketController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/JadwalPiketController.php) & [GuruPiketController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/GuruPiketController.php).
    * **Semua Pengajuan**: [app/Http/Controllers/Admin/DispensasiController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/DispensasiController.php) (monitoring, penutupan, atau penghapusan data dispensasi).
    * **Rekap Laporan**: [app/Http/Controllers/Admin/LaporanController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/LaporanController.php) (`exportPdf()` via Dompdf & `exportExcel()`).
    * **Template WhatsApp**: [app/Http/Controllers/Admin/WhatsappTemplateController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/WhatsappTemplateController.php) $\rightarrow$ mengelola tabel model `WhatsappTemplate`.
    * **Sinkronisasi SiPintu**: [app/Http/Controllers/Admin/SipintuSyncController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/SipintuSyncController.php) (memanggil `App\Services\SipintuService`).
    * **Audit Log Keamanan**: [app/Http/Controllers/Admin/AuditLogController.php](file:///home/zainal/Documents/Digipen/app/Http/Controllers/Admin/AuditLogController.php) $\rightarrow$ memantau rekaman tabel `audit_logs`.
  * **Tampilan (View)**: Terletak lengkap di dalam folder [resources/views/admin/](file:///home/zainal/Documents/Digipen/resources/views/admin/).

---

## 📊 Matriks Ringkasan Cepat Teknologi (Untuk Ujian / Presentasi)

| Nama Teknologi | Kategori | Pengertian Singkat | Peran / Fungsi Utama di Sistem |
| :--- | :--- | :--- | :--- |
| **PHP 8.3** | Backend Language | Bahasa pemrograman script server-side | Fondasi pemrosesan logika bisnis dan data di server |
| **Laravel 11.x** | Backend Framework | Framework PHP berbasis MVC | Struktur arsitektur, keamanan (CSRF/Auth), routing, & controller |
| **MySQL / MariaDB** | Database | Sistem basis data relasional (RDBMS) | Penyimpanan tabel master data, pengajuan izin, & audit log |
| **Tailwind CSS** | Frontend CSS | Utility-first CSS framework | Desain antarmuka modern, rapi, dan responsif di smartphone/PC |
| **Alpine.js** | Frontend JS | Micro-framework JavaScript reaktif | Interaktivitas UI ringan (modal, loading status, tab, dropdown) |
| **Vite** | Frontend Tool | Asset bundler & development tool | Kompilasi kilat aset CSS & JS untuk performa web maksimal |
| **Web Bluetooth API** | Web Hardware API | API komunikasi nirkabel Bluetooth browser | Mengirim instruksi cetak ESC/POS ke Mini Printer Thermal 58mm |
| **Html5-QRCode** | Client Library | Scanner QR/Barcode berbasis kamera | Memindai QR Token dispensasi di pos gerbang oleh Satpam |
| **MediaDevices API** | Browser Web API | API akses kamera laptop/ponsel | Pengambilan foto selfie siswa & bukti fisik di gerbang |
| **Dompdf** | PHP Package | Generator dokumen PDF dari HTML | Penerbitan berkas PDF Surat Izin, rekap laporan, & struk |
| **Simple QrCode** | PHP Package | Generator gambar kode QR | Menghasilkan kode QR dinamis berisi token acak 64-karakter |
| **WhatsApp wa.me** | Communication Protocol | Protokol resmi Click to Chat WhatsApp | Penghubung pesan & template dinamis perizinan langsung ke WhatsApp (tanpa bot pihak ketiga) |
| **SiPintu Gateway** | External Gateway | Portal SSO & Master Data SMKN 1 Bangsri | Autentikasi terpadu (SSO) dan sinkronisasi data siswa/guru |
| **SweetAlert2** | Client Library | Modal dialog pop-up interaktif | Notifikasi pop-up cantik untuk konfirmasi persetujuan/hapus |
| **Chart.js** | Client Library | Pustaka visualisasi grafik berbasis Canvas | Menampilkan grafik analitik pengajuan izin di dasbor Admin |

---
*Dokumentasi ini disusun oleh Tim Pengembang DIDISPEN (By 3M) — SMK Negeri 1 Bangsri.*
