<?php

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

$html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        size: A4 landscape;
        margin: 12mm 15mm 12mm 15mm;
    }
    body {
        font-family: 'Helvetica', Arial, sans-serif;
        color: #0f172a;
        margin: 0;
        padding: 0;
        font-size: 11pt;
        line-height: 1.4;
    }
    .page-break {
        page-break-after: always;
    }
    .header {
        border-bottom: 2px solid #2563eb;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    .header .category {
        font-size: 9pt;
        font-weight: bold;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .header h2 {
        margin: 3px 0 0 0;
        font-size: 18pt;
        color: #0f172a;
    }
    .grid-2 {
        width: 100%;
    }
    .grid-2 td {
        vertical-align: top;
        width: 50%;
        padding: 0 8px;
    }
    .grid-3 {
        width: 100%;
    }
    .grid-3 td {
        vertical-align: top;
        width: 33.33%;
        padding: 0 6px;
    }
    .grid-4 {
        width: 100%;
    }
    .grid-4 td {
        vertical-align: top;
        width: 25%;
        padding: 0 5px;
    }
    .card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 12px 14px;
        min-height: 180px;
    }
    .card-title {
        font-weight: bold;
        font-size: 11pt;
        margin-bottom: 8px;
    }
    .card-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 8pt;
        font-weight: bold;
        margin-bottom: 8px;
    }
    ul, ol {
        margin: 0;
        padding-left: 18px;
    }
    li {
        margin-bottom: 5px;
        font-size: 9.5pt;
        color: #334155;
    }
    .badge-amber { background: #fef3c7; color: #92400e; }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-green { background: #d1fae5; color: #065f46; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    .badge-gray { background: #f1f5f9; color: #475569; }

    /* Table styling */
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table.data-table th, table.data-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        font-size: 9pt;
        text-align: left;
    }
    table.data-table th {
        background: #0f172a;
        color: #ffffff;
        font-weight: bold;
    }
    table.data-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    /* Cover slide */
    .cover-container {
        text-align: center;
        padding-top: 80px;
    }
    .cover-title {
        font-size: 26pt;
        font-weight: bold;
        color: #0f172a;
        margin: 15px 0;
        line-height: 1.2;
    }
    .cover-subtitle {
        font-size: 14pt;
        color: #2563eb;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .cover-desc {
        font-size: 11pt;
        color: #475569;
        max-width: 650px;
        margin: 0 auto 30px auto;
    }
    .cover-footer {
        font-size: 9.5pt;
        color: #64748b;
        margin-top: 50px;
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
    }
</style>
</head>
<body>

<!-- SLIDE 1: COVER -->
<div class="cover-container page-break">
    <div class="cover-subtitle">SMK NEGERI 1 BANGSRI • TAHUN AJARAN 2026/2027</div>
    <div class="cover-title">SOSIALISASI OPERASIONAL & FITUR<br>DIDISPEN (DISPENSASI DIGITAL)</div>
    <div class="cover-desc">
        Panduan Praktis Alur Perizinan & Katalog Fitur Lengkap untuk Guru Piket & Petugas Satpam Gerbang Sekolah
    </div>
    <div style="margin-top: 25px;">
        <span class="card-badge badge-blue" style="font-size: 10pt; padding: 6px 14px;">Paperless</span>
        <span class="card-badge badge-green" style="font-size: 10pt; padding: 6px 14px;">Thermal Bluetooth 58mm</span>
        <span class="card-badge badge-amber" style="font-size: 10pt; padding: 6px 14px;">QR Token 64-Char</span>
        <span class="card-badge badge-red" style="font-size: 10pt; padding: 6px 14px;">Gate Photo Verification</span>
    </div>
    <div class="cover-footer">
        Dikembangkan oleh Tim By 3M: Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief
    </div>
</div>

<!-- SLIDE 2: MENGAPA DIDISPEN -->
<div class="page-break">
    <div class="header">
        <div class="category">TRANSFORMASI DIGITAL SEKOLAH</div>
        <h2>Mengapa Harus Berpindah ke DIDISPEN?</h2>
    </div>
    <table class="grid-2">
        <tr>
            <td>
                <div class="card" style="border-color: #fca5a5;">
                    <div class="card-title" style="color: #dc2626;">🔴 KENDALA SISTEM KERTAS LAMA</div>
                    <ul>
                        <li><strong>Surat Izin Kertas Mudah Hilang:</strong> Sering tercecer, robek, dan sulit diarsipkan untuk keperluan buku induk piket.</li>
                        <li><strong>Potensi 'Joki Izin':</strong> Siswa menitipkan surat izin atau memakai nama teman tanpa verifikasi identitas fisik.</li>
                        <li><strong>Meja Piket & Gerbang Terisolasi:</strong> Satpam di gerbang tidak mengetahui keaslian izin yang dibawa siswa.</li>
                        <li><strong>Siswa Terlambat / Kabur Tidak Terdeteksi:</strong> Tidak ada alarm atau pencatatan presisi batas waktu kembali siswa.</li>
                        <li><strong>Rekapitulasi Manual Memakan Waktu:</strong> Menyita banyak waktu bagi BK dan Kurikulum saat rekap bulanan.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-color: #86efac;">
                    <div class="card-title" style="color: #16a34a;">🟢 SOLUSI MODERN DIDISPEN</div>
                    <ul>
                        <li><strong>100% Real-Time & Paperless:</strong> Seluruh permohonan tersinkronisasi seketika dari meja piket ke pos gerbang.</li>
                        <li><strong>Verifikasi Wajah Ganda:</strong> Wajib selfie wajah saat pengajuan + foto fisik siswa di pos gerbang oleh satpam.</li>
                        <li><strong>QR Token 64-Karakter:</strong> QR Code dinamis sekali pakai (single-use), tidak bisa difotokopi atau di-screenshot ulang.</li>
                        <li><strong>Auto-Overdue Detector:</strong> Sistem otomatis menyalakan layar merah dan menghitung menit keterlambatan siswa.</li>
                        <li><strong>Fleksibilitas Struk Thermal:</strong> Cetak struk mini 58mm via Bluetooth jika siswa tidak memegang ponsel.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SLIDE 3: GAMBARAN BESAR ALUR KERJA (4 LANGKAH) -->
<div class="page-break">
    <div class="header">
        <div class="category">ALUR OPERASIONAL UTAMA</div>
        <h2>Gambaran Besar Alur Kerja Dispensasi (4 Langkah)</h2>
    </div>
    <table class="grid-4">
        <tr>
            <td>
                <div class="card" style="border-top: 4px solid #f59e0b;">
                    <div style="font-size: 8pt; font-weight: bold; color: #d97706;">LANGKAH 1</div>
                    <div class="card-title">Siswa Mengajukan</div>
                    <span class="card-badge badge-amber">Status: Menunggu</span>
                    <ul>
                        <li>Siswa isi form di HP (jam KBM, alasan, tujuan).</li>
                        <li>Wajib jepret foto selfie wajah.</li>
                        <li>Bisa pengajuan rombongan (lomba/ekskul).</li>
                        <li>Siswa menemui guru piket di meja piket.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #2563eb;">
                    <div style="font-size: 8pt; font-weight: bold; color: #2563eb;">LANGKAH 2</div>
                    <div class="card-title">Guru Piket ACC</div>
                    <span class="card-badge badge-blue">Status: Disetujui</span>
                    <ul>
                        <li>Guru piket periksa bukti & foto selfie.</li>
                        <li>Klik <strong>Setujui</strong> ➔ QR Token terbit.</li>
                        <li>Opsi cetak Struk Bluetooth 58mm bagi siswa tanpa HP.</li>
                        <li>Jika tolak, wajib berikan alasan penolakan.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #0f172a;">
                    <div style="font-size: 8pt; font-weight: bold; color: #0f172a;">LANGKAH 3</div>
                    <div class="card-title">Satpam Scan Keluar</div>
                    <span class="card-badge badge-gray">Status: Keluar</span>
                    <ul>
                        <li>Siswa tunjukkan QR di HP / Struk kertas.</li>
                        <li>Satpam scan QR via kamera pos gerbang.</li>
                        <li>Satpam ambil foto fisik siswa di pos gerbang.</li>
                        <li>Klik <strong>Konfirmasi Keluar</strong> & buka gerbang.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #10b981;">
                    <div style="font-size: 8pt; font-weight: bold; color: #10b981;">LANGKAH 4</div>
                    <div class="card-title">Satpam Scan Masuk</div>
                    <span class="card-badge badge-green">Status: Selesai</span>
                    <ul>
                        <li>Siswa tiba kembali & melapor ke pos satpam.</li>
                        <li>Satpam scan ulang QR Code siswa.</li>
                        <li><strong>Layar Hijau:</strong> Tepat waktu, izin selesai.</li>
                        <li><strong>Layar Merah:</strong> Terlambat (Overdue) + hitung menit telat + alert WA.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SLIDE 4: SIKLUS 7 STATUS DISPENSASI -->
<div class="page-break">
    <div class="header">
        <div class="category">STATE MACHINE LIFECYCLE</div>
        <h2>Siklus 7 Status Dispensasi di Sistem DIDISPEN</h2>
    </div>
    <table class="grid-2">
        <tr>
            <td>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-amber">1. MENUNGGU</span>
                    <div style="font-size: 9pt; color: #334155;">Siswa telah mengirim pengajuan mandiri. Menunggu peninjauan & persetujuan dari Guru Piket sesi aktif.</div>
                </div>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-blue">2. DISETUJUI</span>
                    <div style="font-size: 9pt; color: #334155;">Guru piket menyetujui izin (atau dibuat langsung via Dispen Mandiri). QR Token 64-char resmi terbit.</div>
                </div>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-red">3. DITOLAK</span>
                    <div style="font-size: 9pt; color: #334155;">Izin ditolak oleh guru piket disertai catatan alasan resmi (misal: ada ujian penting / alasan tidak logis).</div>
                </div>
                <div class="card" style="min-height: 70px;">
                    <span class="card-badge badge-blue" style="background:#e0f2fe; color:#0369a1;">4. KELUAR</span>
                    <div style="font-size: 9pt; color: #334155;">Satpam berhasil memindai QR di gerbang & mengambil foto pos. Jam keluar aktual tercatat, siswa di luar.</div>
                </div>
            </td>
            <td>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-green">5. SELESAI</span>
                    <div style="font-size: 9pt; color: #334155;">Siswa telah kembali ke sekolah dan discan masuk oleh satpam. Tercatat apakah tepat waktu atau terlambat.</div>
                </div>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-gray">6. KADALUARSA</span>
                    <div style="font-size: 9pt; color: #334155;">Pengajuan berstatus 'menunggu' yang tidak sempat direspons guru hingga jam sekolah usai otomatis hangus.</div>
                </div>
                <div class="card" style="margin-bottom: 10px; min-height: 70px;">
                    <span class="card-badge badge-gray" style="background:#f1f5f9; color:#94a3b8;">7. DIBATALKAN</span>
                    <div style="font-size: 9pt; color: #334155;">Siswa membatalkan izin sendiri sebelum scan gerbang, atau auto-cancel jika tidak pernah scan keluar.</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SLIDE 5: FITUR UNGGULAN GURU PIKET -->
<div class="page-break">
    <div class="header">
        <div class="category">MODUL GURU PIKET</div>
        <h2>Katalog Fitur Lengkap untuk Bapak/Ibu Guru Piket</h2>
    </div>
    <table class="grid-3">
        <tr>
            <td>
                <div class="card" style="border-top: 4px solid #2563eb;">
                    <div class="card-title" style="color: #2563eb;">Otorisasi Shift Sesi</div>
                    <span class="card-badge badge-blue">Shift Sesi 1 & Sesi 2</span>
                    <ul>
                        <li><strong>Badge Bertugas Aktif:</strong> Tombol Setujui/Tolak hanya aktif untuk guru piket di sesi jam berjalan.</li>
                        <li><strong>Mode Pemantau:</strong> Guru non-piket otomatis masuk mode read-only (mencegah salah pencet).</li>
                        <li><strong>Rollover Pending:</strong> Izin yang belum sempat di-ACC di Sesi 1 dapat dilanjutkan oleh Guru Sesi 2.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #10b981;">
                    <div class="card-title" style="color: #10b981;">Cetak Struk 58mm</div>
                    <span class="card-badge badge-green">Thermal Bluetooth & PDF</span>
                    <ul>
                        <li><strong>Web Bluetooth Direct:</strong> Kirim byte ESC/POS langsung dari browser ke printer mini 58mm tanpa driver.</li>
                        <li><strong>Unduh Struk PDF:</strong> Format struk 58mm untuk dicetak via printer kabel meja piket.</li>
                        <li><strong>Dispensasi Mandiri:</strong> Izin darurat yang diinput guru langsung berstatus <em>Disetujui</em> otomatis.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #f59e0b;">
                    <div class="card-title" style="color: #d97706;">Checklog & Tukar Shift</div>
                    <span class="card-badge badge-amber">Manajemen Tugas Guru</span>
                    <ul>
                        <li><strong>Guru Checklog:</strong> Catat waktu keluar tugas dinas luar & jam tiba kembali ke sekolah.</li>
                        <li><strong>Tukar Jadwal Piket:</strong> Ajukan penggantian piket ke guru rekan jika berhalangan hadir.</li>
                        <li><strong>Proteksi Real-Time:</strong> Tanggal tukar piket terkunci di bulan berjalan (anti-backdate).</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SLIDE 6: FITUR UNGGULAN POS SATPAM -->
<div class="page-break">
    <div class="header">
        <div class="category">MODUL POS SATPAM</div>
        <h2>Katalog Fitur Lengkap untuk Petugas Satpam Gerbang</h2>
    </div>
    <table class="grid-3">
        <tr>
            <td>
                <div class="card" style="border-top: 4px solid #dc2626;">
                    <div class="card-title" style="color: #dc2626;">Scanner Kamera & Audio</div>
                    <span class="card-badge badge-red">Gerbang Keamanan</span>
                    <ul>
                        <li><strong>Live QR Scanner:</strong> Pemindaian cepat di bawah 1 detik via HP Android / Tablet pos satpam.</li>
                        <li><strong>Kontrol Kamera:</strong> Pilihan kamera depan/belakang serta tombol lampu senter flash.</li>
                        <li><strong>Audio Beep:</strong> Suara nada konfirmasi otomatis saat pemindaian QR Code berhasil.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #0f172a;">
                    <div class="card-title" style="color: #0f172a;">Gate Photo Verification</div>
                    <span class="card-badge badge-gray">Bukti Otentik Gerbang</span>
                    <ul>
                        <li><strong>Wajib Foto Siswa di Pos:</strong> Satpam memotret fisik siswa di gerbang saat hendak keluar.</li>
                        <li><strong>Cegah Joki Izin:</strong> Satpam mencocokkan wajah siswa asli dengan foto profil di layar scanner.</li>
                        <li><strong>Bukti Hukum Sekolah:</strong> Tersimpan di server jika ada komplain dari pihak wali murid.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #2563eb;">
                    <div class="card-title" style="color: #2563eb;">Auto-Overdue & Manual</div>
                    <span class="card-badge badge-blue">Deteksi & Cadangan</span>
                    <ul>
                        <li><strong>Deteksi Telat Otomatis:</strong> Layar merah menyala & hitung menit keterlambatan saat siswa kembali.</li>
                        <li><strong>Pencarian Manual:</strong> Cari berdasarkan NIS / Nama jika layar HP siswa pecah / kamera error.</li>
                        <li><strong>Kartu Guru Piket Hari Ini:</strong> Kontak direct call / WA ke meja piket untuk koordinasi cepat.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SLIDE 7: TROUBLESHOOTING & PENANGANAN LAPANGAN -->
<div class="page-break">
    <div class="header">
        <div class="category">PANDUAN OPERASIONAL</div>
        <h2>Matriks Penanganan Kendala Lapangan (Troubleshooting)</h2>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;">Kendala Lapangan</th>
                <th style="width: 36%;">Tindakan Guru Piket</th>
                <th style="width: 36%;">Tindakan Petugas Satpam</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Siswa Tidak Membawa HP / Baterai Drop</strong></td>
                <td>Klik tombol <em>'Cetak Bluetooth'</em> atau <em>'Download PDF'</em> struk kasir 58mm di meja piket.</td>
                <td>Pindai QR Code dari struk kertas thermal yang ditunjukkan oleh siswa.</td>
            </tr>
            <tr>
                <td><strong>Kamera Scanner Satpam Gelap / Error</strong></td>
                <td>Pastikan permohonan siswa sudah diklik <em>'Setujui'</em> di akun guru piket.</td>
                <td>Buka tab <em>'Pencarian Manual'</em> ➔ Ketik NIS atau Nama Siswa ➔ Klik Konfirmasi.</td>
            </tr>
            <tr>
                <td><strong>Wajah Siswa Tidak Cocok dengan Foto (Joki)</strong></td>
                <td>Tolak pengajuan dan catat nomor induk siswa pada daftar pelanggaran piket.</td>
                <td><strong>Tolak buka gerbang</strong>, tahan siswa di pos gerbang, dan telepon meja piket.</td>
            </tr>
            <tr>
                <td><strong>Siswa Terlambat Melewati Jam Izin</strong></td>
                <td>Cek tab <em>'Siswa Terlambat'</em> di dashboard & hubungi siswa/wali via tombol WA.</td>
                <td>Klik <em>'Konfirmasi Masuk (Overdue)'</em> agar menit keterlambatan tercatat otomatis.</td>
            </tr>
            <tr>
                <td><strong>Guru Piket Berhalangan Hadir</strong></td>
                <td>Ajukan <em>'Tukar Jadwal Piket'</em> ke guru pengganti sebelum sesi jam dinas dimulai.</td>
                <td>Pantau kartu <em>'Guru Piket Hari Ini'</em> di dashboard untuk koordinasi petugas baru.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- SLIDE 8: CHECKLIST HARIAN & PENUTUP -->
<div>
    <div class="header">
        <div class="category">KOMITMEN BERSAMA</div>
        <h2>Checklist Tanggung Jawab Harian Guru Piket & Satpam</h2>
    </div>
    <table class="grid-2">
        <tr>
            <td>
                <div class="card" style="border-top: 4px solid #2563eb;">
                    <div class="card-title" style="color: #2563eb;">📋 CHECKLIST GURU PIKET</div>
                    <ul>
                        <li>Login akun & pastikan muncul badge <strong>'Sedang Bertugas Aktif'</strong>.</li>
                        <li>Tinjau antrean pengajuan berstatus <em>'Menunggu'</em> secara berkala.</li>
                        <li>Periksa foto selfie wajah & kesesuaian jam kembali sebelum klik Setujui.</li>
                        <li>Wajib berikan catatan alasan jika terpaksa menolak izin siswa.</li>
                        <li>Cetak struk thermal 58mm bagi siswa yang tidak memegang ponsel.</li>
                        <li>Lakukan operan antrean tertunda jika berganti ke guru piket sesi berikutnya.</li>
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-top: 4px solid #10b981;">
                    <div class="card-title" style="color: #10b981;">🛡️ CHECKLIST SATPAM GERBANG</div>
                    <ul>
                        <li>Pastikan perangkat pos gerbang terisi daya & izin akses kamera aktif.</li>
                        <li>Jangan izinkan siswa keluar gerbang sebelum QR Code berhasil dipindai.</li>
                        <li>Wajib ambil foto fisik siswa di pos gerbang sebelum membuka palang pintu.</li>
                        <li>Pindai ulang QR Code saat siswa kembali untuk menandai izin selesai.</li>
                        <li>Perhatikan warna layar: jika Merah (Overdue), ingatkan siswa terkait telatnya.</li>
                        <li>Gunakan tab pencarian manual jika scanner atau HP siswa terkendala.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-top: 25px; font-size: 9.5pt; color: #64748b;">
        © 2026 <strong>DIDISPEN — SMK Negeri 1 Bangsri</strong>. Dirancang & dibangun oleh Tim By 3M.
    </div>
</div>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$outputPath = '/home/zainal/Documents/Digipen/DIDISPEN_Sosialisasi_Alur_dan_Fitur.pdf';
file_put_contents($outputPath, $dompdf->output());

echo "PDF saved successfully to: " . $outputPath . PHP_EOL;
