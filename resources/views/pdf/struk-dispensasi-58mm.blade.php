<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Dispensasi - {{ $dispensasi->nomor_surat }}</title>
    <style>
        /* Pengaturan khusus untuk kertas thermal 58mm */
        @page {
            size: 58mm auto;
            margin: 2mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            width: 54mm;
            margin: 0 auto;
            color: #000;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .logo {
            max-height: 35px;
            max-width: 35px;
            margin-bottom: 3px;
        }
        .school-name {
            font-weight: bold;
            font-size: 11px;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .school-address {
            font-size: 8px;
            margin: 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 8px 0;
            text-decoration: underline;
            letter-spacing: 1px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 30%;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .qr-section {
            text-align: center;
            margin: 8px 0;
        }
        .qr-section img, .qr-section svg {
            width: 100px;
            height: 100px;
            max-width: 100px;
        }
        .qr-token {
            font-size: 8px;
            word-break: break-all;
            margin-top: 2px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
        }
        .status-badge {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin: 5px 0;
            border: 1px solid #000;
            padding: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    {{-- HEADER: Logo & Identitas Sekolah --}}
    <div class="header">
        {{-- Pastikan file logo-smkn1.png sudah ada di folder public/images/ --}}
        @if(file_exists(public_path('images/logo-smkn1.png')))
            <img src="{{ public_path('images/logo-smkn1.png') }}" class="logo" alt="Logo">
        @endif
        <p class="school-name">SMK N 1 BANGSRI</p>
        <p class="school-address">Desa Bangsri, Kab. Jepara</p>
        <p class="school-address">Sistem Informasi Dispensasi</p>
    </div>

    {{-- JUDUL STRUK --}}
    <div class="title">BUKTI DISPENSASI</div>

    {{-- STATUS --}}
    <div class="status-badge">
        {{ strtoupper($dispensasi->status) }}
    </div>

    {{-- DATA SISWA & DISPENSASI --}}
    <table class="info-table">
        <tr>
            <td>No. Surat</td>
            <td>: {{ $dispensasi->nomor_surat }}</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>: {{ $dispensasi->siswa->nama_lengkap }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>: {{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>: {{ $dispensasi->tujuan }}</td>
        </tr>
        <tr>
            <td>Keluar</td>
            <td>: {{ $dispensasi->jam_keluar }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td>: {{ $dispensasi->jam_kembali }}</td>
        </tr>
        <tr>
            <td>Guru Piket</td>
            <td>: {{ $dispensasi->guru->nama_lengkap ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ✅ QR CODE: Memanggil file yang sudah tersimpan (BEBAS ERROR IMAGICK) --}}
    <div class="qr-section">
        @if($dispensasi->qr_code && file_exists(public_path($dispensasi->qr_code)))
            {{-- Memanggil file SVG/PNG yang sudah di-generate saat pengajuan --}}
            <img src="{{ public_path($dispensasi->qr_code) }}" alt="QR Code">
        @else
            {{-- Fallback jika file tidak ditemukan (menggunakan SVG inline yang tidak butuh imagick) --}}
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate($dispensasi->qr_token) !!}
        @endif
        <p class="qr-token">{{ $dispensasi->qr_token }}</p>
    </div>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
        <p>Simpan struk ini dan tunjukkan<br>kepada petugas Satpam.</p>
        <p style="margin-top: 10px; font-weight: bold;">- TERIMA KASIH -</p>
    </div>

</body>
</html>