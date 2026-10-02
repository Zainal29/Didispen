# LAMPIRAN DAN LEMBAR KERJA PENGEMBANGAN SCRUM
## SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)
**SMK NEGERI 1 BANGSRI**

Dokumentasi lengkap kerangka kerja Scrum (*Agile Development*) yang disesuaikan secara presisi dengan arsitektur, fitur, dan implementasi nyata dari aplikasi web **DIDISPEN**.

---

## A.1 Product Vision Board

| Elemen | Isian |
| :--- | :--- |
| **Nama Produk** | **DIDISPEN** (*Digital Dispensasi Pendidikan*) — SMKN 1 Bangsri |
| **Visi Produk** | Menyediakan platform web terpusat pengelolaan perizinan dispensasi siswa yang aman, akuntabel, dan terintegrasi secara *real-time* berbasis QR Code dan WhatsApp Gateway, guna memangkas birokrasi manual, mencegah pemalsuan surat izin, memantau pergerakan siswa di pos gerbang, serta menyajikan analitik kedisiplinan sekolah secara transparan. |
| **Target Pengguna** | 1. **Siswa**: Pengajuan izin individu/rombongan, upload bukti, pantau status, dan tunjukkan QR token.<br>2. **Guru & Guru Piket**: Verifikasi/approval izin, cetak struk thermal 58mm/PDF, kelola jadwal piket, dan checklog dinas.<br>3. **Satpam**: Scan QR Code di gerbang sekolah saat keluar/kembali, ambil foto verifikasi, dan pantau batas waktu (*overdue*).<br>4. **Administrator**: Manajemen master data (siswa/guru/kelas/jurusan), monitoring audit log, integrasi SiPintu Gateway, template WhatsApp, dan ekspor laporan. |
| **Masalah Utama** | 1. Blanko surat dispensasi kertas rawan hilang, rusak, atau dipalsukan tanda tangannya.<br>2. Guru piket dan wali kelas kesulitan memantau apakah siswa benar-benar sudah kembali ke kelas atau mangkir.<br>3. Satpam di gerbang tidak memiliki rujukan data valid secara instan mengenai siswa yang diizinkan keluar.<br>4. Rekapitulasi bulanan kedisiplinan dan absensi dispensasi lambat serta membutuhkan waktu berhari-hari. |
| **Manfaat Utama** | • **Zero Paper & Anti Pemalsuan**: Pengajuan digital dengan QR Token terenkripsi dan batas waktu otomatis.<br>• **Pengawasan Gerbang Akurat**: Validasi fisik 2 arah oleh Satpam (Keluar & Kembali) dilengkapi foto verifikasi.<br>• **Notifikasi Otomatis**: Integrasi WhatsApp Bot ke guru piket dan /wali siswa.<br>• **Single Source of Truth**: Integrasi SSO & sinkronisasi data terpusat dengan SiPintu Gateway.<br>• **Analitik Cepat**: Ekspor laporan rekap bulanan/semester ke PDF dan Excel dalam hitungan detik. |
| **Fitur Kunci** | 1. **Pengajuan Dispensasi Online** (Mandiri & Rombongan, unggah foto bukti kegiatan/surat).<br>2. **Approval & State Machine Real-Time** (Menunggu $\rightarrow$ Disetujui/Ditolak $\rightarrow$ Sedang Keluar $\rightarrow$ Sudah Kembali $\rightarrow$ Selesai).<br>3. **Verifikasi Gerbang Pos Satpam** (QR Scanner kamera HP/webcam, validasi status, dan foto verifikasi).<br>4. **Cetak Struk Dispensasi** (Printer Thermal Bluetooth/ESC-POS 58mm & Struk PDF).<br>5. **Checklog Keberadaan Guru** (Pencatatan guru keluar dinas/tugas luar dan estimasi kembali).<br>6. **Jadwal Guru Piket & Tukar Jadwal** (Manajemen sesi piket, koordinator, dan approval pertukaran).<br>7. **Integrasi SiPintu Gateway (SSO Hybrid)** (OAuth2 callback, sinkronisasi webhook real-time, real-time fallback login).<br>8. **WhatsApp Notification Gateway** (Notifikasi pengajuan, persetujuan, pengingat piket).<br>9. **Dashboard Analitik, Ekspor PDF/Excel, & Audit Trail Security**. |
| **Ukuran Keberhasilan** | • 100% peralihan perizinan dispensasi siswa dari form kertas ke sistem DIDISPEN.<br>• Kecepatan persetujuan dispensasi dari siswa mengajukan hingga disetujui guru piket < 3 menit.<br>• Waktu validasi check-in/check-out oleh Satpam di gerbang < 15 detik per siswa.<br>• Akurasi pencatatan siswa kembali tepat waktu meningkat hingga > 95% dengan deteksi otomatis status *overdue*.<br>• Ketiadaan insiden pemalsuan surat izin keluar sekolah. |

---

## A.2 Product Backlog & User Stories

### PB-01: Modul Pengajuan Dispensasi Siswa (Mandiri & Rombongan)
* **ID**: PB-01
* **Judul**: Pengajuan Dispensasi Online oleh Siswa
* **User Story**: Sebagai **Siswa**, saya ingin mengajukan dispensasi secara online melalui ponsel/web dengan melampirkan alasan, jam pelajaran/waktu, dan bukti foto kegiatan, sehingga proses perizinan menjadi cepat tanpa harus meminta paraf fisik keliling ke banyak guru.
* **Acceptance Criteria**:
  1. *Given* Siswa login ke dashboard, *When* memilih tombol "Ajukan Dispensasi", *Then* sistem menampilkan form pengajuan (pilihan jenis izin: dinas/lomba/organisasi/pribadi/sakit, rentang jam keluar & jam kembali, alasan, tujuan, dan upload foto bukti surat).
  2. *Given* Pengajuan bersifat rombongan, *When* siswa menambahkan rekan satu rombongan (pencarian berdasarkan NIS/nama siswa lain), *Then* rekan rombongan terdata dalam satu berkas pengajuan dispensasi yang sama.
  3. *Given* Siswa menekan "Kirim Pengajuan", *Then* status otomatis menjadi `menunggu`, data tersimpan ke database, dan notifikasi instan terkirim ke WhatsApp Guru Piket yang bertugas pada hari tersebut.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Master data siswa, jadwal KBM sekolah, dan limitasi kuota pengajuan per semester.

---

### PB-02: Verifikasi & Konfirmasi Guru Piket (Approval & Cetak Struk)
* **ID**: PB-02
* **Judul**: Verifikasi, Persetujuan, dan Cetak Struk oleh Guru Piket
* **User Story**: Sebagai **Guru Piket**, saya ingin memeriksa detail permohonan dispensasi siswa, menyetujui atau menolak dengan alasan tertulis, serta mencetak struk bukti izin (thermal 58mm / PDF), sehingga pengawasan izin memiliki bukti fisik yang sah dan tercatat di sistem.
* **Acceptance Criteria**:
  1. *Given* Guru Piket membuka menu "Daftar Pengajuan", *When* melihat permohonan berstatus `menunggu`, *Then* Guru Piket dapat melihat detail alasan, foto bukti, dan memilih tindakan "Setujui" atau "Tolak" (wajib mengisi alasan penolakan jika ditolak).
  2. *Given* Dispensasi disetujui, *When* Guru Piket menekan tombol "Cetak Struk", *Then* sistem memfasilitasi pencetakan ke printer thermal 58mm (ESC/POS) atau mengunduh struk PDF resmi berformat 58mm yang dilengkapi QR Token unik dan informasi batas waktu kembali.
  3. *Given* Keadaan mendesak di ruang piket, *When* Guru Piket membuatkan izin langsung di tempat bagi siswa, *Then* sistem menyediakan fitur "Buat Dispensasi Manual oleh Guru" yang langsung berstatus disetujui.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Driver printer thermal ESC/POS, parser format jam pelajaran fleksibel pada template struk.

---

### PB-03: Validasi Gerbang Sekolah & Verifikasi Kehadiran oleh Satpam
* **ID**: PB-03
* **Judul**: Scan QR Code Gerbang & Pemantauan Keberadaan Siswa oleh Satpam
* **User Story**: Sebagai **Satpam**, saya ingin memindai QR Code dispensasi siswa menggunakan kamera HP/webcam di pos jaga dan mengambil foto verifikasi, sehingga verifikasi keluar dan masuk kembali siswa tercatat secara akurat tanpa perdebatan.
* **Acceptance Criteria**:
  1. *Given* Siswa menunjukkan QR Code pada struk/layar HP di pos satpam, *When* Satpam memindai QR Code via scanner kamera web, *Then* sistem memvalidasi keabsahan izin dan menampilkan foto profil serta identitas siswa.
  2. *Given* Izin valid berstatus `disetujui`, *When* Satpam menekan tombol "Konfirmasi Keluar" (dengan opsi unggah/jepret foto verifikasi pos), *Then* status otomatis bertransisi menjadi `sedang_keluar` dengan stempel waktu keluar tercatat presisi.
  3. *Given* Siswa kembali ke sekolah, *When* Satpam memindai ulang QR Code, *Then* sistem mencocokkan jam kembali. Jika tepat waktu status bertransisi menjadi `sudah_kembali` $\rightarrow$ `selesai`. Jika melewati jam kembali, sistem menandai peringatan status `overdue` / terlambat.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Kestabilan kamera perangkat scanner di pos jaga dan pencahayaan saat malam/sore hari.

---

### PB-04: Pencatatan Keberadaan Guru (Guru Checklog Dinas Luar)
* **ID**: PB-04
* **Judul**: Pencatatan Keberadaan Guru Saat Keluar dan Masuk Sekolah
* **User Story**: Sebagai **Guru**, saya ingin mencatat keterangan saat sedang keluar lingkungan sekolah untuk dinas/tugas luar beserta estimasi waktu kembali, sehingga pihak sekolah, satpam, dan siswa mengetahui ketersediaan saya di sekolah.
* **Acceptance Criteria**:
  1. *Given* Guru berada di dashboard, *When* memilih menu "Checklog Keberadaan", *Then* sistem menampilkan form pencatatan (keperluan dinas/tugas luar/pribadi, tujuan, estimasi waktu kembali).
  2. *Given* Form disimpan, *Then* status guru pada dashboard tertera "Sedang Keluar Dinas" dan tercatat di riwayat checklog guru.
  3. *Given* Guru telah tiba kembali di sekolah, *When* menekan tombol "Konfirmasi Kembali", *Then* status ketersediaan guru kembali menjadi "Tersedia / Standby di Sekolah".
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Entitas status guru harus terpisah secara independen dari status dispensasi siswa agar tidak rancu di dashboard.

---

### PB-05: Manajemen Jadwal Guru Piket & Pertukaran Piket
* **ID**: PB-05
* **Judul**: Penjadwalan Piket Terstruktur dan Sistem Tukar Jadwal Piket
* **User Story**: Sebagai **Admin / Guru Piket**, saya ingin mengatur pembagian hari piket (Sesi Pagi/Siang, Koordinator) serta mengajukan pertukaran jadwal jika berhalangan, sehingga pos piket selalu terisi tanpa kekosongan petugas.
* **Acceptance Criteria**:
  1. *Given* Admin membuka menu Jadwal Piket, *When* menyusun jadwal mingguan, *Then* Admin dapat menetapkan guru piket per hari, sesi tugas, serta menunjuk koordinator piket harian.
  2. *Given* Guru piket berhalangan hadir pada hari tugasnya, *When* mengajukan tukar piket kepada rekan guru lain, *Then* sistem mengirimkan permintaan konfirmasi ke guru tujuan.
  3. *Given* Rekan guru menyetujui, *Then* jadwal harian otomatis diperbarui dan notifikasi pengingat WhatsApp menyesuaikan penerima baru.
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Validasi bentrok jadwal mengajar guru dengan jadwal piket.

---

### PB-06: Integrasi SiPintu Identity Gateway (SSO Hybrid & Sinkronisasi Master)
* **ID**: PB-06
* **Judul**: Integrasi Terpusat SSO Hybrid & Sinkronisasi Akun dengan SiPintu
* **User Story**: Sebagai **Siswa & Guru**, saya ingin dapat login langsung ke DIDISPEN dari Portal SiPintu (SSO tanpa ketik password) maupun login manual via form lokal DIDISPEN dengan password yang selalu sinkron, sehingga akses aplikasi terpadu dan aman.
* **Acceptance Criteria**:
  1. *Given* Pengguna login di SiPintu, *When* mengklik ikon DIDISPEN, *Then* sistem mengarahkan ke endpoint `/oauth/callback`, menukarkan authorization code dengan token, mengambil profil `/api/v1/user`, dan otomatis login ke dashboard peran masing-masing.
  2. *Given* Pengguna mengubah email, status, atau password di SiPintu, *When* webhook `POST /api/sipintu/sync-user` dikirimkan dengan signature HMAC SHA-256, *Then* DIDISPEN memperbarui akun lokal tanpa menimpa perubahan profil lokal (*Smart Conflict Resolution*) dan menyimpan hash password via query langsung tanpa *double-hashing*.
  3. *Given* Pengguna mengganti password di SiPintu lalu langsung login di form manual DIDISPEN (`/login`), *When* hash lokal belum sama, *Then* sistem melakukan *real-time fallback* ke SiPintu `/api/v1/auth/verify-credentials`, memperbarui hash lokal, dan meloloskan login pengguna.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Konektivitas jaringan antara server DIDISPEN dan server SiPintu Gateway (`sipintu.smkn1bangsri.sch.id`).

---

### PB-07: WhatsApp Gateway Notification Engine
* **ID**: PB-07
* **Judul**: Notifikasi Pesan WhatsApp Otomatis Multi-Event
* **User Story**: Sebagai ** / Siswa / Guru Piket**, saya ingin menerima notifikasi status izin dispensasi secara instan melalui WhatsApp, sehingga informasi keterlambatan atau perizinan siswa tersampaikan secara transparan.
* **Acceptance Criteria**:
  1. *Given* Siswa berhasil mengirim pengajuan dispensasi, *Then* WhatsApp Bot mengirim pesan notifikasi ke nomor WhatsApp Guru Piket yang sedang bertugas.
  2. *Given* Pengajuan disetujui atau ditolak, *Then* WhatsApp Bot mengirim notifikasi hasil verifikasi beserta link QR Code kepada siswa pemohon.
  3. *Given* Waktu izin dispensasi telah habis namun siswa belum check-in di pos gerbang, *Then* sistem otomatis mengirimkan peringatan *overdue* kepada nomor guru piket atau .
  4. *Given* Admin membuka menu Pengaturan Template WA, *Then* Admin dapat mengedit template pesan variabel dinamis (`{nama}`, `{kelas}`, `{alasan}`, `{jam_kembali}`).
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Kestabilan server Node.js WhatsApp Bot API dan koneksi internet gateway.

---

### PB-08: Dashboard Analitik, Rekapitulasi Laporan, & Audit Log Security
* **ID**: PB-08
* **Judul**: Dashboard Analitik Kedisiplinan, Ekspor Rekap (PDF/Excel), & Audit Trail
* **User Story**: Sebagai **Administrator & Kepala Sekolah**, saya ingin memantau grafik tren dispensasi, mengunduh rekapitulasi data terfilter, dan melihat jejak audit keamanan, sehingga evaluasi ketertiban sekolah berbasis data nyata (*data-driven*).
* **Acceptance Criteria**:
  1. *Given* Admin membuka Dashboard, *Then* sistem menyajikan visualisasi grafik interaktif: total dispensasi harian/mingguan/bulanan, kategori alasan terbanyak, dan pemeringkatan kelas/jurusan paling sering mengajukan izin.
  2. *Given* Admin membuka menu Laporan, *When* memfilter rentang tanggal, status, atau tingkatan kelas, *Then* laporan dapat diekspor ke file PDF resmi (siap cetak dengan kop sekolah) dan file spreadsheet Excel (.xlsx).
  3. *Given* Terjadi aktivitas sensitif (login gagal beruntun, lockout akun, perubahan status izin, sinkronisasi gateway), *Then* sistem mencatat IP address, user agent, identitas pelaku, dan payload perubahan ke tabel `audit_logs`.
* **Prioritas**: Must Have
* **Story Point**: 5
* **Dependency/Risiko**: Performa query agregasi data saat volume data dispensasi mencapai ribuan baris.

---

## A.3 Sprint Planning

### Ringkasan Rencana Sprint
Pengembangan proyek DIDISPEN dipecah menjadi **2 Sprint** terfokus:

```text
┌────────────────────────────────────────────────────────┐
│ SPRINT 1 (04 Agustus – 15 Agustus 2026)                │
│ Target: Core Engine, Multi-Role Auth, & State Machine  │
│ Kapasitas: 29 Story Points                             │
└──────────────────────────┬─────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────┐
│ SPRINT 2 (18 Agustus – 29 Agustus 2026)                │
│ Target: Gate Security, Integrasi SiPintu, & Pelaporan  │
│ Kapasitas: 28 Story Points                             │
└────────────────────────────────────────────────────────┘
```

---

### Detail Sprint 1: Core Engine, Multi-Role Auth, & Alur Dispensasi
* **Periode**: 04 Agustus – 15 Agustus 2026 (2 Pekan)
* **Sprint Goal**: Menyediakan fondasi arsitektur aplikasi web DIDISPEN, sistem autentikasi multi-role (Siswa, Guru, Satpam, Admin) dengan proteksi anti-brute force, sinkronisasi master data, form pengajuan dispensasi online (PB-01), dan alur verifikasi guru piket (PB-02).
* **Kapasitas Tim**: 29 Story Points
* **Item Backlog Terpilih**:
  1. Setup Framework Laravel 11, Database Schema, & Role-based Middleware (5 pts)
  2. **PB-01**: Form Pengajuan Dispensasi Siswa Mandiri & Rombongan (8 pts)
  3. **PB-02**: Modul Verifikasi Guru Piket, Approval State Machine, & Cetak Struk 58mm (8 pts)
  4. **PB-04**: Checklog Keberadaan Guru Dinas Luar (5 pts)
  5. Security Brute-Force & Lockout System (3 pts)
* **Risiko & Hambatan**:
  - Penyesuaian fleksibilitas parser jam pelajaran sekolah (format jam KBM seperti *"Jam Pelajaran ke-4"* vs format jam absolut *"10:15"*).
  - Alur *state machine* status dispensasi yang ketat agar status tidak bisa melompat sembarangan.
* **Definition of Done (DoD) Sprint 1**:
  - [x] Seluruh kode teruji pada local development server (PHP 8.2+ / MySQL).
  - [x] CRUD pengajuan dispensasi siswa berhasil disimpan dan dapat dibuka di dashboard guru piket.
  - [x] Fitur cetak struk 58mm menghasilkan output layout yang pas tanpa terpotong teksnya.
  - [x] Lolos code review internal dan tidak ada celah SQL Injection atau bypass role middleware.

---

### Detail Sprint 2: Gate Security, Integrasi SiPintu Gateway, & Analitik
* **Periode**: 18 Agustus – 29 Agustus 2026 (2 Pekan)
* **Sprint Goal**: Menyelesaikan pemindaian QR Code pos Satpam (PB-03), integrasi SSO Hybrid dan Webhook SiPintu Gateway (PB-06), notifikasi WhatsApp Gateway (PB-07), serta modul dashboard analitik dan ekspor laporan (PB-08).
* **Kapasitas Tim**: 28 Story Points
* **Item Backlog Terpilih**:
  1. **PB-03**: QR Code Token Generator & In-Browser Camera Scanner Pos Satpam (8 pts)
  2. **PB-06**: Integrasi SiPintu SSO Hybrid, HMAC Webhook, & Real-Time Fallback Login (8 pts)
  3. **PB-05**: Modul Jadwal Piket & Tukar Piket Guru (5 pts)
  4. **PB-07**: Integrasi WhatsApp Notification Gateway (5 pts)
  5. **PB-08**: Dashboard Analitik Kedisiplinan & Ekspor Laporan PDF/Excel (5 pts)
* **Risiko & Hambatan**:
  - Kompatibilitas browser kamera satpam saat mengakses PWA via HTTPS di jaringan lokal.
  - Penanganan sinkronisasi password agar tidak terjadi *double-hashing* saat menerima webhook SiPintu.
* **Definition of Done (DoD) Sprint 2**:
  - [x] Kamera scanner pos satpam berhasil membaca QR Token < 2 detik dan merubah status ke `sedang_keluar` atau `sudah_kembali`.
  - [x] SSO SiPintu bekerja end-to-end (login sekali di SiPintu langsung masuk dashboard DIDISPEN).
  - [x] Laporan PDF ter-render rapi dengan library Dompdf dan file Excel terunduh sesuai filter.
  - [x] UAT dijalankan bersama perwakilan siswa, guru piket, dan satpam dengan status *Pass*.

---

## A.4 Daily Scrum Log

Tabel pelaksanaan Daily Scrum selama fase pengembangan:

| Tanggal | Progres Menuju Sprint Goal (Kemarin) | Rencana Hari Ini | Hambatan & Solusi / Tindakan Adaptif |
| :--- | :--- | :--- | :--- |
| **04 Agt 2026** | Inisialisasi repositori Git, konfigurasi Laravel 11, dan pembuatan skema database tabel utama (`users`, `siswas`, `gurus`, `kelas`, `jurusans`). | Merancang controller login multi-peran dan middleware pembatasan akses hak pengguna. | Menunggu standarisasi struktur NIP/NIS dari data dapodik sekolah $\rightarrow$ *Aksi*: Gunakan seeder dummy untuk testing awal. |
| **07 Agt 2026** | Modul autentikasi multi-role dan proteksi lockout 5x gagal selesai; halaman login adaptif selesai di-slicing dengan Tailwind & Alpine.js. | Membangun form pengajuan dispensasi siswa (PB-01) dan sistem unggah foto surat bukti. | Format ukuran foto bukti yang diunggah siswa berukuran besar $\rightarrow$ *Aksi*: Pasang kompresi gambar otomatis di sisi client/backend sebelum disimpan. |
| **11 Agt 2026** | Form pengajuan dispensasi siswa mandiri & rombongan berfungsi; status pengajuan awal tercatat `menunggu`. | Mengembangkan dashboard Guru Piket untuk verifikasi permohonan dan tombol Terima/Tolak (PB-02). | Dibutuhkan indikator penanda darurat jika siswa sakit di ruang UKS $\rightarrow$ *Aksi*: Tambahkan opsi kategori izin "Sakit/UKS" dengan badge warna khusus. |
| **14 Agt 2026** | State machine approval guru piket selesai; cetak struk dispensasi 58mm berhasil diimplementasikan. | Pengujian alur pengajuan Sprint 1 dan persiapan Sprint Review bersama perwakilan guru piket. | Ditemukan bug format jam pelajaran non-waktu (`Jam Pelajaran ke-4`) pada template cetak $\rightarrow$ *Aksi*: Buat helper parser label jam pelajaran fleksibel. |
| **18 Agt 2026** | Sprint 1 ditutup sukses. Memulai pengerjaan modul pemindai kamera QR Code pos Satpam (PB-03). | Mengintegrasikan library scanner HTML5-QRCode pada halaman portal Satpam. | Akses kamera di-block browser jika tidak menggunakan HTTPS $\rightarrow$ *Aksi*: Konfigurasikan sertifikat SSL lokal pada domain pengujian. |
| **21 Agt 2026** | Scanner Satpam berhasil membaca QR Code dan menampilkan profil siswa serta status izin secara instan. | Mengimplementasikan transisi status keluar/masuk gerbang dan fitur ambil foto verifikasi siswa di pos. | Satpam membutuhkan tombol aksi cepat tanpa banyak konfirmasi modal $\rightarrow$ *Aksi*: Buat tombol satu klik "Konfirmasi Keluar & Jepret Foto". |
| **25 Agt 2026** | Modul Satpam tuntas. Memulai integrasi SiPintu Gateway (SSO Hybrid, OAuth callback, dan Webhook sync). | Menulis handler OAuth token exchange dan verifikasi signature HMAC SHA-256 pada endpoint webhook. | Terjadi potensi double-hash password saat webhook diterima karena cast Laravel `'password' => 'hashed'` $\rightarrow$ *Aksi*: Update password menggunakan `DB::table()` langsung. |
| **28 Agt 2026** | SSO SiPintu dan webhook sync berfungsi 100%; modul ekspor laporan PDF/Excel dan WhatsApp notification terpasang. | Menjalankan audit sistem menyeluruh, pengujian fallback login lokal (Langkah 5), dan skenario UAT. | Koneksi WhatsApp bot sempat timeout saat nomor tujuan tidak aktif $\rightarrow$ *Aksi*: Tambahkan try-catch non-blocking agar tidak mengganggu proses simpan izin. |

---

## A.5 Sprint Review

### Komponen Evaluasi Sprint Review

| Komponen | Catatan Pelaksanaan |
| :--- | :--- |
| **Sprint Goal yang Dievaluasi** | Menyediakan ekosistem perizinan dispensasi lengkap dari pengajuan siswa, verifikasi guru piket, validasi pos satpam, integrasi SiPintu SSO, hingga pelaporan rekapitulasi data. |
| **Item yang Didemonstrasikan** | 1. **Alur Siswa**: Login SSO SiPintu $\rightarrow$ Isi form dispensasi rombongan $\rightarrow$ Upload foto bukti $\rightarrow$ Notifikasi terkirim.<br>2. **Alur Guru Piket**: Menerima pengajuan di dashboard $\rightarrow$ Setujui $\rightarrow$ Cetak struk thermal 58mm ber-QR Code.<br>3. **Alur Satpam**: Siswa memperlihatkan struk di pos $\rightarrow$ Satpam scan QR via webcam $\rightarrow$ Status berubah `sedang_keluar` $\rightarrow$ Siswa kembali $\rightarrow$ Scan ulang $\rightarrow$ Status `selesai`.<br>4. **Alur Login Mandiri (Fallback)**: Siswa login manual dengan password yang baru diubah di SiPintu $\rightarrow$ Fallback real-time berhasil memverifikasi dan memperbarui hash lokal otomatis.<br>5. **Alur Admin**: Filter tanggal laporan $\rightarrow$ Ekspor rekapitulasi ke file PDF & Excel. |
| **Masukan Stakeholder (Guru & Satpam)** | • Satpam mengapresiasi tampilan scanner gerbang yang simpel dan meminta suara indikator "BEEP" / audio alert saat scan berhasil.<br>• Guru piket menyarankan adanya tombol pintas "Panggil Siswa via WhatsApp" jika waktu dispensasi hampir habis.<br>• Kepala Tata Usaha mengapresiasi format kop resmi pada ekspor laporan PDF yang sudah sesuai standar surat dinas sekolah. |
| **Perubahan Kondisi & Analisis Risiko** | Lingkungan jaringan sekolah memiliki beberapa titik *blind spot* Wi-Fi. Aplikasi didesain sebagai PWA (*Progressive Web App*) dengan caching aset agar form tetap ringan dibuka di ponsel siswa. |
| **Backlog Baru / Adaptasi Prioritas** | Menambahkan audio beep konfirmasi pada modul scanner satpam dan integrasi tombol wa "Hubungi Guru Piket / Siswa" ke dalam antarmuka operasional. |
| **Keputusan Produk** | **Increment disetujui penuh (*Accepted*)**; sistem dinyatakan siap untuk tahap deployment dan uji coba operasional (UAT) di lingkungan SMKN 1 Bangsri. |

---

## A.6 Sprint Retrospective

Analisis evaluasi tim pengembang (*Scrum Team*) untuk continuous improvement:

```text
┌──────────────────────────────────────┬──────────────────────────────────────┐
│                START                 │                 STOP                 │
├──────────────────────────────────────┼──────────────────────────────────────┤
│ • Menulis unit test & linting PHP    │ • Menunda pengujian integrasi pihak  │
│   otomatis sebelum commit branch.    │   ketiga (Gateway) ke akhir sprint.  │
│ • Membuat standarisasi DTO / payload │ • Melakukan perubahan struktur tabel │
│   data antar-service sistem.         │   database secara ad-hoc tanpa       │
│ • Dokumentasi API endpoint langsung  │   membuat file migration baru.       │
│   saat rute selesai dibangun.        │ • Menumpuk perbaikan bug visual UI   │
│                                      │   di hari terakhir menjelang demo.   │
├──────────────────────────────────────┼──────────────────────────────────────┤
│               CONTINUE               │        ACTION ITEMS & OWNER          │
├──────────────────────────────────────┼──────────────────────────────────────┤
│ • Daily scrum 15 menit setiap pagi   │ 1. Buat checklist pengujian PWA      │
│   untuk deteksi hambatan teknis.     │    di berbagai browser ponsel        │
│ • Pair programming pada logika kritis │    (Owner: UI/UX Dev – 02 Sep 2026)  │
│   seperti state machine & webhook.   │ 2. Otomasi backup harian database     │
│ • Validasi berkala terhadap log      │    dan pembersihan file bukti lama   │
│   keamanan (audit trail) aplikasi.   │    (Owner: Backend Dev – 03 Sep 2026)│
└──────────────────────────────────────┴──────────────────────────────────────┘
```

---

## A.7 User Acceptance Testing (UAT)

Pengujian fungsionalitas menyeluruh (*End-to-End*) oleh perwakilan pengguna akhir:

| ID UAT | Skenario Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **UAT-01** | Siswa mengajukan dispensasi individu dengan data lengkap & foto bukti | NIS: `112233`, Nama: Bagas, Kelas: 11 PPLG 2, Izin: Lomba LKS, Jam: 08.00 - 12.00, Bukti: `surat_undangan.jpg` | Data pengajuan tersimpan di database, status menjadi `menunggu`, dan tampil di antrean dashboard Guru Piket secara real-time. | Data berhasil tersimpan, status `menunggu`, muncul di dashboard piket dengan badge kuning. | **PASS** |
| **UAT-02** | Guru Piket menyetujui pengajuan dan mencetak struk perizinan 58mm | ID Pengajuan: `#DSP-001`, Tindakan: Setujui, Printer: Thermal 58mm | Status permohonan berubah menjadi `disetujui`, QR Token ter-generate, dan struk fisik thermal 58mm tercetak rapi dengan QR Code yang jelas. | Status berhasil berubah `disetujui`, struk ter-print dengan QR Code valid & layout presisi. | **PASS** |
| **UAT-03** | Guru Piket menolak pengajuan dispensasi yang tidak berdasar | ID Pengajuan: `#DSP-002`, Tindakan: Tolak, Alasan: "Surat bukti tidak valid/stempel buram" | Status permohonan berubah menjadi `ditolak`, alasan penolakan tercatat di sistem, dan siswa menerima notifikasi alasan ditolak. | Status menjadi `ditolak`, pesan alasan tampil di layar siswa, siswa tidak bisa mencetak QR. | **PASS** |
| **UAT-04** | Satpam memindai QR Code di gerbang saat siswa keluar sekolah | Token QR: `#DSP-001`, Tindakan: Scan via webcam pos satpam, Ambil Foto Pos: `verifikasi_gerbang.jpg` | Scanner mendeteksi QR < 2 detik, identitas siswa muncul, status izin bertransisi dari `disetujui` menjadi `sedang_keluar`. | Scanner membaca QR seketika, profil siswa tampil, status berubah menjadi `sedang_keluar`. | **PASS** |
| **UAT-05** | Satpam memindai QR Code saat siswa kembali tepat waktu | Token QR: `#DSP-001`, Waktu kembali: 11.45 (Batas jam: 12.00) | Sistem mengenali siswa telah kembali sebelum batas waktu, status bertransisi menjadi `sudah_kembali` $\rightarrow$ `selesai`. | Status terupdate menjadi `selesai`, durasi izin tersimpan, tidak ada denda/overdue. | **PASS** |
| **UAT-06** | Deteksi status keterlambatan (*overdue*) saat siswa telat kembali | Token QR: `#DSP-003`, Waktu kembali: 13.15 (Batas jam: 12.00) | Sistem mendeteksi keterlambatan (+75 menit), menandai status dengan badge merah `overdue` / terlambat kembali. | Status ditandai terlambat secara visual dan tercatat durasi keterlambatan di riwayat. | **PASS** |
| **UAT-07** | Guru menginput pencatatan keberadaan dinas luar (*Guru Checklog*) | NIP: `19850101...`, Keperluan: "Rapat MKKS di Cabang Dinas", Estimasi Kembali: 14.00 | Data tersimpan di tabel `guru_checklog`, status guru pada dashboard sekolah berubah menjadi "Sedang Keluar Dinas". | Tercatat di rekap checklog harian, indikator ketersediaan guru terupdate real-time. | **PASS** |
| **UAT-08** | Single Sign-On (SSO) masuk dari portal SiPintu Gateway | Klik tautan aplikasi DIDISPEN pada portal SiPintu (`http://localhost:8000`) | Browser terlempar ke `/oauth/callback`, token tervalidasi, otomatis login tanpa form input, mendarat di dashboard siswa/guru. | Berhasil auto-login ke DIDISPEN sesuai peran akun di SiPintu, nama pengguna cocok. | **PASS** |
| **UAT-09** | Fallback login mandiri dengan password yang baru diubah di SiPintu | Input NIS dan Password baru di form login lokal `/login` (belum sync webhook) | DIDISPEN mendeteksi hash lokal tidak cocok $\rightarrow$ query fallback ke endpoint SiPintu $\rightarrow$ verifikasi sukses $\rightarrow$ hash lokal diperbarui $\rightarrow$ login berhasil. | Login berhasil mendarat di dashboard, hash DB lokal otomatis ter-update tanpa double hash. | **PASS** |
| **UAT-10** | Admin mengekspor laporan rekapitulasi dispensasi bulanan | Filter: Tanggal `01-08-2026` s/d `31-08-2026`, Format: PDF & Excel | Sistem mengunduh file `laporan-dispensasi.pdf` ber-kop dinas rapi dan file `laporan-dispensasi.xlsx` berisi data terstruktur. | File PDF dan Excel berhasil diunduh, seluruh data perizinan dan durasi terhitung akurat. | **PASS** |
| **UAT-11** | Pembatasan hak akses antar peran (*Role Security Enforcement*) | Akses URL: `/admin/pengaturan` saat login sebagai Siswa | Sistem memblokir akses HTTP 403 Forbidden atau me-redirect kembali ke dashboard siswa dengan alert peringatan. | Akses ditolak, diarahkan kembali ke Dashboard Siswa dengan pesan tidak memiliki otoritas. | **PASS** |

---

## A.8 Rubrik Penilaian Proyek & Bukti Pemenuhan

| Aspek | Bobot | Kriteria Baik | Pemenuhan pada Proyek DIDISPEN |
| :--- | :---: | :--- | :--- |
| **Product Vision dan Scope** | **15%** | Tujuan, pengguna, manfaat, dan batasan terdefinisi secara jelas dan terukur. | **Sangat Baik**: Visi mendigitalkan 100% perizinan dispensasi siswa SMKN 1 Bangsri terjabarkan di Product Vision Board (A.1). 4 persona pengguna (Siswa, Guru Piket, Satpam, Admin) memiliki batasan wewenang dan alur proses bisnis yang tegas. |
| **Product Backlog** | **20%** | User story terstruktur rapi, pembobotan prioritas tepat, acceptance criteria dapat diuji (*testable*). | **Sangat Baik**: Terdiri dari 8 Product Backlog Item (PB-01 s/d PB-08) dengan format standar (*As a... I want to... So that...*), estimasi Story Points (metode Fibonacci), prioritas MoSCoW, serta Acceptance Criteria berbasis skenario *Given-When-Then*. |
| **Pelaksanaan Scrum** | **20%** | Setiap event Scrum berjalan efektif dan menghasilkan keputusan serta adaptasi nyata. | **Sangat Baik**: Rangkaian Sprint Planning terbagi dalam 2 Sprint yang terukur (A.3), Daily Scrum mencatat hambatan teknis nyata beserta solusinya (A.4), Sprint Review membedah masukan stakeholder (A.5), dan Retrospective menghasilkan *action items* konkret (A.6). |
| **Kualitas Increment** | **25%** | Fitur berfungsi penuh, responsif, teruji bebas bug fatal, dan memenuhi Definition of Done (DoD). | **Sangat Baik**: Seluruh fitur inti (pengajuan dispensasi, persetujuan guru piket, cetak struk 58mm, scanner QR pos satpam, integrasi SSO Hybrid SiPintu, checklog guru, dan ekspor laporan) bekerja solid, responsif di mobile & desktop, serta lolos verifikasi sintaks PHP. |
| **Dokumentasi dan Presentasi** | **10%** | Artefak lengkap, rapi, mudah dipahami, serta alur demonstrasi sistem terstruktur logis. | **Sangat Baik**: Dokumentasi memuat lembar kerja A.1 hingga A.8 secara terpadu, skenario UAT transparan, diagram alur status dispensasi jelas, dan siap dipresentasikan di hadapan tim penilai. |
| **Kolaborasi dan Refleksi** | **10%** | Pembagian kerja adaptif, kerja sama tim solid, dan retrospective membuahkan aksi nyata. | **Sangat Baik**: Tim membagi tanggung jawab backend, frontend UI/UX, dan security integrasi secara sinergis, dengan aksi nyata mitigasi risiko jaringan sekolah dan pencegahan isu *double-hashing* password. |
