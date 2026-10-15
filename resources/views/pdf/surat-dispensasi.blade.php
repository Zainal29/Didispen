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

        @if($dispensasi->tipe_pengajuan === 'organisasi')
        <table class="table-data" style="margin-bottom: 12px;">
            <tr>
                <td class="label">Organisasi / Ekskul</td>
                <td class="colon">:</td>
                <td><strong>{{ $dispensasi->nama_organisasi ?? 'Organisasi Sekolah' }}</strong></td>
            </tr>
            @if($dispensasi->gurus && $dispensasi->gurus->count() > 0)
            <tr>
                <td class="label">Guru Pembina</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->gurus->pluck('nama_lengkap')->join(', ') }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Penanggung Jawab / Ketua</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->siswa->nama_lengkap }} ({{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }})</td>
            </tr>
            <tr>
                <td class="label">Tanggal Pelaksanaan</td>
                <td class="colon">:</td>
                <td>
                    @if($dispensasi->tanggal_selesai && $dispensasi->durasi_hari > 1)
                        <strong>{{ \Carbon\Carbon::parse($dispensasi->tanggal_izin)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($dispensasi->tanggal_selesai)->translatedFormat('d F Y') }} ({{ $dispensasi->durasi_hari }} hari)</strong>
                    @else
                        <strong>{{ \Carbon\Carbon::parse($dispensasi->tanggal_izin ?? $dispensasi->created_at)->translatedFormat('l, d F Y') }} (1 hari)</strong>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Keperluan / Alasan</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->alasan }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi Kegiatan</td>
                <td class="colon">:</td>
                <td>{{ $dispensasi->lokasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Waktu Dispensasi</td>
                <td class="colon">:</td>
                <td>
                    {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek) }}
                    &rarr;
                    <strong>{{ $dispensasi->sampai_pulang ? 'Sampai Pulang Sekolah (Akhir KBM)' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek) }}</strong>
                </td>
            </tr>
        </table>

        <div class="catatan-box" style="margin-top: 4px; margin-bottom: 12px; font-style: normal;">
            <strong>Catatan Pelaksanaan:</strong> Dilaksanakan pada tanggal 
            @if($dispensasi->tanggal_selesai && $dispensasi->durasi_hari > 1)
                @php
                    $startCarbon = \Carbon\Carbon::parse($dispensasi->tanggal_izin);
                    $endCarbon = \Carbon\Carbon::parse($dispensasi->tanggal_selesai);
                    $formatRentang = $startCarbon->isSameMonth($endCarbon)
                        ? ($startCarbon->format('d') . ' - ' . $endCarbon->translatedFormat('d F Y'))
                        : ($startCarbon->translatedFormat('d F') . ' - ' . $endCarbon->translatedFormat('d F Y'));
                @endphp
                <strong>{{ $formatRentang }} ({{ $dispensasi->durasi_hari }} hari)</strong>
            @else
                <strong>{{ \Carbon\Carbon::parse($dispensasi->tanggal_izin ?? $dispensasi->created_at)->translatedFormat('l, d F Y') }} (1 hari)</strong>
            @endif
        </div>

        <p style="font-weight: bold; margin: 10px 0 4px 0; font-size: 10.5pt;">Daftar Siswa Peserta Dispensasi:</p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10pt;" border="1" cellpadding="4">
            <thead>
                <tr style="background-color: #f2f2f2; text-align: center;">
                    <th style="width: 25px;">No</th>
                    <th style="width: 80px;">NIS</th>
                    <th>Nama Lengkap</th>
                    <th style="width: 110px;">Kelas / Jurusan</th>
                    <th style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: center;">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</td>
                    <td><strong>{{ $dispensasi->siswa->nama_lengkap }}</strong></td>
                    <td style="text-align: center;">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</td>
                    <td style="text-align: center;">Ketua / Pemohon</td>
                </tr>
                @foreach($dispensasi->anggotaSiswa as $idx => $anggota)
                <tr>
                    <td style="text-align: center;">{{ $idx + 2 }}</td>
                    <td style="text-align: center;">{{ $anggota->user->nis_nip ?? $anggota->nis_nip ?? '-' }}</td>
                    <td>{{ $anggota->nama_lengkap }}</td>
                    <td style="text-align: center;">{{ $anggota->kelas?->nama_kelas ?? '-' }}</td>
                    <td style="text-align: center;">Anggota</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
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
                <td class="label">Tanggal Izin</td>
                <td class="colon">:</td>
                <td><strong>{{ \Carbon\Carbon::parse($dispensasi->tanggal_izin ?? $dispensasi->created_at)->translatedFormat('l, d F Y') }}</strong></td>
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
                <td><strong>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Jam Kembali</td>
                <td class="colon">:</td>
                <td><strong>{{ $dispensasi->sampai_pulang ? 'Sampai Pulang Sekolah (Akhir KBM)' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dispensasi->tanggal_izin?->dayOfWeek ?? $dispensasi->created_at?->dayOfWeek) }}</strong></td>
            </tr>
        </table>
        @endif

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
