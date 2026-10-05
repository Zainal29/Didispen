# LAMPIRAN DAN LEMBAR KERJA PENGEMBANGAN SCRUM
## SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)
**SMK NEGERI 1 BANGSRI**

Dokumentasi lengkap kerangka kerja Scrum (*Agile Development*) yang disesuaikan secara presisi dengan arsitektur, fitur, dan implementasi nyata dari aplikasi web **DIDISPEN** untuk persiapan Uji Kompetensi Keahlian (UKK) & verifikasi tim penguji industri.

---

## A.1 Product Vision Board

| Elemen | Isian |
| :--- | :--- |
| **Nama Produk** | **DIDISPEN** (*Digital Dispensasi Pendidikan*) — SMKN 1 Bangsri |
| **Visi Produk** | Menyediakan platform web terpusat pengelolaan perizinan dispensasi siswa yang aman, akuntabel, dan terintegrasi secara *real-time* berbasis QR Code, verifikasi selfie biometrik, dan Dynamic WhatsApp Engine, guna memangkas birokrasi manual, mencegah pemalsuan surat izin dan joki perizinan, memantau pergerakan siswa di pos gerbang, serta menyajikan analitik kedisiplinan sekolah secara transparan. |
| **Target Pengguna** | 1. **Siswa**: Pengajuan izin mandiri berbasis jadwal KBM, upload foto selfie verifikasi wajah, upload bukti kegiatan di tujuan, pantau status, dan tunjukkan QR token.<br>2. **Guru & Guru Piket**: Verifikasi/approval izin, pembuatan dispensasi manual di ruang piket, cetak struk thermal Bluetooth 58mm/PDF, kelola jadwal piket, dan checklog dinas.<br>3. **Satpam**: Scan QR Code di gerbang via scanner browser, verifikasi kecocokan wajah siswa dengan foto selfie di layar, catat waktu keluar/kembali, dan pantau status keterlambatan (*overdue*).<br>4. **Administrator**: Manajemen master data (siswa/guru/kelas/jurusan), monitoring audit log, integrasi SiPintu Gateway, konfigurasi template WhatsApp, dan ekspor laporan. |
| **Masalah Utama** | 1. Blanko surat dispensasi kertas rawan hilang, rusak, atau dipalsukan tanda tangannya.<br>2. Adanya celah "joki izin" di mana siswa meminjam surat izin rekan lain untuk keluar sekolah.<br>3. Guru piket dan wali kelas kesulitan memantau apakah siswa benar-benar sudah kembali ke kelas atau mangkir.<br>4. Satpam di gerbang tidak memiliki rujukan data valid secara instan mengenai siswa yang diizinkan keluar.<br>5. Rekapitulasi bulanan kedisiplinan dan absensi dispensasi lambat serta membutuhkan waktu berhari-hari. |
| **Manfaat Utama** | • **Zero Paper & Anti Pemalsuan**: Pengajuan digital dengan QR Token terenkripsi 64-karakter dan batas waktu otomatis.<br>• **Anti-Joki dengan Foto Selfie Verifikasi**: Siswa wajib melampirkan selfie saat pengajuan yang diverifikasi langsung oleh Satpam di layar pos gerbang.<br>• **Pengawasan Gerbang Presisi**: Validasi 2 arah oleh Satpam (Keluar & Kembali) dengan audio alert dan pencatatan stempel waktu aktual.<br>• **Komunikasi Andal & Anti-Banned**: Integrasi Dynamic WhatsApp Engine menggunakan Official Deep-Link Protocol (`wa.me`) berbasis template dinamis database.<br>• **Single Source of Truth**: Integrasi SSO Hybrid & sinkronisasi data terpusat dengan SiPintu Identity Gateway.<br>• **Otomatisasi Penutupan KBM**: Scheduled cron task otomatis mendeteksi dan menyelesaikan dispensasi menggantung (*auto-complete, expired, canceled*).<br>• **Analitik Cepat**: Ekspor laporan rekap bulanan/semester ke PDF dan Excel dalam hitungan detik. |
| **Fitur Kunci** | 1. **Pengajuan Dispensasi Mandiri Online** (Validasi jam operasional KBM, slot jam pelajaran/HH:MM, dan selfie verifikasi wajah).<br>2. **Approval & State Machine 7-Status** (Menunggu $\rightarrow$ Disetujui/Ditolak $\rightarrow$ Keluar $\rightarrow$ Selesai $\rightarrow$ Dibatalkan $\rightarrow$ Kadaluarsa).<br>3. **Verifikasi Gerbang Pos Satpam** (QR Scanner kamera, pencocokan foto selfie siswa, audio beep, dan deteksi otomatis overdue).<br>4. **Cetak Struk Dispensasi 58mm** (Printer Thermal Bluetooth ESC/POS via Web Bluetooth API & Struk PDF 58mm).<br>5. **Checklog Keberadaan Guru Dinas Luar** (Pencatatan guru keluar dinas dan konfirmasi selesai/tiba kembali).<br>6. **Jadwal Guru Piket & Sistem Tukar Jadwal** (Manajemen sesi piket, koordinator, dan permohonan tukar piket).<br>7. **Integrasi SiPintu Identity Gateway (SSO Hybrid)** (OAuth2 callback, webhook HMAC SHA-256, real-time fallback login).<br>8. **Dynamic WhatsApp Template Engine** (Template dinamis database untuk pengingat, penolakan, overdue, dan hubungi guru piket).<br>9. **Dashboard Analitik, Ekspor PDF/Excel, & Audit Trail Security**. |
| **Ukuran Keberhasilan** | • 100% peralihan perizinan dispensasi siswa dari form kertas ke sistem DIDISPEN.<br>• Kecepatan persetujuan dispensasi dari siswa mengajukan hingga disetujui guru piket < 3 menit.<br>• Waktu validasi check-in/check-out oleh Satpam di gerbang < 10 detik per siswa.<br>• Akurasi pencatatan siswa kembali tepat waktu meningkat hingga > 95% dengan deteksi otomatis status *overdue*.<br>• Ketiadaan insiden joki atau pemalsuan surat izin keluar sekolah. |

---

## A.2 Product Backlog & User Stories

### PB-01: Modul Pengajuan Dispensasi Siswa Mandiri & Verifikasi Wajah
* **ID**: PB-01
* **Judul**: Pengajuan Dispensasi Mandiri Online oleh Siswa
* **User Story**: Sebagai **Siswa**, saya ingin mengajukan dispensasi secara online melalui ponsel/web berbasis jadwal pelajaran aktif hari ini dengan melampirkan alasan dan foto selfie wajah, sehingga proses perizinan menjadi cepat, transparan, dan tidak dapat disalahgunakan pihak lain.
* **Acceptance Criteria**:
  1. *Given* Siswa login ke dashboard saat jam operasional sekolah, *When* memilih tombol "Ajukan Dispensasi", *Then* sistem menampilkan form pengajuan (pilihan jenis izin: sakit/izin/keperluan_sekolah/lainnya, pilihan jam keluar & kembali berbasis slot jam KBM atau waktu HH:MM, alasan min 10 karakter, tujuan, nomor WhatsApp, dan pengambilan foto selfie wajah wajib via kamera).
  2. *Given* Siswa masih memiliki pengajuan berstatus aktif (`menunggu`, `disetujui`, atau `keluar`), *When* mencoba membuat pengajuan baru, *Then* sistem menolak dengan pesan peringatan (*Concurrency Lock*).
  3. *Given* Siswa menekan "Kirim Pengajuan", *Then* status otomatis menjadi `menunggu`, data tersimpan ke database, nomor surat resmi terbit, dan sistem menyajikan detail izin beserta tombol "Hubungi Guru Piket via WhatsApp" selama 3 menit pertama.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Master data siswa, jadwal KBM aktif sekolah, dan akses izin kamera browser ponsel.

---

### PB-02: Verifikasi, Persetujuan, & Cetak Struk oleh Guru Piket
* **ID**: PB-02
* **Judul**: Verifikasi, Persetujuan, dan Cetak Struk oleh Guru Piket
* **User Story**: Sebagai **Guru Piket**, saya ingin memeriksa detail permohonan dispensasi siswa, menyetujui atau menolak dengan alasan tertulis, membuatkan izin darurat di tempat, serta mencetak struk bukti izin (thermal Bluetooth 58mm / PDF), sehingga pengawasan izin memiliki bukti fisik yang sah dan tercatat di sistem.
* **Acceptance Criteria**:
  1. *Given* Guru Piket membuka menu "Daftar Pengajuan", *When* melihat permohonan berstatus `menunggu`, *Then* Guru Piket dapat melihat detail alasan, foto selfie verifikasi siswa, dan memilih tindakan "Setujui" (menghitung batas waktu & generate token QR) atau "Tolak" (wajib mengisi alasan penolakan pada catatan admin).
  2. *Given* Dispensasi disetujui, *When* Guru Piket menekan tombol "Cetak Struk", *Then* sistem memfasilitasi pencetakan ke printer thermal Bluetooth 58mm (ESC/POS) via Web Bluetooth API atau mengunduh struk PDF format 58mm dengan batas kuota cetak (maksimal 3 kali).
  3. *Given* Keadaan mendesak di mana siswa tidak membawa ponsel, *When* Guru Piket membuka menu "Buat Pengajuan Manual", *Then* Guru Piket dapat mencari siswa via AJAX Select2 dan menerbitkan izin yang langsung berstatus `disetujui` (`dibuat_manual_oleh_guru = true`).
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Dukungan Web Bluetooth API pada browser dan kompatibilitas printer thermal 58mm.

---

### PB-03: Validasi Gerbang Sekolah & Pemantauan Kehadiran oleh Satpam
* **ID**: PB-03
* **Judul**: Scan QR Code Gerbang, Pencocokan Selfie Wajah, & Deteksi Overdue
* **User Story**: Sebagai **Satpam**, saya ingin memindai QR Code dispensasi siswa menggunakan kamera browser di pos gerbang dan mencocokkan identitas dengan foto selfie siswa di layar, sehingga verifikasi keluar dan kembali siswa tercatat akurat dan mencegah joki izin.
* **Acceptance Criteria**:
  1. *Given* Siswa menunjukkan QR Code pada struk/layar HP di pos satpam, *When* Satpam memindai QR Code via scanner kamera web, *Then* sistem memvalidasi keabsahan izin dan menampilkan preview modal: foto selfie verifikasi wajah, nama, NIS, kelas, jurusan, jam izin, dan status.
  2. *Given* Izin valid berstatus `disetujui` dan wajah siswa sesuai dengan foto selfie di layar, *When* Satpam menekan tombol "Konfirmasi Keluar", *Then* status otomatis bertransisi menjadi `keluar`, stempel waktu keluar dan ID satpam tercatat, serta audio beep sukses berbunyi.
  3. *Given* Siswa kembali ke sekolah (status `keluar`), *When* Satpam memindai ulang QR Code, *Then* sistem mencocokkan waktu aktual dengan `batas_waktu_kembali`. Jika tepat waktu status bertransisi menjadi `selesai`. Jika melewati batas waktu, sistem menandai peringatan Overdue (`is_warned = true`, stempel `warned_at`) dan menghitung jumlah menit keterlambatan.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Kestabilan pencahayaan pos gerbang saat memindai QR Code dan HTTPS pada akses kamera browser.

---

### PB-04: Pencatatan Keberadaan Guru (Guru Checklog Dinas Luar)
* **ID**: PB-04
* **Judul**: Pencatatan Mobilitas Guru Saat Keluar dan Masuk Sekolah
* **User Story**: Sebagai **Guru**, saya ingin mencatat keterangan saat sedang keluar lingkungan sekolah untuk dinas/tugas luar beserta konfirmasi tiba kembali, sehingga pihak sekolah, satpam, dan siswa mengetahui ketersediaan saya di sekolah secara real-time.
* **Acceptance Criteria**:
  1. *Given* Guru berada di menu Checklog Keberadaan, *When* memilih "Catat Keluar", *Then* sistem menampilkan form pencatatan (keperluan dinas, tujuan, dan lokasi).
  2. *Given* Form disimpan, *Then* status guru tercatat `keluar` dengan stempel waktu otomatis tombol ditekan, status guru pada dashboard tertera "Sedang Keluar Dinas", dan aktivitas tercatat di audit log (`guru_check_out`).
  3. *Given* Guru telah tiba kembali di sekolah, *When* menekan tombol "Konfirmasi Tiba di Sekolah", *Then* status checklog bertransisi menjadi `selesai`, stempel waktu kembali tercatat, dan status guru di dashboard kembali menjadi "Tersedia / Standby di Sekolah".
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Validasi satu catatan keluar aktif per guru agar tidak terjadi penumpukan log tanpa check-in.

---

### PB-05: Manajemen Jadwal Guru Piket & Pertukaran Piket
* **ID**: PB-05
* **Judul**: Penjadwalan Piket Terstruktur dan Sistem Tukar Jadwal Piket
* **User Story**: Sebagai **Admin / Guru Piket**, saya ingin mengatur jadwal hari piket (Sesi Pagi/Siang, Koordinator) serta mengajukan pertukaran jadwal jika berhalangan, sehingga pos piket selalu terisi tanpa kekosongan petugas.
* **Acceptance Criteria**:
  1. *Given* Admin membuka menu Jadwal Piket, *When* menyusun jadwal mingguan, *Then* Admin dapat menetapkan guru piket per hari, sesi tugas (Pagi/Siang), serta menunjuk koordinator piket harian.
  2. *Given* Guru piket berhalangan hadir pada hari tugasnya, *When* mengajukan tukar piket kepada rekan guru lain, *Then* sistem menyimpan permohonan ke tabel `pertukaran_jadwal_piket` dengan status `menunggu`. Guru asal dapat membatalkan permohonan sebelum direspon (status `dibatalkan`).
  3. *Given* Rekan guru pengganti menyetujui, *Then* status pertukaran menjadi `disetujui`, flag `is_active` menjadi `true` pada tanggal tersebut, dan jadwal aktif otomatis digantikan oleh guru pengganti.
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Sinkronisasi tanggal berlakunya pertukaran jadwal dengan daftar guru piket harian.

---

### PB-06: Integrasi SiPintu Identity Gateway (SSO Hybrid & Sinkronisasi Master)
* **ID**: PB-06
* **Judul**: Integrasi Terpusat SSO Hybrid & Sinkronisasi Akun dengan SiPintu
* **User Story**: Sebagai **Siswa & Guru**, saya ingin dapat login langsung ke DIDISPEN dari Portal SiPintu (SSO tanpa ketik password) maupun login manual via form lokal DIDISPEN dengan kredensial yang selalu sinkron, sehingga akses aplikasi terpadu dan aman.
* **Acceptance Criteria**:
  1. *Given* Pengguna login di SiPintu, *When* mengklik ikon DIDISPEN, *Then* sistem mengarahkan ke endpoint `/oauth/callback`, menukarkan authorization code dengan token, mengambil profil `/api/v1/user`, dan otomatis login ke dashboard peran masing-masing.
  2. *Given* Pengguna mengubah email, status, atau password di SiPintu, *When* webhook `POST /api/sipintu/sync-user` dikirimkan dengan signature HMAC SHA-256, *Then* DIDISPEN memperbarui akun lokal tanpa menimpa perubahan profil lokal (*Smart Conflict Resolution*) dan menyimpan hash password via Query Builder tanpa *double-hashing*.
  3. *Given* Pengguna mengganti password di SiPintu lalu langsung login di form manual DIDISPEN (`/login`), *When* hash lokal belum sama, *Then* sistem melakukan *real-time fallback* ke SiPintu `/api/v1/auth/verify-credentials`, memperbarui hash lokal, dan meloloskan login pengguna.
* **Prioritas**: Must Have
* **Story Point**: 8
* **Dependency/Risiko**: Konektivitas jaringan antara server DIDISPEN dan server SiPintu Gateway (`sipintu.smkn1bangsri.sch.id`).

---

### PB-07: Dynamic WhatsApp Notification & Communication Engine
* **ID**: PB-07
* **Judul**: Komunikasi WhatsApp Terintegrasi Berbasis Dynamic Database Template
* **User Story**: Sebagai **Siswa / Guru Piket / Satpam**, saya ingin dapat mengirim dan menerima pesan notifikasi perizinan secara instan melalui tautan WhatsApp dinamis, sehingga komunikasi keterlambatan dan perizinan siswa tersampaikan tanpa risiko server bot terblokir.
* **Acceptance Criteria**:
  1. *Given* Admin membuka menu Pengaturan Template WA, *Then* Admin dapat mengedit konten pesan untuk berbagai event (`disetujui`, `ditolak`, `keluar`, `kembali`, `terlambat`, `pengingat-piket`, `hubungi-guru-piket`) menggunakan variabel dinamis (`{nama_siswa}`, `{nomor_surat}`, `{jam_kembali}`, `{durasi_terlambat}`).
  2. *Given* Siswa selesai mengajukan dispensasi, *When* berada di halaman detail izin pada 3 menit pertama, *Then* siswa dapat mengklik "Hubungi Guru Piket via WhatsApp" yang otomatis membuka chat dengan nomor guru piket aktif hari ini berisi format izin lengkap.
  3. *Given* Siswa terlambat kembali melewati batas jam, *When* Satpam atau Guru memproses dispensasi, *Then* sistem menyediakan tautan cepat WhatsApp peringatan keterlambatan terformat yang siap dikirimkan ke nomor siswa/wali.
* **Prioritas**: Should Have
* **Story Point**: 5
* **Dependency/Risiko**: Format nomor WhatsApp Indonesia (`08...` / `+628...`) dinormalisasi secara otomatis ke format internasional `628...`.

---

### PB-08: Dashboard Analitik, Rekapitulasi Laporan, & Audit Trail Security
* **ID**: PB-08
* **Judul**: Dashboard Analitik Kedisiplinan, Ekspor Rekap (PDF/Excel), & Audit Trail
* **User Story**: Sebagai **Administrator & Kepala Sekolah**, saya ingin memantau grafik tren dispensasi, mengunduh rekapitulasi data terfilter, dan melihat jejak audit keamanan, sehingga evaluasi ketertiban sekolah berbasis data nyata (*data-driven*).
* **Acceptance Criteria**:
  1. *Given* Admin membuka Dashboard, *Then* sistem menyajikan visualisasi grafik interaktif: total dispensasi harian/mingguan/bulanan, kategori alasan terbanyak, dan pemeringkatan kelas/jurusan paling sering mengajukan izin.
  2. *Given* Admin membuka menu Laporan, *When* memfilter rentang tanggal, status, atau jurusan/kelas, *Then* laporan dapat diekspor ke file PDF resmi (siap cetak dengan kop sekolah) dan file spreadsheet Excel (.xlsx).
  3. *Given* Terjadi aktivitas sistem (login, lockout akun, perubahan status izin, checklog guru, sinkronisasi gateway), *Then* sistem mencatat IP address, user agent, browser, OS, identitas user, dan payload ke tabel `audit_logs`.
* **Prioritas**: Must Have
* **Story Point**: 5
* **Dependency/Risiko**: Agregasi data laporan teroptimasi dengan eager loading (`with(['siswa.kelas.jurusan', 'guru'])`).

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
* **Sprint Goal**: Menyediakan fondasi arsitektur aplikasi web DIDISPEN, sistem autentikasi multi-role (Siswa, Guru, Satpam, Admin) dengan proteksi anti-brute force, migrasi skema database relasional, form pengajuan dispensasi mandiri siswa dengan selfie verifikasi wajah (PB-01), modul approval guru piket dan cetak struk 58mm (PB-02), serta checklog keberadaan guru (PB-04).
* **Kapasitas Tim**: 29 Story Points
* **Item Backlog Terpilih**:
  1. Setup Framework Laravel 11, Database Schema (`users`, `siswa`, `guru`, `jurusan`, `kelas`, `dispensasi`), & Role-based Middleware (5 pts)
  2. **PB-01**: Form Pengajuan Dispensasi Siswa Mandiri, Validasi Slot KBM, & Selfie Verifikasi Wajah (8 pts)
  3. **PB-02**: Modul Verifikasi Guru Piket, Buat Dispensasi Manual di Ruang Piket, & Cetak Struk 58mm (8 pts)
  4. **PB-04**: Checklog Keberadaan Guru Dinas Luar (5 pts)
  5. Security Brute-Force & Lockout System 5x Gagal (3 pts)
* **Risiko & Hambatan**:
  - Penyesuaian fleksibilitas parser jam pelajaran sekolah (format jam KBM seperti *"Jam Pelajaran ke-4"* vs format jam absolut *"10:15"*).
  - Alur *state machine* status dispensasi yang ketat agar status tidak bisa melompat sembarangan.
* **Definition of Done (DoD) Sprint 1**:
  - [x] Seluruh kode teruji pada local development server (PHP 8.2+ / MySQL).
  - [x] CRUD pengajuan dispensasi siswa berhasil disimpan dengan foto selfie dan dapat dibuka di dashboard guru piket.
  - [x] Fitur cetak struk 58mm menghasilkan output layout thermal yang presisi via Web Bluetooth API dan PDF Dompdf.
  - [x] Lolos code review internal dan tidak ada celah SQL Injection atau bypass role middleware.

---

### Detail Sprint 2: Gate Security, Integrasi SiPintu Gateway, & Analitik
* **Periode**: 18 Agustus – 29 Agustus 2026 (2 Pekan)
* **Sprint Goal**: Menyelesaikan pemindaian QR Code pos Satpam dengan verifikasi wajah selfie (PB-03), integrasi SSO Hybrid dan Webhook SiPintu Gateway (PB-06), Dynamic WhatsApp Message Engine (PB-07), modul jadwal & tukar piket (PB-05), scheduled task auto-complete KBM, serta modul dashboard analitik dan ekspor laporan (PB-08).
* **Kapasitas Tim**: 28 Story Points
* **Item Backlog Terpilih**:
  1. **PB-03**: QR Code Token Generator & In-Browser Camera Scanner Pos Satpam dengan Pencocokan Wajah (8 pts)
  2. **PB-06**: Integrasi SiPintu SSO Hybrid, HMAC Webhook, & Real-Time Fallback Login (8 pts)
  3. **PB-05**: Modul Jadwal Piket & Pertukaran Piket Guru (5 pts)
  4. **PB-07**: Dynamic WhatsApp Notification & Communication Engine (5 pts)
  5. **PB-08**: Dashboard Analitik Kedisiplinan & Ekspor Laporan PDF/Excel (5 pts)
* **Risiko & Hambatan**:
  - Kompatibilitas browser kamera satpam saat mengakses PWA via HTTPS di jaringan lokal.
  - Penanganan sinkronisasi password agar tidak terjadi *double-hashing* saat menerima webhook SiPintu.
* **Definition of Done (DoD) Sprint 2**:
  - [x] Kamera scanner pos satpam berhasil membaca QR Token < 2 detik, memvalidasi wajah selfie siswa di layar, dan merubah status ke `keluar` atau `selesai`.
  - [x] SSO SiPintu bekerja end-to-end (login sekali di SiPintu langsung masuk dashboard DIDISPEN).
  - [x] Laporan PDF ter-render rapi dengan library Dompdf dan file Excel terunduh sesuai filter.
  - [x] Command `dispensasi:auto-complete` berhasil menutup berkas menggantung menjadi `kadaluarsa`, `dibatalkan`, atau `selesai (Tidak Kembali)`.
  - [x] UAT dijalankan bersama perwakilan siswa, guru piket, dan satpam dengan status *Pass*.

---

## A.4 Daily Scrum Log

Tabel pelaksanaan Daily Scrum selama fase pengembangan:

| Tanggal | Progres Menuju Sprint Goal (Kemarin) | Rencana Hari Ini | Hambatan & Solusi / Tindakan Adaptif |
| :--- | :--- | :--- | :--- |
| **04 Agt 2026** | Inisialisasi repositori Git, konfigurasi Laravel 11, dan pembuatan skema database tabel utama (`users`, `siswa`, `guru`, `jurusan`, `kelas`). | Merancang controller login multi-peran dan middleware pembatasan hak akses hak pengguna. | Menunggu standarisasi struktur NIP/NIS dari data dapodik sekolah $\rightarrow$ *Aksi*: Gunakan seeder dummy untuk testing awal. |
| **07 Agt 2026** | Modul autentikasi multi-role dan proteksi lockout 5x gagal selesai; halaman login adaptif selesai di-slicing dengan Tailwind CSS. | Membangun form pengajuan dispensasi siswa mandiri (PB-01) dan modul unggah foto selfie verifikasi wajah. | Siswa berpotensi mengunggah foto selfie berukuran besar $\rightarrow$ *Aksi*: Pasang kompresi gambar otomatis sisi client/backend sebelum disimpan ke storage. |
| **11 Agt 2026** | Form pengajuan dispensasi siswa mandiri berfungsi; validasi pencegahan izin ganda (*concurrency lock*) aktif; status tercatat `menunggu`. | Mengembangkan dashboard Guru Piket untuk verifikasi permohonan dan tombol Terima/Tolak serta Buat Izin Manual (PB-02). | Siswa yang tidak membawa HP perlu dilayani cepat di ruang piket $\rightarrow$ *Aksi*: Buat fitur "Buat Pengajuan Manual Guru" dengan pencarian siswa Select2. |
| **14 Agt 2026** | State machine approval guru piket selesai; cetak struk dispensasi 58mm via Web Bluetooth API dan PDF selesai. | Pengujian alur pengajuan Sprint 1 dan persiapan Sprint Review bersama perwakilan guru piket. | Ditemukan bug format jam pelajaran non-waktu (`Jam Pelajaran ke-4`) pada template cetak $\rightarrow$ *Aksi*: Buat helper `TimeHelper` parser label jam fleksibel. |
| **18 Agt 2026** | Sprint 1 ditutup sukses. Memulai pengerjaan modul pemindai kamera QR Code pos Satpam (PB-03). | Mengintegrasikan library scanner HTML5-QRCode pada halaman portal Satpam. | Akses kamera di-block browser jika tidak menggunakan HTTPS $\rightarrow$ *Aksi*: Konfigurasikan sertifikat SSL lokal pada domain pengujian. |
| **21 Agt 2026** | Scanner Satpam berhasil membaca QR Code dan menampilkan modal preview identitas beserta foto selfie verifikasi siswa. | Mengimplementasikan transisi status keluar/masuk gerbang, audio beep sukses, dan deteksi otomatis status overdue. | Satpam membutuhkan konfirmasi visual yang tegas $\rightarrow$ *Aksi*: Tampilkan badge warna hijau untuk tepat waktu dan layar merah jika melewati batas waktu. |
| **25 Agt 2026** | Modul Satpam tuntas. Memulai integrasi SiPintu Gateway (SSO Hybrid, OAuth callback, dan Webhook sync). | Menulis handler OAuth token exchange dan verifikasi signature HMAC SHA-256 pada endpoint webhook. | Terjadi potensi double-hash password saat webhook diterima karena cast Laravel `'password' => 'hashed'` $\rightarrow$ *Aksi*: Update password menggunakan `DB::table()` langsung. |
| **28 Agt 2026** | SSO SiPintu dan webhook sync berfungsi 100%; modul ekspor laporan PDF/Excel, WhatsApp template engine, dan cron auto-complete terpasang. | Menjalankan audit sistem menyeluruh, pengujian fallback login lokal, dan skenario UAT. | Siswa yang lupa scan kembali di gerbang menggantung statusnya $\rightarrow$ *Aksi*: Jalankan command `dispensasi:auto-complete` setiap akhir KBM untuk menutup izin otomatis. |

---

## A.5 Sprint Review

### Komponen Evaluasi Sprint Review

| Komponen | Catatan Pelaksanaan |
| :--- | :--- |
| **Sprint Goal yang Dievaluasi** | Menyediakan ekosistem perizinan dispensasi lengkap dari pengajuan mandiri siswa, selfie verifikasi wajah, verifikasi guru piket, validasi pos satpam, integrasi SiPintu SSO, hingga pelaporan rekapitulasi data. |
| **Item yang Didemonstrasikan** | 1. **Alur Siswa**: Login SSO SiPintu $\rightarrow$ Isi form dispensasi berbasis jadwal KBM $\rightarrow$ Ambil foto selfie verifikasi $\rightarrow$ Pengajuan terbit dengan status `menunggu`.<br>2. **Alur Guru Piket**: Menerima pengajuan di dashboard $\rightarrow$ Setujui $\rightarrow$ Cetak struk thermal Bluetooth 58mm ber-QR Code unik.<br>3. **Alur Satpam**: Siswa memperlihatkan struk di pos $\rightarrow$ Satpam scan QR via webcam $\rightarrow$ Wajah siswa dicocokkan dengan foto selfie di layar $\rightarrow$ Konfirmasi keluar (status `keluar`) $\rightarrow$ Siswa kembali $\rightarrow$ Scan ulang $\rightarrow$ Konfirmasi kembali (status `selesai`).<br>4. **Alur Fallback Login**: Siswa login manual dengan password yang baru diubah di SiPintu $\rightarrow$ Fallback real-time berhasil memverifikasi dan memperbarui hash lokal otomatis.<br>5. **Alur Admin & Otomasi**: Filter tanggal laporan $\rightarrow$ Ekspor rekapitulasi ke file PDF & Excel $\rightarrow$ Eksekusi `dispensasi:auto-complete` untuk menutup perizinan menggantung. |
| **Masukan Stakeholder (Guru & Satpam)** | • Satpam mengapresiasi foto selfie verifikasi yang muncul di scanner karena langsung bisa membedakan siswa pemegang izin asli dengan joki.<br>• Guru piket menyarankan adanya tombol cepat "Hubungi Guru Piket via WhatsApp" di halaman siswa agar siswa tidak perlu mencari nomor manual.<br>• Kepala Tata Usaha mengapresiasi format kop resmi pada ekspor laporan PDF yang sudah sesuai standar surat dinas sekolah. |
| **Perubahan Kondisi & Analisis Risiko** | Lingkungan jaringan sekolah memiliki beberapa titik *blind spot* Wi-Fi. Sistem didesain ringan, aset di-cache, dan ukuran gambar foto selfie dikompresi agar tetap gegas dibuka di ponsel siswa. |
| **Backlog Baru / Adaptasi Prioritas** | Menambahkan audio beep konfirmasi pada modul scanner satpam dan integrasi tombol wa "Hubungi Guru Piket" dalam jendela waktu 3 menit pertama pengajuan. |
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
│ • Validasi integritas data dengan    │ • Melakukan perubahan struktur tabel │
│   Database Transaction & Concurrency │   database secara ad-hoc tanpa       │
│   Lock (lockForUpdate).              │   membuat file migration baru.       │
│ • Dokumentasi API endpoint langsung  │ • Menumpuk perbaikan bug visual UI   │
│   saat rute selesai dibangun.        │   di hari terakhir menjelang demo.   │
├──────────────────────────────────────┼──────────────────────────────────────┤
│               CONTINUE               │        ACTION ITEMS & OWNER          │
├──────────────────────────────────────┼──────────────────────────────────────┤
│ • Daily scrum 15 menit setiap pagi   │ 1. Buat checklist pengujian PWA      │
│   untuk deteksi hambatan teknis.     │    di berbagai browser ponsel        │
│ • Pair programming pada logika kritis │    (Owner: UI/UX Dev – 02 Sep 2026)  │
│   seperti state machine & webhook.   │ 2. Otomasi backup harian database     │
│ • Validasi berkala terhadap log      │    dan pembersihan file bukti lama    │
│   keamanan (audit trail) aplikasi.   │    (Owner: Backend Dev – 03 Sep 2026)│
└──────────────────────────────────────┴──────────────────────────────────────┘
```

---

## A.7 User Acceptance Testing (UAT)

Pengujian fungsionalitas menyeluruh (*End-to-End*) oleh perwakilan pengguna akhir:

| ID UAT | Skenario Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **UAT-01** | Siswa mengajukan dispensasi mandiri dengan data lengkap & foto selfie verifikasi | NIS: `112233`, Nama: Bagas, Kelas: 11 PPLG 2, Izin: Lomba LKS, Jam: 08.00 - 12.00, Foto Selfie: `selfie_verif.jpg` | Data pengajuan tersimpan di database, status menjadi `menunggu`, muncul tombol "Hubungi Guru Piket via WA" (3 mnt), dan tampil di antrean Guru Piket. | Data berhasil tersimpan, status `menunggu`, tombol WA aktif, muncul di antrean piket. | **PASS** |
| **UAT-02** | Guru Piket menyetujui pengajuan dan mencetak struk perizinan 58mm | ID Pengajuan: `#DSP-001`, Tindakan: Setujui, Printer: Thermal Bluetooth 58mm | Status permohonan berubah menjadi `disetujui`, QR Token ter-generate, dan struk fisik thermal 58mm tercetak rapi dengan QR Code yang jelas. | Status berhasil berubah `disetujui`, struk ter-print via Web Bluetooth API dengan QR valid. | **PASS** |
| **UAT-03** | Guru Piket menolak pengajuan dispensasi yang tidak berdasar | ID Pengajuan: `#DSP-002`, Tindakan: Tolak, Alasan: "Alasan tidak jelas / tidak ada surat tugas dinas" | Status permohonan berubah menjadi `ditolak`, alasan penolakan tercatat di `catatan_admin`, dan siswa menerima informasi penolakan. | Status menjadi `ditolak`, catatan admin tersimpan, siswa tidak bisa mencetak QR. | **PASS** |
| **UAT-04** | Satpam memindai QR Code di gerbang dan memverifikasi wajah siswa saat keluar | Token QR: `#DSP-001`, Tindakan: Scan via kamera satpam pos gerbang | Scanner mendeteksi QR < 2 detik, modal preview menampilkan foto selfie siswa & identitas, status izin bertransisi menjadi `keluar`. | Scanner membaca QR seketika, foto selfie tampil di layar, status berubah menjadi `keluar`. | **PASS** |
| **UAT-05** | Satpam memindai QR Code saat siswa kembali tepat waktu | Token QR: `#DSP-001`, Waktu kembali: 11.45 (Batas jam: 12.00) | Sistem mengenali siswa telah kembali sebelum batas waktu, status bertransisi menjadi `selesai`, stempel waktu kembali tercatat. | Status terupdate menjadi `selesai`, stempel waktu kembali tersimpan, indikator hijau tepat waktu. | **PASS** |
| **UAT-06** | Deteksi status keterlambatan (*overdue*) saat siswa telat kembali | Token QR: `#DSP-003`, Waktu kembali: 13.15 (Batas jam: 12.00) | Sistem mendeteksi keterlambatan (+75 menit), status menjadi `selesai`, ditandai `is_warned = true`, dan muncul badge merah Overdue. | Status `selesai` ditandai terlambat secara visual dan durasi keterlambatan terhitung akurat. | **PASS** |
| **UAT-07** | Guru menginput pencatatan keberadaan dinas luar (*Guru Checklog*) | NIP: `19850101...`, Keperluan: "Rapat MKKS di Cabang Dinas", Lokasi: "SMKN 1 Jepara" | Data tersimpan di tabel `guru_checklog`, status menjadi `keluar`, saat kembali dan konfirmasi tiba status berubah menjadi `selesai`. | Tercatat di rekap checklog harian, status berangkat `keluar` dan tiba kembali `selesai`. | **PASS** |
| **UAT-08** | Siswa membatalkan izin disetujui mandiri sebelum scan di gerbang | ID Pengajuan: `#DSP-004`, Tindakan: Klik "Batalkan Izin Keluar", Alasan: "Kegiatan lomba diundur" | Status permohonan bertransisi dari `disetujui` menjadi `dibatalkan`, file QR dibersihkan, siswa bebas mengajukan izin baru. | Status berubah menjadi `dibatalkan`, audit log tercatat, pembatalan sukses. | **PASS** |
| **UAT-09** | Otomasi penutupan izin KBM menggantung via scheduled task console command | Command: `php artisan dispensasi:auto-complete` saat jam KBM berakhir | Izin status `menunggu` berubah `kadaluarsa`; izin status `disetujui` tapi tak keluar berubah `dibatalkan`; izin status `keluar` tapi tak kembali berubah `selesai` (Tidak Kembali). | Seluruh berkas menggantung ditutup otomatis sesuai logika aturan bisnis sekolah. | **PASS** |
| **UAT-10** | Single Sign-On (SSO) masuk dari portal SiPintu Gateway | Klik tautan aplikasi DIDISPEN pada portal SiPintu (`http://localhost:8000`) | Browser terlempar ke `/oauth/callback`, token tervalidasi, otomatis login tanpa form input, mendarat di dashboard siswa/guru. | Berhasil auto-login ke DIDISPEN sesuai peran akun di SiPintu, nama pengguna cocok. | **PASS** |
| **UAT-11** | Fallback login mandiri dengan password yang baru diubah di SiPintu | Input NIS dan Password baru di form login lokal `/login` (belum sync webhook) | DIDISPEN mendeteksi hash lokal tidak cocok $\rightarrow$ query fallback ke endpoint SiPintu $\rightarrow$ verifikasi sukses $\rightarrow$ hash lokal diperbarui $\rightarrow$ login berhasil. | Login berhasil mendarat di dashboard, hash DB lokal otomatis ter-update tanpa double hash. | **PASS** |
| **UAT-12** | Admin mengekspor laporan rekapitulasi dispensasi bulanan | Filter: Tanggal `01-08-2026` s/d `31-08-2026`, Format: PDF & Excel | Sistem mengunduh file `laporan-dispensasi.pdf` ber-kop dinas rapi dan file `laporan-dispensasi.xlsx` berisi data terstruktur. | File PDF dan Excel berhasil diunduh, seluruh data perizinan dan durasi terhitung akurat. | **PASS** |

---

## A.8 Rubrik Penilaian Proyek & Bukti Pemenuhan

| Aspek | Bobot | Kriteria Baik | Pemenuhan pada Proyek DIDISPEN |
| :--- | :---: | :--- | :--- |
| **Product Vision dan Scope** | **15%** | Tujuan, pengguna, manfaat, dan batasan terdefinisi secara jelas dan terukur. | **Sangat Baik**: Visi mendigitalkan 100% perizinan dispensasi siswa SMKN 1 Bangsri terjabarkan di Product Vision Board (A.1). 4 persona pengguna (Siswa, Guru Piket, Satpam, Admin) memiliki batasan wewenang dan alur proses bisnis yang tegas. |
| **Product Backlog** | **20%** | User story terstruktur rapi, pembobotan prioritas tepat, acceptance criteria dapat diuji (*testable*). | **Sangat Baik**: Terdiri dari 8 Product Backlog Item (PB-01 s/d PB-08) dengan format standar (*As a... I want to... So that...*), estimasi Story Points (metode Fibonacci), prioritas MoSCoW, serta Acceptance Criteria berbasis skenario *Given-When-Then*. |
| **Pelaksanaan Scrum** | **20%** | Setiap event Scrum berjalan efektif dan menghasilkan keputusan serta adaptasi nyata. | **Sangat Baik**: Rangkaian Sprint Planning terbagi dalam 2 Sprint yang terukur (A.3), Daily Scrum mencatat hambatan teknis nyata beserta solusinya (A.4), Sprint Review membedah masukan stakeholder (A.5), dan Retrospective menghasilkan *action items* konkret (A.6). |
| **Kualitas Increment** | **25%** | Fitur berfungsi penuh, responsif, teruji bebas bug fatal, dan memenuhi Definition of Done (DoD). | **Sangat Baik**: Seluruh fitur inti (pengajuan dispensasi mandiri, verifikasi wajah selfie, persetujuan guru piket, cetak struk thermal 58mm, scanner QR pos satpam, integrasi SSO Hybrid SiPintu, checklog guru, dan ekspor laporan) bekerja solid, responsif di mobile & desktop, serta lolos verifikasi sintaks PHP. |
| **Dokumentasi dan Presentasi** | **10%** | Artefak lengkap, rapi, mudah dipahami, serta alur demonstrasi sistem terstruktur logis. | **Sangat Baik**: Dokumentasi memuat lembar kerja A.1 hingga A.8 secara terpadu, skenario UAT transparan, diagram alur status dispensasi jelas, dan siap dipresentasikan di hadapan tim penilai. |
| **Kolaborasi dan Refleksi** | **10%** | Pembagian kerja adaptif, kerja sama tim solid, dan retrospective membuahkan aksi nyata. | **Sangat Baik**: Tim membagi tanggung jawab backend, frontend UI/UX, dan security integrasi secara sinergis, dengan aksi nyata mitigasi risiko jaringan sekolah dan pencegahan isu *double-hashing* password. |
