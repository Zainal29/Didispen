<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk - {{ $dispensasi->nomor_surat }}</title>
    <style>
        /* Styling khusus printer thermal 58mm */
        body {
            width: 58mm;
            margin: 0 auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            text-align: center;
        }
        .header {
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        .section {
            text-align: left;
            margin-bottom: 8px;
        }
        .section-title {
            font-weight: bold;
            border-bottom: 1px dashed #000;
            margin-bottom: 4px;
            padding-bottom: 2px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .qr-section {
            text-align: center;
            margin: 10px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
        }
        .qr-section img {
            max-width: 45mm;
            height: auto;
        }
        .footer {
            margin-top: 10px;
            font-size: 9px;
            color: #333;
        }
        .btn-container {
            text-align: center;
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-family: sans-serif;
            font-size: 12px;
        }
        .btn-close {
            background-color: #6b7280;
        }
        @media print {
            .btn-container { display: none; }
            body { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SMK NEGERI 1 BANGSRI</h2>
        <p>SURAT DISPENSASI SISWA</p>
        <p>{{ $dispensasi->nomor_surat }}</p>
    </div>

    <div class="section">
        <div class="row"><span>Nama</span><span>: {{ $dispensasi->siswa->nama_lengkap }}</span></div>
        <div class="row"><span>NIS</span><span>: {{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span></div>
        <div class="row"><span>Kelas</span><span>: {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</span></div>
    </div>

    <div class="section">
        <div class="section-title">DETAIL DISPENSASI</div>
        <div class="row"><span>Kategori</span><span>: {{ ucfirst(str_replace('_', ' ', $dispensasi->kategori)) }}</span></div>
        <div class="row"><span>Tujuan</span><span>: {{ $dispensasi->tujuan }}</span></div>
        @if($dispensasi->lokasi)
            <div class="row"><span>Lokasi</span><span>: {{ $dispensasi->lokasi }}</span></div>
        @endif
        <div class="row"><span>Jam Keluar</span><span>: {{ $dispensasi->jam_keluar }}</span></div>
        <div class="row"><span>Jam Kembali</span><span>: {{ $dispensasi->jam_kembali }}</span></div>
    </div>

    @if(!empty($dispensasi->qr_code))
        <div class="qr-section">
            <p style="margin: 0 0 5px 0; font-weight: bold;">QR CODE VALIDASI</p>
            <img src="{{ public_path('storage/' . $dispensasi->qr_code) }}" alt="QR Code">
            <p style="margin: 5px 0 0 0; font-size: 9px;">Scan di Pos Satpam</p>
        </div>
    @else
        <div class="qr-section">
            <p>QR Code Tidak Ditemukan</p>
            <p>Status: {{ strtoupper($dispensasi->status) }}</p>
        </div>
    @endif

    <div class="section" style="text-align: right; margin-top: 15px;">
        <p>Bangsri, {{ now()->format('d/m/Y') }}</p>
        <p>Guru Piket,</p>
        <br><br>
        <p style="font-weight: bold; text-decoration: underline;">{{ $dispensasi->guru?->nama_lengkap ?? '..........................' }}</p>
        @if(!empty($dispensasi->guru?->nip))
            <p>NIP. {{ $dispensasi->guru?->nip }}</p>
        @endif
    </div>

    <div class="footer">
        <p>Struk ini sah dan ditandatangani secara elektronik.</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</p>
    </div>

    <div class="btn-container">
        <a href="{{ route('guru.cetak-pdf', [$dispensasi, 'format' => 'thermal']) }}" class="btn" target="_blank">
            🖨️ Buka PDF Struk (58mm)
        </a>
        <a href="{{ route('guru.pengajuan.show', $dispensasi) }}" class="btn btn-close">
            ✕ Tutup
        </a>
    </div>

    <script>
        // Auto print saat halaman dimuat (opsional, bisa dihapus jika ingin manual)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
