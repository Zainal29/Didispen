# ENTITY RELATIONSHIP DIAGRAM (ERD) & DATA DICTIONARY
## SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)
**SMK NEGERI 1 BANGSRI**

Dokumentasi rancangan basis data (*database schema*) dan relasi antar-entitas untuk aplikasi **DIDISPEN** berbasis Laravel 11 dan MySQL/MariaDB, disinkronkan 100% dengan migrasi database aktual.

---

## 1. Visualisasi Diagram ERD (Mermaid)

```mermaid
erDiagram
    USERS ||--o| SISWA : "memiliki profil (1:1)"
    USERS ||--o| GURU : "memiliki profil (1:1)"
    USERS ||--o{ NOTIFIKASI : "menerima (1:N)"
    USERS ||--o{ AUDIT_LOGS : "mencatat aktivitas (1:N)"
    USERS ||--o{ PERTUKARAN_JADWAL_PIKET : "diproses_oleh (1:N)"

    JURUSAN ||--o{ KELAS : "memiliki (1:N)"
    JURUSAN ||--o{ SISWA : "jurusan_id (1:N)"
    KELAS ||--o{ SISWA : "kelas_id (1:N)"

    SISWA ||--o{ DISPENSASI : "mengajukan mandiri (1:N)"
    GURU ||--o{ DISPENSASI : "menyetujui/menolak (1:N)"
    USERS ||--o{ DISPENSASI : "satpam_keluar_id / satpam_kembali_id (1:N)"

    GURU ||--o{ GURU_CHECKLOG : "mencatat keluar dinas (1:N)"
    GURU ||--o{ JADWAL_PIKET : "koordinator_guru_id (1:N)"
    GURU ||--o{ JADWAL_PIKET_GURU : "bertugas di (1:N)"
    JADWAL_PIKET ||--o{ JADWAL_PIKET_GURU : "berisi guru (1:N)"

    JADWAL_PIKET ||--o{ PERTUKARAN_JADWAL_PIKET : "jadwal_piket_id (1:N)"
    GURU ||--o{ PERTUKARAN_JADWAL_PIKET : "guru_asal_id (1:N)"
    GURU ||--o{ PERTUKARAN_JADWAL_PIKET : "guru_pengganti_id (1:N)"

    USERS {
        bigint id PK
        string external_id "Nullable, Index SiPintu UUID"
        string name "Nama User"
        string email "Unique Email"
        string password "Hashed Bcrypt/Argon2"
        string role "admin, guru, satpam, siswa"
        string nis_nip "Nullable, NIS/NIP User"
        integer failed_login_attempts "Default 0"
        datetime locked_until "Nullable"
        datetime email_verified_at "Nullable"
        datetime sipintu_last_synced_at "Nullable"
        string remember_token "Nullable"
        datetime created_at
        datetime updated_at
    }

    SISWA {
        bigint id PK
        bigint user_id FK "Unique, Cascade"
        string nis_nip "Nullable, Unique, NIS Siswa"
        bigint jurusan_id FK "Nullable"
        bigint kelas_id FK "Nullable"
        string nama_lengkap
        date tanggal_lahir "Nullable"
        text alamat "Nullable"
        string no_telepon "Nullable"
        boolean status_aktif "Default true"
        datetime created_at
        datetime updated_at
    }

    GURU {
        bigint id PK
        bigint user_id FK "Unique, Cascade"
        string nip "Unique, NIP Guru"
        string nama_lengkap
        string email "Nullable"
        text mata_pelajaran "Nullable"
        date tanggal_lahir "Nullable"
        string no_telepon "Nullable"
        text alamat "Nullable"
        boolean status_aktif "Default true"
        datetime created_at
        datetime updated_at
    }

    JURUSAN {
        bigint id PK
        string kode_jurusan "Unique (PPLG, AKL, dll)"
        string nama_jurusan "Nama Lengkap Jurusan"
        datetime created_at
        datetime updated_at
    }

    KELAS {
        bigint id PK
        bigint jurusan_id FK "Cascade"
        string nama_kelas "Nama Kelas (XII PPLG 1)"
        enum tingkat "X, XI, XII"
        datetime created_at
        datetime updated_at
    }

    DISPENSASI {
        bigint id PK
        string nomor_surat "Unique, DISP/Ymd/RANDOM"
        bigint siswa_id FK "Cascade, Pemohon Mandiri"
        bigint guru_id FK "Nullable, Guru Piket"
        enum kategori "sakit, izin, keperluan_sekolah, lainnya"
        text alasan
        string tujuan
        string lokasi "Nullable"
        string jam_keluar "Jam Pelajaran / Waktu HH:MM"
        string jam_kembali "Jam Pelajaran / Waktu HH:MM"
        datetime batas_waktu_kembali "Nullable, Datetime Overdue"
        datetime waktu_keluar_aktual "Nullable"
        datetime waktu_kembali_aktual "Nullable"
        enum status "menunggu, disetujui, ditolak, keluar, selesai, dibatalkan, kadaluarsa"
        text catatan_admin "Nullable"
        datetime approved_at "Nullable"
        datetime rejected_at "Nullable"
        string qr_code "Nullable, Path Gambar QR"
        string qr_token "Unique, 64-char Hash"
        string foto_verifikasi "Nullable, Path Foto Selfie Siswa (Validasi Pos Satpam)"
        string foto_bukti "Nullable, Path Foto Bukti Kegiatan di Tujuan"
        datetime foto_bukti_uploaded_at "Nullable"
        bigint satpam_keluar_id FK "Nullable, User Satpam"
        bigint satpam_kembali_id FK "Nullable, User Satpam"
        boolean is_warned "Default false, Overdue Warning"
        datetime warned_at "Nullable"
        boolean dibuat_manual_oleh_guru "Default false"
        integer print_count "Default 0"
        integer student_print_count "Default 0"
        integer teacher_print_count "Default 0"
        integer max_print_limit "Default 3"
        datetime printed_at "Nullable"
        datetime created_at
        datetime updated_at
    }

    GURU_CHECKLOG {
        bigint id PK
        bigint guru_id FK "Guru yang Keluar Dinas"
        text alasan
        string tujuan
        string lokasi "Nullable"
        datetime jam_keluar
        datetime jam_kembali "Nullable"
        enum status "keluar, selesai"
        datetime created_at
        datetime updated_at
    }

    JADWAL_PIKET {
        bigint id PK
        integer hari "1=Senin s/d 7=Minggu"
        string nama_sesi "Nullable, Pagi / Siang"
        time jam_mulai "Format H:i:s"
        time jam_selesai "Format H:i:s"
        bigint koordinator_guru_id FK "Nullable"
        date tanggal_mulai_berlaku
        date tanggal_selesai_berlaku "Nullable"
        boolean is_active "Default true"
        datetime created_at
        datetime updated_at
    }

    JADWAL_PIKET_GURU {
        bigint id PK
        bigint jadwal_piket_id FK
        bigint guru_id FK
        datetime created_at
        datetime updated_at
    }

    PERTUKARAN_JADWAL_PIKET {
        bigint id PK
        bigint jadwal_piket_id FK
        bigint guru_asal_id FK
        bigint guru_pengganti_id FK
        date tanggal
        text alasan "Nullable"
        enum status "menunggu, disetujui, ditolak, dibatalkan"
        boolean is_active "Default false"
        datetime diminta_at "Nullable"
        datetime diproses_at "Nullable"
        bigint diproses_oleh FK "Nullable, User ID"
        text catatan "Nullable"
        datetime created_at
        datetime updated_at
    }

    WHATSAPP_TEMPLATES {
        bigint id PK
        string name "Nama Event Template"
        string slug "Unique Slug"
        text content "Pesan dengan variabel {nama_siswa}, dll"
        boolean is_active "Default true"
        datetime created_at
        datetime updated_at
    }

    NOTIFIKASI {
        bigint id PK
        bigint user_id FK
        text message
        boolean is_read "Default false"
        string link "Nullable, URL Redirect"
        datetime created_at
        datetime updated_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK "Nullable"
        string action "login, logout, approve, reject, dll"
        string table_name
        bigint record_id
        json old_value "Nullable"
        json new_value "Nullable"
        string ip_address "Nullable"
        string device_type "Nullable, Desktop/Mobile"
        string os "Nullable, OS Pengguna"
        string browser "Nullable, Browser Pengguna"
        text user_agent "Nullable"
        datetime created_at
        datetime updated_at
    }

    SETTINGS {
        bigint id PK
        string key "Unique Config Key"
        text value "Nullable"
        datetime created_at
        datetime updated_at
    }

    TUTORIAL_VIDEOS {
        bigint id PK
        string role "siswa, guru, satpam, admin"
        string title "Judul Video Panduan"
        string youtube_url "URL Video YouTube"
        string video_id "Nullable, YouTube Video ID"
        boolean is_active "Default true"
        datetime created_at
        datetime updated_at
    }
```

---

## 2. Kamus Data & Spesifikasi Entitas (Data Dictionary)

### 2.1 Tabel `users`
Menyimpan akun pengguna utama untuk autentikasi terpusat (Role-Based Access Control) dan integrasi SiPintu Identity Gateway.

| Nama Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `id` | BIGINT UNSIGNED | Tidak | Primary Key (Auto Increment). |
| `external_id` | VARCHAR(255) | Ya | UUID akun pengguna dari SiPintu Gateway (Indexed). |
| `name` | VARCHAR(255) | Tidak | Nama display akun pengguna. |
| `email` | VARCHAR(255) | Tidak | Alamat email unik untuk login. |
| `email_verified_at`| TIMESTAMP | Ya | Waktu verifikasi email. |
| `password` | VARCHAR(255) | Tidak | Hash password (Bcrypt/Argon2). |
| `role` | ENUM | Tidak | Hak akses: `'admin'`, `'guru'`, `'satpam'`, `'siswa'`. |
| `nis_nip` | VARCHAR(50) | Ya | Nomor Induk Siswa (NIS) atau NIP Guru untuk login alternatif. |
| `failed_login_attempts` | INT / TINYINT | Tidak | Penghitung gagal login untuk proteksi anti-brute force (Default 0). |
| `locked_until` | TIMESTAMP | Ya | Waktu akhir lockout akun jika 5x berturut-turut gagal login. |
| `sipintu_last_synced_at` | TIMESTAMP | Ya | Waktu terakhir data disinkronkan dari SiPintu (*Conflict Resolution*). |
| `remember_token`| VARCHAR(100) | Ya | Token "Ingat Saya" sesi login Laravel. |
| `created_at` / `updated_at` | TIMESTAMP | Ya | Jejak waktu pembuatan dan pembaruan data. |

---

### 2.2 Tabel `siswa`
Profil terperinci peserta didik yang terhubung dengan akun `users`, `kelas`, dan `jurusan`.

| Nama Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `id` | BIGINT UNSIGNED | Tidak | Primary Key. |
| `user_id` | BIGINT UNSIGNED | Tidak | Foreign Key ke `users.id` (Unique, On Delete Cascade). |
| `nis_nip` | VARCHAR(50) | Ya | Nomor Induk Siswa (Unique). |
| `jurusan_id` | BIGINT UNSIGNED | Ya | Foreign Key ke `jurusan.id`. |
| `kelas_id` | BIGINT UNSIGNED | Ya | Foreign Key ke `kelas.id`. |
| `nama_lengkap` | VARCHAR(255) | Tidak | Nama lengkap siswa sesuai dapodik. |
| `tanggal_lahir` | DATE | Ya | Tanggal lahir siswa. |
| `alamat` | TEXT | Ya | Alamat tempat tinggal. |
| `no_telepon` | VARCHAR(30) | Ya | Nomor WhatsApp aktif siswa untuk notifikasi & komunikasi dispensasi. |
| `status_aktif` | BOOLEAN | Tidak | Status aktif belajar siswa (Default `true`). |
| `created_at` / `updated_at` | TIMESTAMP | Ya | Timestamps. |

---

### 2.3 Tabel `guru`
Profil tenaga pendidik dan guru piket yang memiliki wewenang verifikasi dispensasi.

| Nama Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `id` | BIGINT UNSIGNED | Tidak | Primary Key. |
| `user_id` | BIGINT UNSIGNED | Tidak | Foreign Key ke `users.id` (Unique, On Delete Cascade). |
| `nip` | VARCHAR(50) | Tidak | Nomor Induk Pegawai Guru (Unique). |
| `nama_lengkap` | VARCHAR(255) | Tidak | Nama lengkap dan gelar guru. |
| `email` | VARCHAR(255) | Ya | Email dinas / akun guru. |
| `mata_pelajaran` | TEXT | Ya | Mata pelajaran yang diampu. |
| `tanggal_lahir` | DATE | Ya | Tanggal lahir guru. |
| `no_telepon` | VARCHAR(30) | Ya | Nomor WhatsApp aktif untuk notifikasi piket. |
| `alamat` | TEXT | Ya | Alamat tempat tinggal. |
| `status_aktif` | BOOLEAN | Tidak | Status aktif dinas guru (Default `true`). |
| `created_at` / `updated_at` | TIMESTAMP | Ya | Timestamps. |

---

### 2.4 Tabel `jurusan` & `kelas`
Master data akademik tingkat sekolah.
* **`jurusan`**: `id` (PK), `kode_jurusan` (e.g. *PPLG, AKL, TO* - Unique), `nama_jurusan` (e.g. *Pengembangan Perangkat Lunak dan Gim*).
* **`kelas`**: `id` (PK), `jurusan_id` (FK ke `jurusan`, Cascade), `nama_kelas` (e.g. *XII PPLG 1*), `tingkat` (ENUM: `'X'`, `'XI'`, `'XII'`).

---

### 2.5 Tabel `dispensasi` (Entitas Transaksional Inti)
Menyimpan seluruh data siklus pengajuan izin dispensasi siswa.

| Nama Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `id` | BIGINT UNSIGNED | Tidak | Primary Key. |
| `nomor_surat` | VARCHAR(100) | Tidak | Format nomor resmi unik: `DISP/YYYYMMDD/XXXXXX`. |
| `siswa_id` | BIGINT UNSIGNED | Tidak | Foreign Key ke `siswa.id` (Pemohon dispensasi mandiri). |
| `guru_id` | BIGINT UNSIGNED | Ya | Foreign Key ke `guru.id` (Guru Piket yang memproses / memberi approval). |
| `kategori` | ENUM | Tidak | `'sakit'`, `'izin'`, `'keperluan_sekolah'`, `'lainnya'`. |
| `alasan` | TEXT | Tidak | Deskripsi keperluan/alasan dispensasi (min 10 karakter). |
| `tujuan` | VARCHAR(255) | Tidak | Tempat/instansi tujuan dispensasi. |
| `lokasi` | VARCHAR(255) | Ya | Lokasi spesifik tujuan izin. |
| `jam_keluar` | VARCHAR(50) | Tidak | Jam keluar (fleksibel: `"10:15"` atau `"Jam Pelajaran ke-4"`). |
| `jam_kembali` | VARCHAR(50) | Tidak | Jam kembali (fleksibel: `"12:30"` atau `"Jam Pelajaran ke-7"`). |
| `batas_waktu_kembali`| DATETIME | Ya | Timestamp evaluasi otomatis keterlambatan (*overdue*). |
| `waktu_keluar_aktual`| DATETIME | Ya | Waktu aktual siswa keluar saat di-scan Satpam di pos gerbang. |
| `waktu_kembali_aktual`| DATETIME | Ya | Waktu aktual siswa kembali saat di-scan Satpam di pos gerbang. |
| `status` | ENUM | Tidak | Status: `'menunggu'`, `'disetujui'`, `'ditolak'`, `'keluar'`, `'selesai'`, `'dibatalkan'`, `'kadaluarsa'`. |
| `catatan_admin` | TEXT | Ya | Alasan penolakan dari guru piket atau catatan penutupan otomatis sistem. |
| `approved_at` / `rejected_at` | DATETIME | Ya | Timestamp persetujuan atau penolakan permohonan. |
| `qr_code` | VARCHAR(255) | Ya | Path file gambar QR Code yang di-generate sistem. |
| `qr_token` | VARCHAR(64) | Tidak | Token terenkripsi 64 karakter untuk pemindaian aman di pos satpam. |
| `foto_verifikasi` | VARCHAR(255) | Ya | Path file foto selfie siswa yang diunggah saat pengajuan (dicocokkan Satpam di pos gerbang). |
| `foto_bukti` | VARCHAR(255) | Ya | Path file foto bukti kegiatan yang diunggah siswa saat berada di lokasi tujuan (status 'keluar'). |
| `foto_bukti_uploaded_at` | TIMESTAMP | Ya | Waktu unggah foto bukti kegiatan. |
| `satpam_keluar_id` | BIGINT UNSIGNED | Ya | Foreign Key ke `users.id` (Petugas satpam saat keluar). |
| `satpam_kembali_id`| BIGINT UNSIGNED | Ya | Foreign Key ke `users.id` (Petugas satpam saat kembali). |
| `is_warned` / `warned_at` | BOOLEAN / DATETIME | Tidak / Ya | Flag penanda notifikasi keterlambatan/overdue terkirim. |
| `dibuat_manual_oleh_guru`| BOOLEAN | Tidak | Penanda jika izin dibuatkan langsung di tempat oleh guru piket di ruang piket. |
| `print_count` | INT | Tidak | Akumulasi cetak struk (Default 0). |
| `student_print_count` | INT | Tidak | Batas cetak mandiri oleh siswa (Default 0). |
| `teacher_print_count` | INT | Tidak | Batas cetak oleh guru piket (Default 0). |
| `max_print_limit` | INT | Tidak | Batas kuota cetak per dispensasi (Default 3). |
| `printed_at` | DATETIME | Ya | Waktu pencetakan struk terakhir. |

---

### 2.6 Tabel `guru_checklog`
Mencatat ketersediaan dan mobilitas dinas luar guru selama jam sekolah.
* `id` (PK)
* `guru_id` (FK ke `guru.id`, Cascade)
* `alasan`, `tujuan`, `lokasi` (Detail penugasan luar)
* `jam_keluar` (Datetime waktu guru berangkat)
* `jam_kembali` (Datetime waktu aktual guru tiba kembali)
* `status` (ENUM: `'keluar'`, `'selesai'`)

---

### 2.7 Tabel `jadwal_piket`, `jadwal_piket_guru`, & `pertukaran_jadwal_piket`
* **`jadwal_piket`**: `id` (PK), `hari` (1=Senin s/d 7=Minggu), `nama_sesi` (*Pagi / Siang*), `jam_mulai` (Time), `jam_selesai` (Time), `koordinator_guru_id` (FK ke `guru`, Nullable), `tanggal_mulai_berlaku`, `tanggal_selesai_berlaku`, `is_active`.
* **`jadwal_piket_guru`**: Pivot table Many-to-Many antara `jadwal_piket_id` dan `guru_id`.
* **`pertukaran_jadwal_piket`**: `id` (PK), `jadwal_piket_id` (FK), `guru_asal_id` (FK), `guru_pengganti_id` (FK), `tanggal`, `alasan`, `status` (ENUM: `'menunggu'`, `'disetujui'`, `'ditolak'`, `'dibatalkan'`), `is_active` (Default `false`), `diminta_at`, `diproses_at`, `diproses_oleh` (FK ke `users`), `catatan`.

---

### 2.8 Tabel Pendukung Operasional
* **`whatsapp_templates`**: Menyimpan template pesan dinamis Deep-Link WhatsApp (`name`, `slug`, `content`, `is_active`). Template: `disetujui`, `ditolak`, `keluar`, `kembali`, `terlambat`, `pengingat-piket`, `hubungi-guru-piket`.
* **`notifikasi`**: Pesan pemberitahuan in-app dashboard (`user_id`, `message`, `is_read`, `link`).
* **`audit_logs`**: Catatan jejak audit sistem keamanan (`user_id`, `action`, `table_name`, `record_id`, `old_value`, `new_value`, `ip_address`, `device_type`, `os`, `browser`, `user_agent`).
* **`settings`**: Konfigurasi dinamis sistem (`key`, `value`), misalnya batas jam dispensasi sekolah, hari aktif izin, dan fallback guru piket.
* **`tutorial_videos`**: Video panduan interaktif YouTube per role pengguna (`role`, `title`, `youtube_url`, `video_id`, `is_active`).

---

## 3. Matriks Relasi Antar-Entitas (Cardinallity)

| Entitas Asal | Relasi | Entitas Tujuan | Tipe Relasi | Keterangan Bisnis |
| :--- | :---: | :--- | :---: | :--- |
| `users` | 1 $\rightarrow$ 1 | `siswa` | One-to-One | 1 akun user siswa memiliki 1 profil biodata siswa. |
| `users` | 1 $\rightarrow$ 1 | `guru` | One-to-One | 1 akun user guru memiliki 1 profil biodata guru. |
| `jurusan` | 1 $\rightarrow$ N | `kelas` | One-to-Many | 1 program keahlian/jurusan menaungi banyak rombel kelas. |
| `kelas` | 1 $\rightarrow$ N | `siswa` | One-to-Many | 1 rombel kelas terdiri dari banyak siswa. |
| `siswa` | 1 $\rightarrow$ N | `dispensasi` | One-to-Many | 1 siswa dapat mengajukan dispensasi berulang kali (individual pemohon). |
| `guru` | 1 $\rightarrow$ N | `dispensasi` | One-to-Many | 1 guru piket memverifikasi banyak pengajuan siswa. |
| `users` (Satpam) | 1 $\rightarrow$ N | `dispensasi` | One-to-Many | 1 satpam memvalidasi scan keluar & kembali banyak siswa. |
| `guru` | 1 $\rightarrow$ N | `guru_checklog` | One-to-Many | 1 guru memiliki riwayat checklog dinas luar. |
| `jadwal_piket` | N $\rightarrow$ M | `guru` | Many-to-Many | Dihubungkan via tabel perantara `jadwal_piket_guru`. |
| `guru` | 1 $\rightarrow$ N | `pertukaran_jadwal_piket` | One-to-Many | Guru dapat mengajukan atau menerima tukar jadwal piket. |
| `users` | 1 $\rightarrow$ N | `audit_logs` | One-to-Many | Semua aksi pengguna terekam ke log audit keamanan. |
| `users` | 1 $\rightarrow$ N | `notifikasi` | One-to-Many | Pengguna menerima notifikasi aktivitas dispensasi. |