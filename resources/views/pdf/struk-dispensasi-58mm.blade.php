<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Dispensasi - {{ $dispensasi->nomor_surat }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 2mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 54mm;
            margin: 0 auto;
            color: #000;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-bottom: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            /* Filter untuk memastikan logo jadi hitam putih di thermal */
            filter: grayscale(100%) contrast(1.2);
        }
        .school-name {
            font-weight: bold;
            font-size: 12px;
            margin: 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .school-address {
            font-size: 9px;
            margin: 1px 0;
            text-align: center;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0;
            text-decoration: underline;
            letter-spacing: 1px;
        }
        .status-badge {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin: 6px 0;
            border: 1px solid #000;
            padding: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .info-table td.label {
            width: 28%;
            white-space: nowrap;
        }
        .info-table td.separator {
            width: 5%;
            text-align: center;
        }
        .info-table td.value {
            width: 67%;
            word-wrap: break-word;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .signature-section {
            margin-top: 12px;
            text-align: right;
            padding-right: 5px;
        }
        .signature-section .date {
            font-size: 10px;
            margin-bottom: 3px;
        }
        .signature-section .role {
            font-size: 10px;
            margin-bottom: 40px;
        }
        .signature-section .name {
            font-weight: bold;
            font-size: 11px;
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 2px;
            min-width: 100px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 12px;
        }
        .footer-note {
            font-size: 9px;
            font-style: italic;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        @php
            $logoPath = public_path('images/logo.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        @endphp

        @if($logoBase64)
            <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo" alt="Logo">
        @else
            <div style="font-size: 30px; margin-bottom: 5px;">🏫</div>
        @endif

        <p class="school-name">SMKN 1 BANGSRI</p>
        <p class="school-address">Desa Bangsri, Kab. Jepara</p>
        <p class="school-address">Sistem Informasi Dispensasi</p>
    </div>

    {{-- JUDUL --}}
    <div class="title">BUKTI DISPENSASI</div>

    {{-- STATUS --}}
    <!--<div class="status-badge">
        {{ strtoupper($dispensasi->status) }}
    </div>-->

    {{-- DATA --}}
    <table class="info-table">
        <tr>
            <td class="label">No. Surat</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->nomor_surat }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->siswa->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tujuan</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->tujuan }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->lokasi }}</td>
        </tr>
        <tr>
            <td class="label">Keluar</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->jam_keluar }}</td>
        </tr>
        <tr>
            <td class="label">Kembali</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->jam_kembali }}</td>
        </tr>
        <!--<tr>
            <td class="label">Guru Piket</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->guru->nama_lengkap ?? '-' }}</td>
        </tr>-->
    </table>

    <div class="divider"></div>

    {{-- TANDA TANGAN --}}
    <div class="signature-section">
        <p class="date">SMKN 1 Bangsri, {{ now()->format('d/m/Y') }}</p>
        <p class="role">Guru Piket,</p>
        <p class="name">{{ $dispensasi->guru->nama_lengkap ?? '________________' }}</p>
    </div>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
        <p class="footer-note">Struk ini sah jika ditandatangani<br>oleh Guru Piket.</p>
        <p style="margin-top: 10px; font-weight: bold;">- TERIMA KASIH -</p>
    </div>

</body>
</html>
