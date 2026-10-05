# SYSTEM FLOWCHARTS & PROCESS WORKFLOWS
## SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)
**SMK NEGERI 1 BANGSRI**

Dokumentasi diagram alir (*flowchart*) operasional sistem **DIDISPEN** yang memetakan seluruh interaksi pengguna (Siswa, Guru Piket, Satpam, Admin) dan integrasi eksternal (SiPintu Identity Gateway, Dynamic WhatsApp Engine, Scanner Gerbang, & Thermal Printer Bluetooth ESC/POS). Disinkronkan 100% dengan alur controller dan *state machine* aplikasi aktual.

---

## DAFTAR ISI DIAGRAM ALIR
1. [Flowchart 1: Autentikasi Multi-Role & Model Hybrid SiPintu SSO](#flowchart-1-autentikasi-multi-role--model-hybrid-sipintu-sso)
2. [Flowchart 2: Pengajuan Dispensasi Mandiri oleh Siswa & Selfie Verifikasi](#flowchart-2-pengajuan-dispensasi-mandiri-oleh-siswa--selfie-verifikasi)
3. [Flowchart 3: Verifikasi, Persetujuan, & Cetak Struk Guru Piket](#flowchart-3-verifikasi-persetujuan--cetak-struk-guru-piket)
4. [Flowchart 4: Pemindaian QR Code Pos Satpam & Deteksi Overdue](#flowchart-4-pemindaian-qr-code-pos-satpam--deteksi-overdue)
5. [Flowchart 5: Pencatatan Keberadaan Guru (Guru Checklog Dinas Luar)](#flowchart-5-pencatatan-keberadaan-guru-guru-checklog-dinas-luar)
6. [Flowchart 6: Manajemen & Pertukaran Jadwal Guru Piket](#flowchart-6-manajemen--pertukaran-jadwal-guru-piket)
7. [Flowchart 7: State Machine Siklus 7 Status Dispensasi & Auto-Complete KBM](#flowchart-7-state-machine-siklus-7-status-dispensasi--auto-complete-kbm)

---

## Flowchart 1: Autentikasi Multi-Role & Model Hybrid SiPintu SSO

Diagram ini memetakan 2 jalur masuk aplikasi DIDISPEN: **Jalur 1 (SSO Terpusat via SiPintu)** dan **Jalur 2 (Form Login Mandiri dengan Fallback Real-Time)** dilengkapi proteksi anti-brute force (*account locking*).

```mermaid
flowchart TD
    Start(["Mulai Akses DIDISPEN"]) --> Choice{"Pilih Jalur Masuk"}

    %% JALUR 1: SSO SIPINTU
    Choice -- "Jalur 1: SSO SiPintu" --> ClickPortal["Pengguna Klik Ikon DIDISPEN di Portal SiPintu"]
    ClickPortal --> RedirectCallback["Redirect ke /oauth/callback dengan Auth Code"]
    RedirectCallback --> ExchangeToken["Tukar Auth Code dengan Access Token Server-to-Server"]
    ExchangeToken --> FetchProfile["Ambil Profil Pengguna dari GET /api/v1/user"]
    FetchProfile --> MatchLocal{"Apakah Akun Ada di DB Lokal?"}
    
    MatchLocal -- "Tidak" --> AutoProvision["Auto-Provision Akun User dan Profil Siswa atau Guru"]
    MatchLocal -- "Ya" --> UpdateSync["Update Timestamp sipintu_last_synced_at"]
    
    AutoProvision --> ValidateRoleSSO{"Cek Role SSO"}
    UpdateSync --> ValidateRoleSSO
    ValidateRoleSSO -- "Admin" --> RejectSSO["Tolak SSO: Admin Wajib Login Manual"]
    RejectSSO --> ShowLogin
    ValidateRoleSSO -- "Siswa/Guru/Satpam" --> AuthLoginSSO["Autentikasi Login dan Regenerate Session"]

    %% JALUR 2: LOGIN MANUAL LOKAL
    Choice -- "Jalur 2: Login Manual" --> ShowLogin["Buka Halaman Login (/login)"]
    ShowLogin --> InputForm["Input NIS/NIP/Email, Password, dan Pilih Role"]
    InputForm --> CheckThrottle{"Gagal 5 Kali atau Lebih?"}
    
    CheckThrottle -- "Ya" --> AccountLocked["Akun Terkunci 15 Menit dan Catat Audit Log"]
    AccountLocked --> EndFail(["Selesai: Akses Ditolak"])
    
    CheckThrottle -- "Tidak" --> QueryUser{"Cari User di DB Lokal Sesuai Role"}
    QueryUser -- "Ditemukan" --> VerifyLocalHash{"Password Lokal Cocok?"}
    
    VerifyLocalHash -- "Ya" --> ResetThrottle["Reset Gagal Login ke 0"]
    ResetThrottle --> AuthLoginManual["Autentikasi Login Sesi Lokal"]

    %% FALLBACK KE SIPINTU GATEWAY
    QueryUser -- "Tidak Ditemukan dan Bukan Admin" --> CallFallback["Kirim Kredensial ke POST /api/v1/auth/verify-credentials"]
    VerifyLocalHash -- "Password Salah dan Bukan Admin" --> CallFallback
    
    CallFallback --> FallbackResult{"Respon SiPintu Valid?"}
    FallbackResult -- "Ya" --> UpdateLocalHash["Update Password Hash Baru di DB via Query Builder"]
    UpdateLocalHash --> EnsureProfile["Pastikan Relasi Profil Siswa atau Guru Tersedia"]
    EnsureProfile --> ResetThrottle
    
    FallbackResult -- "Tidak" --> IncThrottle["Increment failed_login_attempts"]
    IncThrottle --> LoginError["Tampilkan Pesan: NIS/Email atau Password Salah"]
    LoginError --> ShowLogin

    %% REDIRECT SESUAI ROLE
    AuthLoginSSO --> RouteDashboard{"Redirect Dashboard Sesuai Role"}
    AuthLoginManual --> RouteDashboard

    RouteDashboard -- "Role: Siswa" --> DashSiswa(["Masuk Dashboard Siswa"])
    RouteDashboard -- "Role: Guru" --> DashGuru(["Masuk Dashboard Guru"])
    RouteDashboard -- "Role: Satpam" --> DashSatpam(["Masuk Dashboard Satpam"])
    RouteDashboard -- "Role: Admin" --> DashAdmin(["Masuk Dashboard Admin"])
```

---

## Flowchart 2: Pengajuan Dispensasi Mandiri oleh Siswa & Selfie Verifikasi

Alur pengajuan izin dispensasi siswa secara mandiri berbasis jadwal KBM aktif sekolah, pencegahan pengajuan ganda (*concurrency lock*), dan pengambilan foto selfie wajah sebagai verifikasi identitas fisik di pos gerbang.

```mermaid
flowchart TD
    StartSiswa(["Siswa Berada di Dashboard"]) --> ClickAjukan["Pilih Menu Buat Pengajuan Dispensasi"]
    ClickAjukan --> FormDispen["Tampilkan Form Pengajuan Berbasis Jadwal KBM Hari Ini"]
    
    FormDispen --> CheckTimeOp{"Apakah Masih dalam Jam Operasional Sekolah?"}
    CheckTimeOp -- "Tidak (Di Luar Jam Operasional)" --> AlertTime["Tampilkan Peringatan: Pengajuan Hanya Dapat Dibuat Saat Jam Sekolah"]
    AlertTime --> DashSiswa(["Kembali ke Dashboard"])
    
    CheckTimeOp -- "Ya (Jam Sekolah Aktif)" --> InputData["Pilih Kategori: Sakit, Izin, Keperluan Sekolah, Lainnya<br/>Isi Alasan min 10 Karakter, Tujuan, Lokasi, dan Nomor WhatsApp"]
    InputData --> SelectJam["Pilih Jam Keluar & Jam Kembali:<br/>Format Jam Pelajaran ke-X ATAU Jam Menit HH:MM WIB"]
    SelectJam --> SnapSelfie["Ambil Foto Selfie Verifikasi Wajah via Kamera Perangkat (Wajib)"]
    
    SnapSelfie --> SubmitForm["Klik Tombol Kirim Pengajuan"]

    SubmitForm --> ConcurrencyCheck{"Apakah Siswa Masih Punya Izin Aktif?<br/>(status: menunggu, disetujui, keluar)"}
    ConcurrencyCheck -- "Ya (Ada Izin Aktif)" --> RejectDuplicate["Tolak: Selesaikan / Batalkan Izin Sebelumnya Terlebih Dahulu"]
    RejectDuplicate --> DashSiswa

    ConcurrencyCheck -- "Tidak Ada Izin Aktif" --> ValidateSchedule{"Validasi Jam:<br/>Jam Kembali > Jam Keluar & Sesuai Slot KBM Hari Ini?"}
    ValidateSchedule -- "Tidak Valid" --> ShowError["Tampilkan Pesan Validasi Jam"]
    ShowError --> FormDispen

    ValidateSchedule -- "Valid" --> CalcDeadline["Hitung batas_waktu_kembali Otomatis Berdasarkan Jam Pelajaran"]
    CalcDeadline --> DBTrans["DB Transaction: Simpan Pengajuan<br/>status: menunggu, nomor_surat unik, token QR acak 64 char"]
    
    DBTrans --> ShowSuccess["Pengajuan Berhasil Disimpan"]
    ShowSuccess --> PopupHubungi["Buka Halaman Detail Dispensasi<br/>(Tersedia Tombol: Hubungi Guru Piket via WhatsApp selama 3 Menit Pertama)"]
    PopupHubungi --> EndSiswa(["Siswa Menemui Guru Piket di Ruang Piket"])
```

---

## Flowchart 3: Verifikasi, Persetujuan, & Cetak Struk Guru Piket

Alur kerja Guru Piket dalam memproses antrean pengajuan dispensasi siswa, persetujuan/penolakan, pembuatan dispensasi manual di ruang piket, serta pencetakan bukti fisik struk 58mm.

```mermaid
flowchart TD
    StartPiket(["Guru Piket Membuka Menu Daftar Pengajuan"]) --> ChoicePiket{"Pilih Alur"}

    %% ALUR MANUAL DI RUANG PIKET
    ChoicePiket -- "Siswa Datang Langsung (Darurat)" --> FormManual["Menu: Buat Pengajuan Manual Guru"]
    FormManual --> SearchSiswa["Cari Siswa via AJAX Select2 (NIS / Nama)"]
    SearchSiswa --> InputManualData["Isi Alasan, Tujuan, Kategori, Jam Keluar & Kembali"]
    InputManualData --> SaveManual["Simpan Pengajuan Langsung: status disetujui<br/>dibuat_manual_oleh_guru: true"]
    SaveManual --> PrintDirect["Langsung Siap Cetak Struk"]

    %% ALUR VERIFIKASI ONLINE
    ChoicePiket -- "Verifikasi Antrean Online" --> ViewQueue["Tampilkan Antrean Status: menunggu"]
    ViewQueue --> SelectItem["Pilih Permohonan Siswa"]
    SelectItem --> ReviewDetail["Review Alasan, Foto Selfie Siswa, Jam Keluar, & Batas Jam Kembali"]
    
    ReviewDetail --> Decision{"Keputusan Guru Piket"}
    
    %% SKENARIO DITOLAK
    Decision -- "Tolak" --> InputReason["Wajib Isi Alasan Penolakan pada Catatan Admin"]
    InputReason --> SaveReject["Update status: ditolak dan catat waktu rejected_at"]
    SaveReject --> LinkWAReject["Sistem Generate Template WhatsApp Penolakan"]
    LinkWAReject --> EndReject(["Selesai: Dispensasi Ditolak"])

    %% SKENARIO DISETUJUI
    Decision -- "Setujui" --> CalcApprove["Hitung Ulang batas_waktu_kembali Presisi"]
    CalcApprove --> GenerateQRImage["Render Gambar QR Code Berisi Token JSON"]
    GenerateQRImage --> SaveApprove["Update status: disetujui dan catat waktu approved_at"]
    SaveApprove --> PrintDirect

    %% CETAK STRUK
    PrintDirect --> PrintOption{"Pilihan Cetak Struk Perizinan"}
    PrintOption -- "Printer Thermal Bluetooth 58mm" --> PrintESC["Kirim Raw Data ESC/POS via Web Bluetooth API"]
    PrintOption -- "Cetak / Unduh PDF" --> RenderPDF["Render Template Blade Struk Format 58mm via Dompdf"]
    
    PrintESC --> IncCounter["Increment print_count & teacher_print_count (Maks 3 Kali)"]
    RenderPDF --> IncCounter
    IncCounter --> StrukFisik["Struk Fisik Ber-QR Code Diserahkan ke Siswa"]
    StrukFisik --> EndApprove(["Siswa Siap Menuju Pos Gerbang Satpam"])
```

---

## Flowchart 4: Pemindaian QR Code Pos Satpam & Deteksi Overdue

Alur pemeriksaan perizinan 2 arah di gerbang sekolah oleh petugas Satpam (saat keluar dan kembali) dengan pencocokan identitas foto selfie verifikasi siswa di layar scanner.

```mermaid
flowchart TD
    StartSatpam(["Satpam Membuka Kamera Scanner di Gerbang"]) --> SiswaArrive["Siswa Menunjukkan QR Code (Struk Thermal / Layar HP)"]
    SiswaArrive --> ScanQR["Kamera Satpam Memindai QR Code"]
    ScanQR --> QueryToken{"Cari Data Dispensasi Berdasarkan qr_token"}
    
    QueryToken -- "Token Tidak Ditemukan" --> InvalidQR["Tampilkan Layar Peringatan: QR Code Tidak Terdaftar"]
    InvalidQR --> EndGateFail(["Akses Gerbang Ditolak"])

    QueryToken -- "Token Ditemukan" --> CheckStatusCurrent{"Periksa Status Saat Ini"}

    %% KONDISI STATUS TIDAK DAPAT DIPROSES
    CheckStatusCurrent -- "Status: menunggu / ditolak / selesai / dibatalkan / kadaluarsa" --> BlockAction["Tampilkan Status Izin Tidak Aktif dan Blokir Gerbang"]
    BlockAction --> EndGateFail

    %% KONDISI 1: SISWA KELUAR
    CheckStatusCurrent -- "Status: disetujui" --> PreviewKeluar["Modal Preview: Tampilkan Foto Selfie Verifikasi, Nama, NIS, Kelas, & Batas Jam Kembali"]
    PreviewKeluar --> VerifyFace{"Satpam Mencocokkan Wajah Siswa Asli dengan Foto Selfie di Layar"}
    
    VerifyFace -- "Wajah Tidak Cocok (Joki Izin)" --> RejectFace["Tahan Siswa dan Laporkan ke Guru Piket"]
    RejectFace --> EndGateFail

    VerifyFace -- "Wajah Cocok" --> ClickConfirmOut["Satpam Klik Tombol Konfirmasi Keluar"]
    ClickConfirmOut --> UpdateKeluar["Update DB: status: keluar<br/>Catat waktu_keluar_aktual & satpam_keluar_id"]
    UpdateKeluar --> PlayBeepOut["Bunyikan Audio Beep Sukses Keluar"]
    PlayBeepOut --> AllowExit(["Satpam Buka Gerbang: Siswa Diizinkan Keluar"])
    AllowExit --> SiswaUploadBukti["(Opsional: Siswa Dapat Unggah foto_bukti Kegiatan Saat di Luar)"]

    %% KONDISI 2: SISWA KEMBALI
    CheckStatusCurrent -- "Status: keluar" --> EvaluateTime{"Cek Waktu Aktual vs Batas Waktu Kembali"}
    
    EvaluateTime -- "Kembali Tepat Waktu" --> ShowOnTime["Layar Hijau: Tepat Waktu<br/>Satpam Klik Konfirmasi Kembali"]
    ShowOnTime --> UpdateSelesai["Update DB: status: selesai<br/>Catat waktu_kembali_aktual & satpam_kembali_id"]
    UpdateSelesai --> AllowEnter(["Siswa Diizinkan Masuk Kembali ke Kelas"])

    EvaluateTime -- "Melewati Batas Waktu (Overdue)" --> ShowOverdue["Layar Merah: SISWA TERLAMBAT / OVERDUE<br/>Tampilkan Jumlah Menit Keterlambatan"]
    ShowOverdue --> ConfirmOverdue["Satpam Klik Konfirmasi Kembali (Terlambat)"]
    ConfirmOverdue --> UpdateOverdue["Update DB: status: selesai, waktu_kembali_aktual<br/>Tandai is_warned: true, warned_at: now()"]
    UpdateOverdue --> LinkWAOverdue["Generate Tombol WhatsApp Peringatan Keterlambatan ke Siswa/Wali"]
    LinkWAOverdue --> AllowEnter
```

---

## Flowchart 5: Pencatatan Keberadaan Guru (Guru Checklog Dinas Luar)

Pencatatan mobilitas guru yang sedang bertugas keluar atau dinas luar lingkungan sekolah beserta waktu tiba kembali.

```mermaid
flowchart TD
    StartGuru(["Guru Buka Menu Checklog Keberadaan"]) --> ActionChoice{"Pilih Aksi"}
    
    ActionChoice -- "Guru Hendak Keluar Dinas" --> CheckActiveLog{"Apakah Masih Memiliki Log Keluar Aktif?"}
    CheckActiveLog -- "Ya" --> AlertFinish["Wajib Selesaikan Check-in Log Sebelumnya Terlebih Dahulu"]
    AlertFinish --> StartGuru

    CheckActiveLog -- "Tidak Ada" --> FillChecklog["Isi Form: Keperluan / Alasan Dinas, Tujuan, dan Lokasi"]
    FillChecklog --> SaveOut["Simpan ke tabel guru_checklog:<br/>jam_keluar: waktu otomatis tombol ditekan, status: keluar"]
    SaveOut --> AuditOut["Catat Jejak Audit: guru_check_out"]
    AuditOut --> UpdateStatusGuru["Status Guru di Dashboard: Sedang Keluar Dinas"]
    UpdateStatusGuru --> EndCheckOut(["Guru Berangkat Tugas Luar"])

    ActionChoice -- "Guru Tiba Kembali di Sekolah" --> OpenHistory["Buka Menu Checklog Keberadaan"]
    OpenHistory --> ClickKembali["Klik Tombol Konfirmasi Tiba di Sekolah"]
    ClickKembali --> SaveIn["Update guru_checklog:<br/>jam_kembali: now(), status: selesai"]
    SaveIn --> AuditIn["Catat Jejak Audit: guru_check_in"]
    AuditIn --> ResetStatusGuru["Status Guru di Dashboard: Tersedia / Standby di Sekolah"]
    ResetStatusGuru --> EndCheckIn(["Guru Siap Mengajar Kembali"])
```

---

## Flowchart 6: Manajemen & Pertukaran Jadwal Guru Piket

Alur pengajuan, pembatalan mandiri, dan persetujuan pergantian jadwal jaga piket antar-guru.

```mermaid
flowchart TD
    StartPiketSchedule(["Guru Berhalangan Piket"]) --> OpenPiketMenu["Buka Menu Jadwal & Tukar Piket"]
    OpenPiketMenu --> SelectDate["Pilih Hari Tugas Piket yang Ingin Digantikan"]
    SelectDate --> PickSubstitute["Pilih Guru Pengganti Aktif dan Masukkan Alasan"]
    PickSubstitute --> SendSwapReq["Simpan ke tabel pertukaran_jadwal_piket<br/>status: menunggu, is_active: false"]
    
    SendSwapReq --> GuruAsalOption{"Aksi Tambahan Guru Asal"}
    GuruAsalOption -- "Ingin Membatalkan Permohonan" --> CancelSwap["Guru Asal Klik Batalkan: status menjadi dibatalkan"]
    CancelSwap --> EndSwapCanceled(["Permohonan Dibatalkan"])

    GuruAsalOption -- "Menunggu Respon" --> NotifySubTeacher["Notifikasi / Link WhatsApp Terkirim ke Rekan Guru Pengganti"]
    NotifySubTeacher --> SubDecision{"Keputusan Rekan Guru Pengganti"}
    
    SubDecision -- "Tolak" --> UpdateSwapReject["Update status pertukaran: ditolak<br/>catat diproses_at & diproses_oleh"]
    UpdateSwapReject --> EndSwapFail(["Permohonan Ditolak, Jadwal Tetap"])

    SubDecision -- "Setujui" --> UpdateSwapApprove["Update status pertukaran: disetujui<br/>is_active: true (pada tanggal terkait)"]
    UpdateSwapApprove --> SwapActiveSchedule["Sistem Menetapkan Guru Pengganti Bertugas pada Tanggal Tersebut"]
    SwapActiveSchedule --> EndSwapSuccess(["Pertukaran Jadwal Resmi Aktif"])
```

---

## Flowchart 7: State Machine Siklus 7 Status Dispensasi & Auto-Complete KBM

Diagram transisi status (*Lifecycle State Machine*) lengkap 7 status sesuai implementasi database enum dan scheduled task console command `dispensasi:auto-complete`.

```mermaid
stateDiagram-v2
    [*] --> menunggu : Siswa Mengirimkan Pengajuan Mandiri (PB-01)
    [*] --> disetujui : Guru Piket Membuat Izin Manual di Ruang Piket (PB-02)
    
    menunggu --> ditolak : Ditolak Guru Piket (disertai alasan penolakan pada catatan_admin)
    menunggu --> disetujui : Disetujui Guru Piket (QR Token 64-char & Batas Waktu Terbit)
    menunggu --> kadaluarsa : Auto-Kadaluarsa via Cron saat Jam KBM Hari Itu Usai Tanpa Approval

    disetujui --> keluar : Satpam Scan QR di Gerbang Pos Satpam (waktu_keluar_aktual tercatat)
    disetujui --> dibatalkan : Siswa Membatalkan Mandiri (batalKeluar) Sebelum Scan Gerbang
    disetujui --> dibatalkan : Auto-Batal via Cron jika Jam KBM Usai Siswa Tidak Pernah Scan Keluar
    
    keluar --> selesai : Satpam Scan QR Kembali (Tepat Waktu)
    keluar --> selesai : Satpam Scan QR Kembali (Overdue / Terlambat, ditandai is_warned: true)
    keluar --> selesai : Auto-Closed via Cron saat KBM Berakhir Tanpa Scan Masuk (Status: Selesai Tidak Kembali)
    
    ditolak --> [*] : Berkas Izin Ditolak
    kadaluarsa --> [*] : Berkas Kadaluarsa
    dibatalkan --> [*] : Izin Batal Keluar
    selesai --> [*] : Berkas Masuk Rekapitulasi & Arsip Laporan (PDF/Excel)
```