bagian siswa itu ketika guru tidak ada di tempat nah kan ada modal untuk hubungi guru itu yang muncul cuma 1 guru saja tidak kan itu jadwalnya ada 2 ya menyesuaikan degan jadwal yang telah di atur oleh admin lah.

pertanyaan
Rencana Solusi yang Akan Dibuat:
Controller (

DashboardController.php
 & 

PengajuanController.php
):

Mengambil seluruh guru piket yang bertugas pada sesi tersebut ($daftarGuruPiket = [...]), bukan hanya guru pertama.
Tetap memperhitungkan jika ada guru pengganti / jadwal pertukaran shift resmi.
Endpoint WhatsApp (hubungi-guru-piket) disesuaikan agar bisa menerima parameter guru_id, sehingga siswa bisa memilih menghubungi guru yang diinginkan.
Tampilan Modal Pop-up (Dashboard & Detail Pengajuan):

Modal akan me-looping dan menampilkan kartu untuk semua guru piket yang sedang bertugas pada sesi tersebut.
Masing-masing guru memiliki nama, status bertugas, nomor telepon, dan tombol "Hubungi via WhatsApp" tersendiri.
Pertanyaan Konfirmasi untuk Anda:
Desain Tombol WhatsApp di Modal:

Opsi A (Rekomendasi): Tiap kartu guru memiliki tombol WhatsApp-nya masing-masing langsung di dalam kartu (misal: Kartu Guru 1 ada tombol "Hubungi Bpk. Ahmad", Kartu Guru 2 ada tombol "Hubungi Ibu Trias").
Opsi B: Siswa memilih salah satu guru dengan radio button/klik kartu, lalu tombol "Buka WhatsApp" di bagian bawah akan menghubungi guru yang dipilih.
Cakupan Halaman:

Fitur modal "Hubungi Guru Piket" ini ada di 2 halaman:
Dashboard Siswa (/siswa/dashboard)
Detail Pengajuan Siswa (/siswa/pengajuan/{id}) Apakah Anda setuju kita perbarui keduanya agar seragam menampilkan seluruh guru yang bertugas?
Jika Opsi A dan pembaruan di kedua halaman sudah sesuai dengan yang Anda maksud, silakan konfirmasi dan saya akan langsung mengeksekusinya!

jawaban
buak menampilkan seluruh guru, hanya menampilkan guru yanng sesuai sesinya/ jam nya saja  nanti di dashboard siswa klik wa hubad modal hubungi gurupiket nah itu ada guru yang sedang bertugas sesuai dengan jam dan sesinya misal ada dua ya menampilkan dua nanti ketika sesinya selesai ya ganti guru, menyesuaikan kalo admin ngatur jadwalnya ada yang 3 guru misal itu juga nanti di sesuaikan.