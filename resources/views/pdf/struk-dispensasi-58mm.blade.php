<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Dispensasi - {{ $dispensasi->nomor_surat }}</title>

<style>
    * {
        box-sizing: border-box;
    }

    @page {
        margin: 0;
    }

    html, body {
        margin: 0;
        padding: 0;
        background: #fff;
        color: #000;
        font-family: 'Courier New', Courier, monospace;
        font-size: 8.5px;
        line-height: 1.25;
    }

    .container {
        width: 100%;
        max-width: 58mm;
        padding: 4mm 3mm;
        margin: 0 auto;
    }

    /* HEADER TABLE (KUNCI AGAR LOGO DAN TEKS PASTI PERSIS DI TENGAH) */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
    }

    .header-table td {
        text-align: center;
        vertical-align: middle;
        padding: 0;
    }

    .logo {
        width: 34px;
        height: 34px;
        display: inline-block;
        margin-bottom: 2px;
    }

    .school-name {
        font-weight: bold;
        font-size: 9.5px;
        margin: 2px 0 1px;
        text-align: center;
    }

    .school-address {
        font-size: 7.5px;
        margin: 0;
        text-align: center;
    }

    /* GARIS PEMBATAS */
    .divider {
        border-top: 1px dashed #000;
        margin: 5px 0;
        width: 100%;
    }

    /* JUDUL */
    .title-table {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
    }

    .title-text {
        text-align: center;
        font-weight: bold;
        font-size: 9.5px;
        letter-spacing: 0.5px;
        text-decoration: underline;
    }

    /* DATA DISPENSASI */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .info-table td {
        padding: 1.5px 0;
        vertical-align: top;
    }

    .info-table td.label {
        width: 26%;
        white-space: nowrap;
    }

    .info-table td.separator {
        width: 5%;
        text-align: center;
    }

    .info-table td.value {
        width: 69%;
        word-break: break-word;
    }

    /* TANDA TANGAN (TABLE BASED) */
    .sign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }

    .sign-space {
        width: 40%;
    }

    .sign-content {
        width: 60%;
        text-align: center;
        vertical-align: top;
    }

    .sign-content .date {
        font-size: 7.5px;
        margin: 0 0 2px;
        text-align: center;
    }

    .sign-content .role {
        font-size: 7.5px;
        margin: 0 0 35px;
        text-align: center;
    }

    .sign-content .name {
        font-weight: bold;
        font-size: 8px;
        text-decoration: underline;
        margin: 0;
        text-align: center;
    }

    .sign-content .nip {
        font-size: 7px;
        margin: 2px 0 0;
        text-align: center;
    }

    /* FOOTER */
    .footer-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    .footer-table td {
        text-align: center;
    }

    .footer-note {
        font-size: 7.5px;
        font-style: italic;
        margin: 3px 0;
        text-align: center;
    }

    .thank-you {
        font-weight: bold;
        font-size: 8px;
        margin: 4px 0 0;
        text-align: center;
    }
</style>
</head>

<body>
<div class="container">

    {{-- HEADER MENGGUNAKAN TABEL SUPAYA SENTRALISASI LOGO TIDAK MENCONG --}}
    <table class="header-table">
        @php
            $logoPath = public_path('images/logo-didispen.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        @endphp

        @if($logoBase64)
            <tr>
                <td>
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo" alt="Logo">
                </td>
            </tr>
        @endif
        <tr>
            <td class="school-name">SMKN 1 BANGSRI</td>
        </tr>
        <tr>
            <td class="school-address">Sistem Informasi Dispensasi</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- JUDUL --}}
    <table class="title-table">
        <tr>
            <td class="title-text">BUKTI DISPENSASI</td>
        </tr>
    </table>

    {{-- EKSTRAKSI WAKTU --}}
    @php
        $extractTime = function ($value) {
            if (empty($value)) return null;
            preg_match('/\b(\d{1,2}[:.]\d{2})\b/', (string) $value, $matches);
            return $matches[1] ?? null;
        };

        $waktuKeluar = $extractTime(\App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar));
        $waktuKembali = $extractTime(\App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali));
    @endphp

    {{-- DATA DISPENSASI --}}
    <table class="info-table">
        <tr>
            <td class="label">No. Surat</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->nomor_surat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->siswa?->user?->nis_nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->siswa?->nama_lengkap ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->siswa?->kelas?->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tujuan</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->tujuan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi</td>
            <td class="separator">:</td>
            <td class="value">{{ $dispensasi->lokasi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jam</td>
            <td class="separator">:</td>
            <td class="value">{{ $waktuKeluar ?? '-' }} - {{ $waktuKembali ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- TANDA TANGAN GURU PIKET --}}
    @php
        $namaGuru = $dispensasi->guru?->nama_lengkap 
            ?? $dispensasi->guru?->user?->name 
            ?? $dispensasi->user?->name 
            ?? 'Guru Piket';

        $nipGuru = $dispensasi->guru?->nip 
            ?? $dispensasi->guru?->user?->nis_nip 
            ?? $dispensasi->user?->nis_nip;
    @endphp

    <table class="sign-table">
        <tr>
            <td class="sign-space"></td>
            <td class="sign-content">
                <div class="date">Bangsri, {{ now()->format('d/m/Y') }}</div>
                <div class="role">Guru Piket,</div>
                <div class="name">{{ $namaGuru }}</div>
                @if(!empty($nipGuru))
                    <div class="nip">NIP. {{ $nipGuru }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <table class="footer-table">
        <tr>
            <td>
                <div>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
                <div class="footer-note">
                    Struk ini sah jika ditandatangani<br>oleh Guru Piket.
                </div>
                <div class="thank-you">- TERIMA KASIH -</div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>