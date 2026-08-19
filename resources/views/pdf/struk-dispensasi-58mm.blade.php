<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Dispensasi {{ $dispensasi->nomor_surat }}</title>
    <style>
        @page {
            margin: 4px 6px;
        }
        body {
            font-family: 'Courier New', Courier, monospace, Arial;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .title-main {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .title-sub {
            font-size: 9.5pt;
            font-weight: bold;
            margin-top: 1px;
        }
        .nomor-surat {
            font-size: 8.5pt;
            margin-top: 1px;
        }

        .divider {
            border-top: 1px dashed #000000;
            margin: 5px 0;
        }

        .item-row {
            margin-bottom: 2px;
            word-wrap: break-word;
        }

        .qr-section {
            text-align: center;
            margin: 8px 0;
        }
        .qr-img {
            width: 110px;
            height: 110px;
            margin: 4px auto;
            display: block;
        }

        .ttd-section {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .ttd-space {
            height: 35px;
        }

        .footer {
            text-align: center;
            font-size: 7.5pt;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    {{-- HEADER DISPENSASI --}}
    <div class="header">
        <div class="title-main">SMK N 1 BANGSRI</div>
        <div class="title-sub">SURAT DISPENSASI</div>
        <div class="nomor-surat">{{ $dispensasi->nomor_surat }}</div>
    </div>

    <div class="divider"></div>

    {{-- DATA SISWA --}}
    <div class="item-row"><span class="bold">Nama :</span> {{ $dispensasi->siswa->nama_lengkap }}</div>
    <div class="item-row"><span class="bold">NIS  :</span> {{ $dispensasi->siswa->user->nis_nip ?? '-' }}</div>
    <div class="item-row"><span class="bold">Kelas:</span> {{ $dispensasi->siswa->kelas->nama_kelas }} - {{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</div>

    <div class="divider"></div>

    {{-- DETAIL PERIZINAN --}}
    <div class="item-row"><span class="bold">Kategori:</span> {{ ucfirst(str_replace('_', ' ', $dispensasi->kategori)) }}</div>
    <div class="item-row"><span class="bold">Alasan  :</span> {{ $dispensasi->alasan }}</div>
    <div class="item-row"><span class="bold">Tujuan  :</span> {{ $dispensasi->tujuan }}</div>
    @if(!empty($dispensasi->lokasi))
        <div class="item-row"><span class="bold">Lokasi  :</span> {{ $dispensasi->lokasi }}</div>
    @endif
    <div class="item-row"><span class="bold">Keluar  :</span> {{ $dispensasi->jam_keluar }}</div>
    <div class="item-row"><span class="bold">Kembali :</span> {{ $dispensasi->jam_kembali }}</div>

    <div class="divider"></div>

    {{-- QR CODE SECTION --}}
    <div class="qr-section">
        <div class="bold">[ QR CODE VALIDASI ]</div>
        @if(!empty($qrBase64))
            <img src="{{ $qrBase64 }}" class="qr-img" alt="QR Code">
        @else
            <div style="margin: 8px 0; border: 1px dashed #000; padding: 4px; font-size: 7.5pt;">
                NO: {{ $dispensasi->nomor_surat }}
            </div>
        @endif
        <div style="font-size: 7.5pt;">Scan di Pos Satpam</div>
    </div>

    <div class="divider"></div>

    {{-- TANDA TANGAN GURU PIKET --}}
    <div class="ttd-section">
        <div>Guru Piket,</div>
        <div class="ttd-space"></div>
        <div class="bold"><u>{{ $dispensasi->guruPiket?->guru?->nama_lengkap ?? '..........................' }}</u></div>
        @if(!empty($dispensasi->guruPiket?->guru?->nip))
            <div style="font-size: 7.5pt;">NIP. {{ $dispensasi->guruPiket->guru->nip }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <div>Struk sah dan ditandatangani elektronik</div>
        <div>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
        <div>SMK N 1 Bangsri - Jepara</div>
    </div>

</body>
</html>
