# Catatan Diskusi & Kesepakatan Fitur Kadaluarsa Dispensasi

### 1. Masalah yang Ditemukan:
- **Dispensasi Disetujui tapi Tidak Segera Scan:** Ketika dispensasi sudah disetujui guru piket, tetapi siswa tidak kunjung melakukan scan keluar di pos gerbang Satpam hingga jam KBM hari tersebut berakhir, statusnya sebelumnya masih menggantung "Disetujui".
- **Dispensasi Menunggu yang Tidak Segera Diproses:** Siswa sudah mengajukan dispensasi namun belum disetujui guru piket, dan belum ada peringatan bahwa permohonan tersebut akan hangus/kadaluarsa jika melewati jam KBM.

### 2. Aturan & Solusi yang Telah Diterapkan:
1. **Otomasi Status Kadaluarsa Saat Jam KBM Berakhir:**
   - Dispensasi berstatus `disetujui` tanpa scan keluar (`waktu_keluar_aktual` kosong) otomatis berubah status menjadi **`kadaluarsa`** begitu jam pelajaran sekolah hari itu berakhir (`TimeHelper::isKbmHariSelesai()`), dengan catatan: *"Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang hingga jam KBM berakhir."*
   - Status badge di sistem menampilkan secara jelas: **Kadaluarsa (Tidak Scan Keluar)**.
   - QR Code otomatis dinonaktifkan sehingga tidak bisa diproses lagi di pos satpam.
   - Pengecekan batas jam KBM ini diintegrasikan langsung ke runtime sistem (`DispensasiService::autoExpireOverdueDispensasi`), sehingga status otomatis ter-update saat halaman dibuka oleh Siswa, Guru, Satpam, maupun Admin tanpa bergantung background cron.

2. **Peringatan / Alert untuk Status Menunggu:**
   - **Di Siswa (Dashboard & Detail Pengajuan):**
     Ditampilkan banner alert oranye/kuning beranimasi: *"Pengajuan Ini Akan Segera Kadaluarsa jika tidak disetujui hingga jam KBM berakhir (pukul [Jam Selesai KBM] WIB). Segera temui Guru Piket di ruang piket atau hubungi via WhatsApp untuk menyelesaikan prosesnya."*
   - **Di Guru Piket & Admin (Daftar Pengajuan & Detail):**
     - Pada tabel riwayat dan mobile card pengajuan status menunggu hari ini, terdapat penanda: *"Kadaluarsa pukul [Jam Selesai KBM] WIB"*.
     - Pada halaman detail, terdapat alert peringatan agar guru segera mengambil keputusan (Setujui atau Tolak) sebelum berkas kadaluarsa otomatis.
   - **Batas Akhir:**
     Jika jam KBM berakhir dan pengajuan tetap belum disetujui, status otomatis berubah menjadi **`kadaluarsa`** dengan catatan: *"Kadaluarsa otomatis: Pengajuan tidak disetujui Guru Piket hingga jam KBM berakhir."*
    