# Catatan Perubahan DIDISPEN (Changelog)

Semua pembaruan penting, perbaikan bug, dan peningkatan fitur pada aplikasi DIDISPEN (Digital Dispensasi Siswa) didokumentasikan dalam dokumen ini.

---

## [v2.5.2] - 2026-10-26

### 🚀 Fitur Baru & Peningkatan
- **Diferensiasi Masa Toleransi Antrean Gerbang (5 Menit) vs Terlambat Resmi:**
  - **Masa Izin Normal:** Siswa aman dan bebas peringatan keterlambatan selama berada dalam rentang waktu jam keluar hingga batas waktu kembali yang diajukan.
  - **Masa Toleransi Antrean Gerbang (Kuning / Amber):** Ketika batas jam kembali terlewati (misal 09:21 s.d. 09:25 pada batas kembali 09:20), sistem **tidak langsung memberi cap merah "Terlambat"**, melainkan menampilkan banner peringatan masa toleransi antrean gerbang (5 menit) lengkap dengan informasi sisa menit toleransi serta jam berakhirnya toleransi.
  - **Terlambat Resmi (Merah):** Setelah masa toleransi 5 menit berakhir (lewat 09:25), siswa baru resmi dinyatakan terlambat dan tercatat sebagai pelanggaran keterlambatan.
  - **Transparansi Jam di Beranda & Detail Siswa:** Informasi batas waktu kembali normal dan batas akhir toleransi gerbang ditampilkan jelas di dashboard maupun halaman detail pengajuan siswa.
- **Penyelarasan & Sinkronisasi Waktu Alur Status Multi-Portal:**
  - Memperbaiki bug tampilan waktu persetujuan Guru Piket pada portal Siswa, Guru, dan Satpam yang sebelumnya keliru merujuk ke `updated_at` menjadi `approved_at`.
  - Mencegah jam persetujuan guru tertimpa atau tergeser saat data dispensasi mengalami pembaruan status di kemudian hari (misal saat scan keluar, bot warning, atau refresh).
  - Alur riwayat status (Pengajuan Dibuat $\rightarrow$ Disetujui Guru $\rightarrow$ Konfirmasi Keluar Satpam $\rightarrow$ Konfirmasi Kembali Satpam) kini 100% konsisten, logis, dan selaras di seluruh portal Siswa, Guru Piket, Satpam Gerbang, dan Administrator.
- **Penyempurnaan Cetak Struk & Web Bluetooth:**
  - Perbaikan error 500 saat cetak struk dengan rendering langsung blade view thermal dan fallback SVG QR code on-the-fly.
  - Penyesuaian antarmuka cetak guru untuk memprioritaskan koneksi Bluetooth BLE langsung pada Android serta panduan khusus browser Bluefy untuk iOS.
- **Peningkatan Laporan Admin & PWA Splash:**
  - Penambahan filter dan counting dispensasi kadaluarsa yang tidak scan keluar pada rekapitulasi, cetak PDF, dan ekspor Excel laporan admin.
  - Penyederhanaan transisi splash screen PWA Android agar ikon dan warna status bar tampil proporsional tanpa overlay ganda.
- **Pembaruan Notifikasi WhatsApp Koordinator Guru Piket (Seluruh Sesi Hari Ini):**
  - Notifikasi pengingat 1x sehari di pagi hari kepada Koordinator Guru Piket kini merangkum **seluruh sesi dan seluruh petugas piket hari tersebut (dari Sesi 1 sampai selesai KBM/pulang)**.
  - Setiap sesi dirinci berdasarkan nama sesi, rentang jam dinas (WIB), dan nama petugas aktual (termasuk deteksi otomatis guru pengganti).
  - Memudahkan Koordinator memegang jadwal komprehensif sejak awal hari guna memastikan pos piket selalu terisi tanpa harus menunggu sesi berganti.

---

## [v2.5.1] - 2026-10-24

### 🚀 Fitur Baru & Peningkatan
- **Optimasi Kamera & Anti-Reload (Bebas Crash untuk Semua HP):**
  - **Pencegahan Low Memory Killer (LMK):** Menghilangkan atribut `capture="user"` kaku pada input berkas yang sebelumnya memaksa Android membuka aplikasi kamera bawaan yang berat (48MP/50MP) sehingga mematikan proses browser di latar belakang pada HP RAM 2GB-4GB.
  - **Lightweight Stream Constraints:** Mengoptimalkan resolusi live camera WebRTC ke rasio ideal $640\times480$ (maksimal 720p) yang terbuka instan (<200ms) tanpa membebani GPU/RAM perangkat hemat daya.
  - **Native Downscaling (createImageBitmap):** Mengimplementasikan decode langsung pada resolusi target tanpa memuat buffer mentah 200MB+ ke dalam JavaScript heap, disertai pembersihan kanvas dan memory cleanup seketika.
  - **Pemulihan Otomatis Isian Formulir (Auto-Save Draft):** Seluruh isian formulir (alasan, tujuan, nomor telepon, jam, kategori, dll.) tersimpan otomatis di `localStorage`. Jika browser tanpa sengaja ter-reload, data langsung dipulihkan lengkap dengan tombol opsi "Reset Formulir".
  - **Session Backup Selfie:** Foto selfie terverifikasi di-backup sementara ke `sessionStorage` sehingga siswa tidak perlu mengambil foto ulang jika terjadi rotasi layar atau jeda singkat.
  - **Deteksi & Panduan In-App Browser (WhatsApp / Instagram):** Menampilkan banner edukatif ramah pengguna ketika membuka tautan di dalam WebView aplikasi perpesanan untuk menyarankan opsi *"Buka di Chrome / Safari"* demi kestabilan maksimal.
  - **Dukungan Penuh iOS & Android:** Penambahan atribut `webkit-playsinline` untuk mencegah video pemotretan live terlempar ke layar penuh pemutar media iOS.

---

## [v2.5.0] - 2026-10-23

### 🚀 Fitur Baru & Peningkatan
- **Penyelarasan Aturan Keterlambatan Transparan:**
  - Siswa yang kembali melewati target waktu izin (`waktu_kembali_aktual > batas_waktu_kembali`) resmi dicatat dan dikategorikan sebagai **Terlambat** di seluruh rekapitulasi laporan, ringkasan statistik, dan badge status visual.
  - Toleransi keterlambatan 5 menit pos satpam diposisikan sebagai **Grace Period Gerbang (Jaga-jaga Antrean)** untuk mencegah status dispensasi kadaluarsa/kabur.
  - Tampilan detail timeline dilengkapi rincian durasi keterlambatan beserta sisa toleransi antrean gerbang (contoh: *Terlambat 4m (Dalam batas toleransi gerbang — sisa 1 mnt toleransi)*).
- **Kombinasi Cerdas Batas Kadaluarsa Menunggu ACC (Smart Hybrid MAX):**
  - Batas waktu kadaluarsa permohonan status `menunggu` dihitung secara cerdas menggunakan $\max(\text{created\_at} + 20\text{m}, \text{jam\_keluar} + 20\text{m})$.
  - Permohonan yang diajukan jauh-jauh waktu (advance) tidak akan hangus secara prematur sebelum jam izin keluar dimulai.
- **Proteksi Anti Double-Scan 4 Lapis & Mode Cepat (Satpam Express) 1.5s:**
  - **Lapis 1 (Frontend):** Cooldown 5 detik per-QR token pemindaian kamera.
  - **Lapis 2 (Backend Cache):** Cache lock 5 detik per ID dispensasi untuk mencegah request paralel.
  - **Lapis 3 (Physical Real-Time Cooldown):** Pemeriksaan jeda waktu nyata minimum 60 detik (1 menit) antara konfirmasi KELUAR dan KEMBALI.
  - **Lapis 4 (Database Transaction):** Kunci transaksional `lockForUpdate` untuk menjamin integritas status atomik.
  - **Satpam Express:** Pemangkasan jeda overlay notifikasi (1.2 detik) dan reset kamera (1 detik) sehingga antrean gerbang mengalir cepat (~1.5 detik per siswa).
- **Penyempurnaan Tampilan Portal Admin & Laporan:**
  - Desain modern bernuansa Royal Blue (`#2563eb`) & Slate dengan Tabler Icons seragam.
  - Opsi filter realisasi cetak laporan: Sedang Keluar, Kembali Tepat Waktu / Lebih Awal, Terlambat, Izin Sampai Pulang, dan Kadaluarsa (Tidak Scan Kembali).
  - Single desktop logout pada dropdown header profil.
  - Perbaikan histori jam persetujuan Guru Piket menggunakan `approved_at`.

---

## [v2.4.0] - 2026-10-22

### 🚀 Fitur Baru & Peningkatan
- **Toleransi 20 Menit & Status Kadaluarsa Otomatis:**
  - Siswa yang disetujui memiliki waktu toleransi 20 menit untuk scan keluar ke satpam sebelum izin berstatus kadaluarsa.
  - Command scheduler otomatis untuk mendeteksi dispensasi overdue dan kadaluarsa.
- **Notifikasi Layar & Anti-Keluar HP Siswa:**
  - Peringatan status bar dan pengingat kembali jika aplikasi di-minimize.
  - Dialog konfirmasi pencegah tutup tab saat izin dispensasi masih aktif berjalan.
- **Mode Pos Satpam Express & Screen Wake Lock:**
  - Penyimpanan preferensi Mode Cepat di local storage satpam.
  - Dukungan Screen Wake Lock API untuk menjaga layar HP satpam tetap menyala saat bertugas.
- **Fitur Ingat Saya & Tampilan Responsif:**
  - Checkbox Ingat Saya (Remember Me) pada halaman login.
  - Penyesuaian responsivitas badge status dan nomor surat di dashboard siswa, guru, dan satpam.

---

## [v2.3.0] - 2026-09-15

### 🚀 Fitur Baru & Peningkatan
- **Dukungan Dispensasi Rombongan:**
  - Pengajuan izin kelompok untuk perwakilan lomba, dinas, atau ekstrakurikuler dalam satu nomor surat resmi.
- **Integrasi WhatsApp Gateway:**
  - Pengiriman notifikasi WhatsApp ke Guru Piket saat siswa mengajukan dispensasi.
  - Pengingat otomatis saat siswa melewati batas waktu kembali.

---

## [v2.0.0] - 2026-08-01

### 🚀 Rilis Awal DIDISPEN
- Digitalisasi permohonan dispensasi siswa berbasis QR Code interaktif.
- Role pengguna terintegrasi: Siswa, Guru Piket, Satpam Gerbang, dan Administrator.
