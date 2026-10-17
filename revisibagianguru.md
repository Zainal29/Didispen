# Dokumentasi Revisi Bagian Guru: Pembatasan Wewenang Guru Piket & Shift Sesi

## 1. Latar Belakang & Tujuan
Sebelum revisi ini diterapkan, setiap akun dengan role `guru` yang login memiliki akses penuh untuk:
1. Menyetujui (*approve*) dan menolak (*reject*) pengajuan dispensasi siswa.
2. Membuat pengajuan dispensasi manual langsung (*auto-approved*).

Hal ini berisiko menimbulkan *abuse of authority*, tumpang tindih shift piket (*cross-shift conflict*), dan tidak akuntabel karena guru yang menyetujui dispensasi belum tentu guru yang sedang menjaga pos meja piket fisik pada saat kejadian berlangsung.

Tujuan revisi ini:
- **Penegakan SOP Piket Sekolah**: Hanya guru yang sedang bertugas piket pada sesi yang aktif yang berhak memproses permohonan dispensasi siswa.
- **Diferensiasi Peran (Piket Aktif vs Pemantau)**: Guru non-piket atau guru yang sedang di luar jam piketnya masuk ke **Mode Pemantau (Read-Only)** untuk memantau siswa secara *real-time*.
- **Disiplin Sesi & Pelimpahan Terkontrol**: Menjamin pengajuan diproses sesuai shift aktif dan menangani kasus pengajuan tertunda (*rollover*) dari sesi sebelumnya tanpa memblokir siswa.
- **Integritas Formulir Manual**: Fitur penerbitan dispensasi manual hanya dapat dilakukan oleh guru piket yang sedang bertugas.

---

## 2. Aturan Bisnis & Logika Otorisasi

### A. Pembagian Hak Akses
1. **Guru Piket Bertugas (Sesi Aktif):**
   - Mendapatkan badge status: 🟢 **Sedang Bertugas Aktif (Nama Sesi & Rentang Jam)**.
   - Berhak menekan tombol **Setuju** dan **Tolak** pada dispensasi berstatus `menunggu`.
   - Berhak mengakses form dan menerbitkan **Dispensasi Manual**.
   - Berhak melakukan Scan QR darurat dan konfirmasi status.
2. **Guru Non-Piket / Di Luar Jam Piket:**
   - Mendapatkan badge status: 👁️ **Mode Pemantau (Read-Only)**.
   - Tombol **Setuju** dan **Tolak** dinonaktifkan / disembunyikan dengan tooltip informatif.
   - Tombol **Buat Pengajuan Baru** disembunyikan.
   - Akses URL langsung ke `POST /guru/pengajuan/{dispensasi}/approve`, `POST /guru/pengajuan/{dispensasi}/reject`, dan `GET /guru/pengajuan/buat` diblokir di tingkat Controller (`403 Forbidden` / dialihkan dengan pesan error).

### B. Aturan Antar-Sesi (Shift Sesi) & Pelimpahan Sesi
1. **Penentuan Hak Persetujuan Berbasis Sesi Aktif Saat Ini (*Real-Time Session*):**
   - Guru Sesi 1 berhak memproses pengajuan selama jam Sesi 1 berlangsung.
   - Guru Sesi 2 baru berhak memproses pengajuan ketika jam Sesi 2 telah dimulai.
2. **Penanganan Kasus Pengajuan Lintas Sesi (Contoh: Dispen Jam 07.00 Sesi 1, Kembali Jam 09.00 Sesi 2):**
   - **Saat Berangkat Keluar (Jam 07.00 - Sesi 1):** Guru Sesi 1 yang melakukan verifikasi fisik dan persetujuan (*approval*).
   - **Saat Kembali (Jam 09.00 - Sesi 2):** Siswa kembali melalui pos Satpam dengan Scan QR. Status otomatis `selesai` tanpa perlu persetujuan ulang dari Guru Sesi 2.
   - **Pemantauan Keterlambatan:** Jika siswa melewati batas waktu kembali di Sesi 2, siswa tampil di daftar "Siswa Terlambat" Guru Sesi 2 untuk dihubungi via WhatsApp.
3. **Pelimpahan Pengajuan Tertunda (*Rollover Pending*):**
   - Jika terdapat pengajuan berstatus `menunggu` dari Sesi 1 yang belum sempat diproses oleh Guru Sesi 1 hingga waktu Sesi 2 tiba, **Guru Sesi 2 diizinkan menyetujui pengajuan tersebut**. Hal ini mencegah siswa terlantar karena pergantian jam dinas guru.

### C. Pertukaran Piket (*Piket Swap*)
1. Sistem pertukaran jadwal piket yang disetujui (`status = 'disetujui'`, `is_active = true`) secara otomatis memindahkan hak piket aktif kepada **Guru Pengganti**.
2. **Guru Asal** kehilangan hak eksekusi pada sesi tersebut.
3. **Guru Pengganti** memperoleh wewenang penuh di sesi yang digantikannya, tanpa menghapus jadwal resmi miliknya di sesi lain (dapat bertugas *double shift* jika jadwal resminya di sesi berikutnya).
4. Dropdown pilihan jadwal pada halaman pengajuan tukar jadwal menampilkan nama sesi dengan jelas:  
   `Hari — Nama Sesi (Jam Mulai - Jam Selesai WIB)`.

### D. Fitur Buat Manual
1. Fitur buat manual (`/guru/pengajuan/buat`) dikhususkan untuk guru piket yang sedang bertugas pada sesi aktif.
2. Dibatasi untuk pengajuan **Individu/Pribadi**.

### E. Indikator "Anda" pada Daftar Guru Piket (Saran g1)
1. Pada kartu daftar guru piket hari ini di Dashboard, jika guru yang tercantum merupakan guru yang sedang login (`auth()->user()->guru->id === $p['guru']->id`), kartu diberikan aksen khusus:
   - Badge `Anda` (biru) di samping nama guru.
   - Border dan ring biru beraksen lembut (`border-blue-400 bg-blue-50/20 ring-1 ring-blue-300/30`).
   - Avatar inisial berlatar biru aktif (`bg-blue-600 text-white`).
2. Memudahkan guru mengenali status jadwalnya secara instan tanpa perlu mencari namanya secara manual.

### F. Graceful Locked Mode pada Formulir Pengajuan (Saran g2)
1. Ketika guru non-piket atau guru di luar jam piket membuka URL/menu Buat Dispensasi (`GET /guru/pengajuan/buat`):
   - Sistem **tidak mengusir/melempar balik** guru secara tiba-tiba dengan pesan merah, melainkan menampilkan halaman form dalam keadaan terkunci (**Mode Pemantau**).
   - Terdapat **Banner Peringatan Informatif (Amber)** di bagian atas form yang menjelaskan bahwa formulir terkunci khusus untuk guru piket yang bertugas pada sesi berjalan, dilengkapi tombol cepat `Kembali ke Dashboard` dan `Ajukan Tukar Jadwal Piket`.
   - Seluruh section isian form (Section 1 s/d 7) dibungkus `<fieldset disabled>` sehingga tidak dapat diisi atau diubah (`opacity-75 select-none pointer-events-none`).
   - Tombol kirim pengajuan digantikan dengan tombol disabled: `Formulir Terkunci (Khusus Guru Piket Aktif)` serta tombol kembali ke dashboard.
2. **Keamanan Backend Berlapis (Defense-in-Depth):**
   - Method `PengajuanController@store` (`POST /guru/pengajuan`) tetap memvalidasi hak otorisasi secara ketat via `GuruPiketService::canGuruCreateManualDispensasi`. Jika ada manipulasi request dari luar, tetap ditolak dan dialihkan dengan pesan error.
3. Pada Sidebar Desktop & Mobile Bottom Sheet navigasi Guru:
   - Tautan menu "Buat Dispensasi" menampilkan badge `[Piket]` dengan ikon gembok jika guru sedang tidak bertugas aktif.

---

## 3. Rincian Teknis Implementasi

1. **`app/Services/GuruPiketService.php`**:
   - `isGuruBertugasSaatIni(Guru $guru, $datetime = null): bool`  
     Memeriksa apakah guru merupakan petugas aktual (guru resmi tanpa pengganti, atau guru pengganti yang aktif) pada sesi piket yang sedang berjalan detik ini.
   - `canGuruApproveDispensasi(Guru $guru, Dispensasi $dispensasi, $datetime = null): array{allowed: bool, reason: ?string}`  
     Memvalidasi apakah guru berhak menyetujui/menolak dispensasi tertentu (memeriksa keaktifan guru, sesi berjalan, dan status dispensasi).
   - `canGuruCreateManualDispensasi(Guru $guru, $datetime = null): array{allowed: bool, reason: ?string}`  
     Memvalidasi apakah guru berhak membuka form dan menyimpan dispensasi manual.

2. **`app/Http/Controllers/Guru/PengajuanController.php`**:
   - `create()`: Memeriksa otorisasi via `canGuruCreateManualDispensasi`, meneruskan variabel `$isSedangBertugasAktif` dan `$kunciReason` ke view `guru.pengajuan.create` tanpa redirect, sehingga guru non-piket tetap dapat melihat form dalam mode terkunci.
   - `store()`: Tetap memvalidasi ketat via `canGuruCreateManualDispensasi`. Jika tidak berhak, redirect ke `guru.dashboard` dengan pesan error.
   - `approve()` & `reject()`: Validasi via `canGuruApproveDispensasi`.
   - `show()` & `index()`: Pass status `$isSedangBertugasAktif` ke Blade.

3. **`resources/views/guru/dashboard.blade.php`**:
   - Tambahkan **Top Status Banner**:
     - Mode Aktif: Hijau Emerald, menampilkan Nama Sesi, Rentang Jam, dan hak akses penuh.
     - Mode Pemantau: Abu-abu Slate / Indigo, menampilkan informasi bahwa saat ini dalam mode read-only.
   - Kartu guru piket: Tambahkan badge `Anda` dan highlight border untuk guru yang sedang login.
   - Sembunyikan/nonaktifkan tombol aksi `Setuju`, `Tolak`, dan `+ Buat Dispensasi` jika bukan petugas aktual sesi berjalan.

4. **`resources/views/guru/pengajuan/create.blade.php`**:
   - Tampilkan Mode Pemantau Banner (Amber) di atas form jika `$isSedangBertugasAktif === false`.
   - Bungkus form input dengan `<fieldset disabled>` jika tidak aktif bertugas.
   - Ganti tombol submit dengan tombol terkunci + navigasi dashboard.

5. **`resources/views/guru/pengajuan/show.blade.php`**:
   - Kondisikan tombol `Setujui & Generate QR` dan `Tolak`:
     - Jika `$isSedangBertugasAktif`: Tombol aktif seperti biasa.
     - Jika bukan: Tampilkan kotak informasi bahwa akun sedang dalam mode pemantau dan tidak memiliki hak eksekusi.
   - Sembunyikan tombol `Buat Dispensasi Baru` untuk non-piket.

6. **`resources/views/guru/pengajuan/index.blade.php`**:
   - Sembunyikan tombol `Buat Pengajuan Baru` jika `$isSedangBertugasAktif` bernilai `false`.

7. **`resources/views/guru/piket/swap-create.blade.php`**:
   - Perbarui label pilihan dropdown jadwal dengan menyertakan `$j->nama_sesi`.

8. **`resources/views/guru/layouts/app.blade.php`**:
   - Tambahkan badge gembok `[Piket]` pada menu "Buat Dispensasi" di sidebar desktop & mobile sheet untuk guru yang sedang tidak bertugas aktif.

---

## 4. Penanganan Kadaluarsa Otomatis Real-Time (Tanpa Tunggu KBM Selesai)

### A. Masalah Sebelumnya
Ketika siswa mengajukan izin keluar (contoh: 09:00 - 09:03) dan telah disetujui Guru Piket (`status = 'disetujui'`), tetapi siswa tidak kunjung keluar hingga jam kembali terlewati (contoh: jam 09:07):
1. Sistem sebelumnya baru mengkadaluarsakan pengajuan saat jam KBM sekolah hari itu usai (setelah pukul 15:10 WIB).
2. Di pos Satpam (`g2`), tombol merah **Scan QR Code untuk Keluar** masih tampil padahal batas waktu siswa berada di luar sudah terlewat.
3. Di portal Siswa (`g1`), QR Code masih berstatus aktif.
4. Di portal Guru (`g3`), masih bertuliskan "Disetujui. Menunggu scan keluar di Satpam".

### B. Solusi & Logika Bisnis Baru
1. **Status `disetujui` (Tanpa Scan Keluar):**
   - Jika waktu saat ini (`now()`) melewati `batas_waktu_kembali` (`$d->batas_waktu_kembali` atau `TimeHelper::getBatasWaktuKembali($d->jam_kembali)`), status **seketika berubah menjadi `kadaluarsa`**.
   - Catatan admin otomatis:  
     `"Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang hingga batas waktu kembali (HH:mm WIB) terlewati."`
   - Badge visual otomatis: `Kadaluarsa (Tidak Scan Keluar)` (oranye lembut).
   - File QR Code langsung dibersihkan via `cleanupCompletedDispensasiFiles`.

2. **Status `menunggu` (Rollover & KBM Threshold Terjaga):**
   - Tetap menunggu keputusan guru hingga jam KBM usai atau melewati hari, sehingga fitur pelimpahan antar sesi (*rollover pending*) dari Sesi 1 ke Sesi 2 tetap berjalan tanpa memblokir siswa di tengah hari.

3. **Status `keluar` (Siswa Sedang Berada di Luar):**
   - Siswa yang sudah keluar dan melewati batas waktu ditandai **TERLAMBAT / Overdue** (`isOverdue() = true`), tetapi **tidak dikadaluarsakan di tengah hari** karena siswa masih harus kembali dan melakukan scan masuk di gerbang pos satpam. Baru dikadaluarsakan jika KBM usai dan belum kembali (Selesai/Kadaluarsa Tidak Kembali).

4. **Penyesuaian Antarmuka (UI/UX):**
   - **Satpam (`satpam/dispensasi-detail.blade.php`)**:
     - Ditambahkan banner peringatan oranye **Dispensasi Kadaluarsa** di bagian atas.
     - Tombol "Scan QR Code untuk Keluar" digantikan dengan kartu pemberitahuan: *"Dispensasi Kadaluarsa ... Arahkan siswa kembali ke kelas / Guru Piket"*.
     - Method `showDetail()` memicu `autoExpireOverdueDispensasi()` dan me-refresh model secara *real-time*.
     - Method `verify()` di `ScanController` menolak verifikasi dengan status `422 Unprocessable Content` jika batas waktu telah terlewati.
   - **Guru (`guru/pengajuan/show.blade.php`)**:
     - Banner status menampilkan informasi kadaluarsa yang presisi dan menghapus tombol batalkan/aksi yang tidak relevan.
   - **Siswa (`siswa/pengajuan/show.blade.php`)**:
     - QR Code disembunyikan dan digantikan banner kadaluarsa yang menjelaskan siswa terlambat dan tidak dapat keluar gerbang.
     - Teks estimasi batas waktu menggunakan method terpusat `$dispensasi->getBatasWaktuExpiredText()`.