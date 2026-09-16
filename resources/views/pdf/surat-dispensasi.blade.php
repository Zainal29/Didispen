<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Dispensasi {{ $dispensasi->nomor_surat }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 20px 30px;
        }

        /* HEADER / KOP SURAT */
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 15px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-left {
            width: 65px;
            text-align: center;
        }

        .logo-left img {
            width: 55px; /* Ukuran logo diperkecil agar pas */
            height: auto;
        }

        .school-info {
            text-align: center;
        }

        .school-info h2 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        .school-info p {
            margin: 2px 0;
            font-size: 9pt;
        }

        .tagline-img {
            max-width: 220px;
            height: auto;
            margin-top: 4px;
        }

        /* JUDUL SURAT */
        .title {
            text-align: center;
            margin: 12px 0 15px 0;
        }

        .title h3 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .title span {
            font-size: 10.5pt;
            font-weight: normal;
        }

        /* ISI SURAT */
        .content {
            font-size: 11pt;
        }

        .table-data {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        .table-data td {
            padding: 3px 5px;
            vertical-align: top;
        }

        .table-data td.label {
            width: 140px;
            font-weight: bold;
        }

        .table-data td.colon {
            width: 10px;
            text-align: center;
        }

        .catatan-box {
            margin: 8px 0;
            padding: 5px 8px;
            background-color: #f9f9f9;
            border-left: 3px solid #666;
            font-style: italic;
            font-size: 10pt;
        }

        /* TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 20px;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ttd-table td {
            vertical-align: top;
        }

        .ttd-space {
            height: 50px;
        }

        /* FOOTER (Mengikuti alur dokumen, tidak melayang di paling bawah) */
        .footer {
            margin-top: 25px;
            font-size: 8pt;
            color: #555;
            border-top: 1px dashed #999;
            padding-top: 6px;
            text-align: center;
        }

        /* WATERMARK */
        .watermark-logo {
            position: fixed;
            top: 40%;
            left: 30%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.04;
            z-index: -1000;
            width: 250px;
        }
    </style>
</head>
<body>

    <!-- WATERMARK LOGO DI BACKGROUND -->
    <img src="{{ public_path('images/logo-didispen.png') }}" class="watermark-logo" alt="Watermark">

    <!-- HEADER / KOP SURAT -->
    <table class="header-table">
        <tr>
            <td class="logo-left">
                <img src="{{ public_path('images/logo-didispen.png') }}" alt="Logo SMK">
            </td>
            <td class="school-info">
                <h2>SMK NEGERI 1 BANGSRI</h2>
                <p><strong>NPSN:</strong> 20360586 | <strong>Telp:</strong> (0291) 772322</p>
                <p>Jl. KH Achmad Fauzan No.17, Krasak, Bangsri, Kec. Bangsri, Kab. Jepara, Jawa Tengah 59415</p>
                @if(file_exists(public_path('images/tagline.png')))
                    <img src="{{ public_path('images/tagline.png') }}" class="tagline-img" alt="Tagline">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL SURAT -->
    <div class="title">
        <h3>SURAT DISPENSASI</h3>
        <span>Nomor: {{ $dispensasi->nomor_surat }}</span>
    </div>

    <!-- ISI SURAT -->
    <div class="content">
        <p style="margin-bottom: 8px;">Yang bertanda tangan di bawah ini, Guru Piket SMK Negeri 1 Bangsri menerangkan bahwa:</p>

        <table class="table-data">
            <tr>
                <td class="label">Nama Siswa</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->siswa->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">NIS / NISN</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas / Jurusan</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }} ({{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }})</td>
            </tr>
            <tr>
                <td class="label">Kategori Izin</td>
                <td class="colon">:</td>
                <td>{{ ucfirst(str_replace('_', ' ', $dispensasi->kategori)) }}</td>
            </tr>
            <tr>
                <td class="label">Alasan</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->alasan }}</td>
            </tr>
            <tr>
                <td class="label">Tujuan</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->tujuan }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->lokasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jam Keluar</td>
                <td class="colon">:</td>
                <td><strong>{{ $dispensasi->jam_keluar }}</strong></td>
            </tr>
            <tr>
                <td class="label">Jam Kembali</td>
                <td class="colon">:</td>
                <td><strong>{{ $dispensasi->jam_kembali }}</strong></td>
            </tr>
        </table>

        @if($dispensasi->catatan_admin)
        <div class="catatan-box">
            <strong>Catatan Guru:</strong> "{{ $dispensasi->catatan_admin }}"
        </div>
        @endif

        <p style="margin-top: 10px;">Demikian surat dispensasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <!-- TANDA TANGAN -->
    <div class="ttd-container">
        <table class="ttd-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    Bangsri, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Guru Piket,<br>
                    <div class="ttd-space"></div>
                    <strong><u>{{ $dispensasi->guru?->nama_lengkap ?? $dispensasi->guru?->user?->name ?? 'Guru Piket' }}</u></strong><br>
                    NIP. {{ $dispensasi->guru?->nip ?? $dispensasi->guru?->user?->nis_nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <strong>Dokumen Resmi Sekolah - Dikeluarkan Secara Otomatis oleh Sistem</strong><br>
        Dicetak: {{ now()->format('d-m-Y H:i') }} WIB | Sistem Informasi Dispensasi SMK Negeri 1 Bangsri
    </div>

</body>
</html>
