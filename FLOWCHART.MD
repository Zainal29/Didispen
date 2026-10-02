# SYSTEM FLOWCHARTS & PROCESS WORKFLOWS
## SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)
**SMK NEGERI 1 BANGSRI**

Dokumentasi diagram alir (*flowchart*) operasional sistem **DIDISPEN** yang memetakan seluruh interaksi pengguna (Siswa, Guru Piket, Satpam, Admin) dan integrasi eksternal (SiPintu Gateway, WhatsApp Bot, Scanner Gerbang, & Thermal Printer).

---

## DAFTAR ISI DIAGRAM ALIR
1. [Flowchart 1: Autentikasi Multi-Role & Model Hybrid SiPintu SSO](#flowchart-1-autentikasi-multi-role--model-hybrid-sipintu-sso)
2. [Flowchart 2: Pengajuan Dispensasi Online oleh Siswa](#flowchart-2-pengajuan-dispensasi-online-oleh-siswa)
3. [Flowchart 3: Verifikasi, Persetujuan, & Cetak Struk Guru Piket](#flowchart-3-verifikasi-persetujuan--cetak-struk-guru-piket)
4. [Flowchart 4: Pemindaian QR Code Pos Satpam & Deteksi Overdue](#flowchart-4-pemindaian-qr-code-pos-satpam--deteksi-overdue)
5. [Flowchart 5: Pencatatan Keberadaan Guru (Guru Checklog)](#flowchart-5-pencatatan-keberadaan-guru-guru-checklog)
6. [Flowchart 6: Manajemen & Pertukaran Jadwal Guru Piket](#flowchart-6-manajemen--pertukaran-jadwal-guru-piket)
7. [Flowchart 7: State Machine Siklus Status Dispensasi](#flowchart-7-state-machine-siklus-status-dispensasi)

---

## Flowchart 1: Autentikasi Multi-Role & Model Hybrid SiPintu SSO

Diagram ini memetakan 2 jalur masuk aplikasi DIDISPEN: **Jalur 1 (SSO Terpusat via SiPintu)** dan **Jalur 2 (Form Login Mandiri dengan Fallback Real-Time)**.

```mermaid
flowchart TD
    Start([Mulai Akses DIDISPEN]) --> Choice{Pilih Jalur Masuk}

    %% JALUR 1: SSO SIPINTU
    Choice -- "Jalur 1: SSO SiPintu" --> ClickPortal[Pengguna Klik Ikon DIDISPEN di Portal SiPintu]
    ClickPortal --> RedirectCallback[Redirect ke DIDISPEN /oauth/callback dengan Auth Code]
    RedirectCallback --> ExchangeToken[Tukar Auth Code dengan Access Token Server-to-Server]
    ExchangeToken --> FetchProfile[Ambil Profil Pengguna dari GET /api/v1/user]
    FetchProfile --> MatchLocal{Apakah Akun Ada di DB Lokal?}
    
    MatchLocal -- Tidak --> AutoProvision[Auto-Provision Akun User & Profil Siswa/Guru]
    MatchLocal -- Ya --> UpdateSync[Update Timestamp sipintu_last_synced_at]
    
    AutoProvision --> ValidateRoleSSO{Cek Role SSO}
    UpdateSync --> ValidateRoleSSO
    ValidateRoleSSO -- "Admin" --> RejectSSO[Tolak SSO: Admin Wajib Login Manual]
    RejectSSO --> ShowLogin
    ValidateRoleSSO -- "Siswa/Guru/Satpam" --> AuthLoginSSO[Auth::login & Regenerate Session]

    %% JALUR 2: LOGIN MANUAL LOKAL
    Choice -- "Jalur 2: Login Manual" --> ShowLogin[Buka Halaman /login]
    ShowLogin --> InputForm[Input NIS/NIP/Email, Password, & Pilih Role]
    InputForm --> CheckThrottle{Gagal >= 5 Kali?}
    
    CheckThrottle -- Ya --> AccountLocked[Akun Terkunci 15 Menit / Catat Audit Log]
    AccountLocked --> EndFail([Selesai: Akses Ditolak])
    
    CheckThrottle -- Tidak --> QueryUser{Cari User di DB Lokal Sesuai Role}
    QueryUser -- Ditemukan --> VerifyLocalHash{Hash::check Password Cocok?}
    
    VerifyLocalHash -- Ya --> ResetThrottle[Reset Gagal Login ke 0]
    ResetThrottle --> AuthLoginManual[Auth::login Sesi Lokal]

    %% FALLBACK KE SIPINTU GATEWAY
    QueryUser -- "Tidak Ditemukan & Bukan Admin" --> CallFallback[Kirim Kredensial ke POST /api/v1/auth/verify-credentials]
    VerifyLocalHash -- "Password Salah & Bukan Admin" --> CallFallback
    
    CallFallback --> FallbackResult{Respon SiPintu Valid?}
    FallbackResult -- Ya --> UpdateLocalHash[Update Password Hash Baru di DB via DB::table]
    UpdateLocalHash --> EnsureProfile[Pastikan Relasi Siswa/Guru Tersedia]
    EnsureProfile --> ResetThrottle
    
    FallbackResult -- Tidak --> IncThrottle[Increment failed_login_attempts]
    IncThrottle --> LoginError[Tampilkan Pesan: NIS/Email atau Password Salah]
    LoginError --> ShowLogin

    %% REDIRECT SESUAI ROLE
    AuthLoginSSO --> RouteDashboard{Redirect Dashboard Sesuai Role}
    AuthLoginManual --> RouteDashboard

    RouteDashboard -- "Role: Siswa" --> DashSiswa([Masuk Dashboard Siswa])
    RouteDashboard -- "Role: Guru" --> DashGuru([Masuk Dashboard Guru])
    RouteDashboard -- "Role: Satpam" --> DashSatpam([Masuk Dashboard Satpam])
    RouteDashboard -- "Role: Admin" --> DashAdmin([Masuk Dashboard Admin])
```

---

## Flowchart 2: Pengajuan Dispensasi Online oleh Siswa

Alur pengajuan izin dispensasi oleh siswa secara mandiri ataupun rombongan kegiatan.

```mermaid
flowchart TD
    StartSiswa([Siswa Berada di Dashboard]) --> ClickAjukan[Pilih Menu 'Ajukan Dispensasi']
    ClickAjukan --> FormDispen[Tampilkan Form Pengajuan Dispensasi]
    
    FormDispen --> InputData[Pilih Kategori: Sakit / Izin / Dinas / Lomba<br>Isi Alasan, Tujuan, Lokasi, Rentang Jam Keluar & Kembali]
    InputData --> UploadBukti[Unggah Foto Bukti Surat / Dokumen Kegiatan]
    
    UploadBukti --> CheckRombongan{Apakah Pengajuan Rombongan?}
    CheckRombongan -- Ya --> SearchAnggota[Cari Siswa Berdasarkan NIS / Nama]
    SearchAnggota --> AddAnggota[Tambahkan Siswa ke Daftar Rombongan]
    AddAnggota --> SubmitForm[Klik Tombol 'Kirim Pengajuan']
    CheckRombongan -- Tidak --> SubmitForm

    SubmitForm --> ValidateInput{Validasi Kelengkapan & Jam Sekolah}
    ValidateInput -- Tidak Valid --> ShowFormError[Tampilkan Pesan Peringatan Input]
    ShowFormError --> FormDispen

    ValidateInput -- Valid --> GenerateNoSurat[Generate Nomor Surat: DISP/YYYYMMDD/XXXXXX]
    GenerateNoSurat --> GenerateQRToken[Generate 64-char QR Token Unik]
    GenerateQRToken --> SaveDB[(Simpan ke Tabel dispensasi status: 'menunggu')]
    
    SaveDB --> TriggerWA[Cari Guru Piket yang Bertugas Hari Ini]
    TriggerWA --> SendWANotif[Kirim Pesan WhatsApp Otomatis ke Guru Piket]
    SendWANotif --> StudentSuccess[Tampilkan Konfirmasi: Pengajuan Berhasil Terkirim]
    StudentSuccess --> EndSiswa([Siswa Menunggu Verifikasi Guru Piket])
```

---

## Flowchart 3: Verifikasi, Persetujuan, & Cetak Struk Guru Piket

Alur kerja Guru Piket dalam memproses antrean pengajuan dispensasi siswa dan pencetakan bukti fisik perizinan.

```mermaid
flowchart TD
    StartPiket([Guru Piket Buka Menu 'Daftar Pengajuan']) --> ViewQueue[Tampilkan Antrean Status: 'menunggu']
    ViewQueue --> SelectItem[Pilih Salah Satu Pengajuan Siswa]
    SelectItem --> ReviewDetail[Review Alasan, Waktu Izin, & Periksa Foto Bukti Surat]
    
    ReviewDetail --> Decision{Keputusan Guru Piket}
    
    %% SKENARIO DITOLAK
    Decision -- "Tolak" --> InputReason[Wajib Ketik Alasan Penolakan pada Catatan Admin]
    InputReason --> SaveReject[(Update Status: 'ditolak' & rejected_at: now)]
    SaveReject --> SendWAReject[Kirim Pesan WhatsApp Info Penolakan ke Siswa]
    SendWAReject --> EndReject([Selesai: Dispensasi Ditolak])

    %% SKENARIO DISETUJUI
    Decision -- "Setujui" --> CalcDeadline[Hitung batas_waktu_kembali dari jam_kembali]
    CalcDeadline --> GenerateQRImage[Render Gambar QR Code Berisi Token]
    GenerateQRImage --> SaveApprove[(Update Status: 'disetujui' & approved_at: now)]
    SaveApprove --> SendWAApprove[Kirim Pesan WhatsApp ke Siswa Beserta Link QR]
    
    SendWAApprove --> PrintOption{Pilihan Cetak Struk}
    PrintOption -- "Printer Thermal 58mm" --> PrintESC[Kirim Raw Data ESC/POS ke Printer Thermal Bluetooth/USB]
    PrintOption -- "Unduh File PDF" --> RenderPDF[Render Template Blade Struk 58mm via Dompdf]
    
    PrintESC --> IncCounter[Increment print_count & teacher_print_count]
    RenderPDF --> IncCounter
    IncCounter --> StrukFisik[Struk Fisik Ber-QR Code Diserahkan ke Siswa]
    StrukFisik --> EndApprove([Siswa Siap Menuju Pos Gerbang Satpam])
```

---

## Flowchart 4: Pemindaian QR Code Pos Satpam & Deteksi Overdue

Alur pemeriksaan perizinan 2 arah di gerbang sekolah oleh petugas Satpam (saat keluar dan kembali).

```mermaid
flowchart TD
    StartSatpam([Satpam Buka Portal Scanner Kamera Pos Gerbang]) --> SiswaArrive[Siswa Tiba di Pos Memperlihatkan QR Code]
    SiswaArrive --> ScanQR[Kamera Satpam Memindai QR Code]
    ScanQR --> QueryToken{Cari Dispensasi Berdasarkan qr_token}
    
    QueryToken -- "Token Tidak Ditemukan" --> InvalidQR[Tampilkan Layar Merah: QR Code Tidak Dikenal]
    InvalidQR --> EndGateFail([Akses Gerbang Ditolak])

    QueryToken -- "Token Ditemukan" --> CheckStatusCurrent{Periksa Status Saat Ini}

    %% KONDISI 1: SISWA HENDAK KELUAR
    CheckStatusCurrent -- "Status: 'disetujui'" --> ShowValidOut[Tampilkan Identitas, Foto, & Batas Jam Kembali]
    ShowValidOut --> SnapPhoto[Satpam Ambil Foto Fisik Siswa di Pos Gerbang]
    SnapPhoto --> ClickConfirmOut[Klik Tombol 'Konfirmasi Keluar']
    ClickConfirmOut --> UpdateKeluar[(Update status: 'keluar'<br>waktu_keluar_aktual: now<br>satpam_keluar_id: Auth::id)]
    UpdateKeluar --> PlayBeepOut[Bunyikan Audio Beep Sukses]
    PlayBeepOut --> AllowExit([Satpam Membuka Gerbang: Siswa Diizinkan Keluar])

    %% KONDISI 2: SISWA KEMBALI KE SEKOLAH
    CheckStatusCurrent -- "Status: 'keluar'" --> EvaluateTime{Cek Waktu Aktual vs batas_waktu_kembali}
    
    EvaluateTime -- "Kembali Tepat Waktu" --> ShowOnTime[Tampilkan Status: Kembali Tepat Waktu (Hijau)]
    ShowOnTime --> ClickConfirmIn[Klik Tombol 'Konfirmasi Masuk / Selesai']
    ClickConfirmIn --> UpdateSelesai[(Update status: 'selesai'<br>waktu_kembali_aktual: now<br>satpam_kembali_id: Auth::id)]
    UpdateSelesai --> SendWAComplete[Kirim Pesan WA: Siswa Telah Tiba di Sekolah]
    SendWAComplete --> AllowEnter([Siswa Diizinkan Masuk Kembali ke Kelas])

    EvaluateTime -- "Melewati Batas Waktu" --> ShowOverdue[Tampilkan Peringatan: SISWA TERLAMBAT / OVERDUE (Merah)]
    ShowOverdue --> NoteOverdue[Sistem Menghitung Menit Keterlambatan]
    NoteOverdue --> ClickConfirmInOverdue[Klik Konfirmasi dengan Tanda Overdue]
    ClickConfirmInOverdue --> UpdateOverdue[(Update status: 'selesai'<br>is_warned: true<br>warned_at: now)]
    UpdateOverdue --> SendWAOverdue[Kirim Alert WhatsApp ke Guru Piket: Siswa Terlambat Kembali]
    SendWAOverdue --> AllowEnter
```

---

## Flowchart 5: Pencatatan Keberadaan Guru (Guru Checklog)

Pencatatan mobilitas guru yang sedang bertugas keluar/dinas luar lingkungan sekolah.

```mermaid
flowchart TD
    StartGuru([Guru Buka Menu 'Checklog Keberadaan']) --> ActionChoice{Pilih Aksi}
    
    ActionChoice -- "Guru Hendak Keluar Dinas" --> FillChecklog[Isi Form: Keperluan Dinas/Pribadi, Tujuan, Estimasi Jam Kembali]
    FillChecklog --> SaveOut[(Simpan ke tabel guru_checklog<br>jam_keluar: now, status: 'keluar')]
    SaveOut --> UpdateStatusGuru[Status Guru di Dashboard Berubah: 'Sedang Keluar Dinas']
    UpdateStatusGuru --> EndCheckOut([Guru Berangkat Tugas Luar])

    ActionChoice -- "Guru Tiba Kembali" --> OpenHistory[Buka Riwayat Checklog Hari Ini]
    OpenHistory --> ClickKembali[Klik Tombol 'Konfirmasi Tiba di Sekolah']
    ClickKembali --> SaveIn[(Update guru_checklog<br>jam_kembali: now, status: 'kembali')]
    SaveIn --> ResetStatusGuru[Status Guru di Dashboard Berubah: 'Tersedia / Standby']
    ResetStatusGuru --> EndCheckIn([Guru Siap Mengajar Kembali])
```

---

## Flowchart 6: Manajemen & Pertukaran Jadwal Guru Piket

Alur pengajuan dan persetujuan pergantian jadwal jaga piket antar-guru.

```mermaid
flowchart TD
    StartPiketSchedule([Guru Berhalangan Piket]) --> OpenPiketMenu[Buka Menu 'Jadwal & Tukar Piket']
    OpenPiketMenu --> SelectDate[Pilih Hari Tugas yang Ingin Digantikan]
    SelectDate --> PickSubstitute[Pilih Guru Pengganti & Tuliskan Alasan]
    PickSubstitute --> SendSwapReq[(Simpan ke tabel pertukaran_jadwal_piket status: 'menunggu')]
    
    SendSwapReq --> NotifySubTeacher[Kirim Notifikasi ke Guru Pengganti]
    NotifySubTeacher --> SubDecision{Keputusan Guru Pengganti}
    
    SubDecision -- "Tolak" --> UpdateSwapReject[(Update status: 'ditolak')]
    UpdateSwapReject --> NotifyAsalReject[Kirim Pesan: Permintaan Tukar Ditolak]
    NotifyAsalReject --> EndSwapFail([Jadwal Tetap Seperti Semula])

    SubDecision -- "Setujui" --> UpdateSwapApprove[(Update status: 'disetujui'<br>diproses_at: now)]
    UpdateSwapApprove --> SwapActiveSchedule[Jadwal Piket Hari Terkait Diperbarui dengan Guru Pengganti]
    SwapActiveSchedule --> NotifyAdminPiket[Notifikasi Konfirmasi Terkirim ke Koordinator Piket]
    NotifyAdminPiket --> EndSwapSuccess([Pertukaran Jadwal Resmi Aktif])
```

---

## Flowchart 7: State Machine Siklus Status Dispensasi

Diagram transisi status (*Lifecycle State Machine*) satu berkas dispensasi siswa dari awal hingga selesai.

```mermaid
stateDiagram-v2
    [*] --> menunggu : Siswa Mengirimkan Pengajuan (PB-01)
    
    menunggu --> ditolak : Ditolak Guru Piket (disertai alasan penolakan)
    menunggu --> disetujui : Disetujui Guru Piket (QR Token terbit)
    
    disetujui --> keluar : Satpam Scan QR di Gerbang (waktu keluar tercatat)
    
    keluar --> selesai : Satpam Scan QR Kembali (Tepat Waktu)
    keluar --> selesai : Satpam Scan QR Kembali (Overdue / Terlambat)
    
    keluar --> selesai : Auto-Complete Otomatis (Cron Schedule pkl 16:00 WIB)
    
    ditolak --> [*] : Izin Dibatalkan
    selesai --> [*] : Berkas Masuk Rekapitulasi & Arsip Laporan
```