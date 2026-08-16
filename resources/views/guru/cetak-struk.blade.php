<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Dispensasi - {{ $dispensasi->nomor_surat }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .actions { text-align: center; margin-bottom: 15px; }
        .btn { display: inline-block; padding: 10px 18px; border: 0; border-radius: 6px; font-size: 14px; cursor: pointer; text-decoration: none; color: #fff; }
        .btn-primary { background: #0d6efd; }
        .btn-secondary { background: #6c757d; }
        .alert { max-width: 420px; margin: 0 auto 15px; padding: 10px 14px; border-radius: 6px; font-size: 14px; }
        .alert-success { background: #d1e7dd; color: #0a7d2c; }
        .alert-error { background: #f8d7da; color: #c0392b; }
        .struk { max-width: 420px; margin: 0 auto; background: #fff; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.15); font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; }
        .text-center { text-align: center; }
        .judul { font-size: 18px; font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .qr-box { text-align: center; margin: 12px 0; }
        .qr-warning { border: 1px dashed #c0392b; color: #c0392b; padding: 10px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="actions">
        <form method="POST" action="{{ route('guru.print-thermal', $dispensasi) }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-primary">🖨️ Cetak Struk Thermal</button>
        </form>
        <a href="{{ route('guru.pengajuan.show', $dispensasi) }}" class="btn btn-secondary">Tutup</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">⚠️ {{ session('error') }}</div>
    @endif

    <div class="struk">
        {{-- Header Sekolah --}}
        <div class="text-center">
            <div class="judul">SMK NEGERI 1 BANGSRI</div>
            <div>SURAT DISPENSASI</div>
            <div>{{ $dispensasi->nomor_surat }}</div>
        </div>

        <div class="divider"></div>

        {{-- Data Siswa --}}
        <div>Nama : {{ $dispensasi->siswa->nama_lengkap }}</div>
        <div>NIS  : {{ $dispensasi->siswa->user->nis_nip ?? '-' }}</div>
        <div>Kelas: {{ $dispensasi->siswa->kelas->nama_kelas }} - {{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</div>

        <div class="divider"></div>

        {{-- Detail Dispensasi --}}
        <div>Kategori: {{ ucfirst(str_replace('_', ' ', $dispensasi->kategori)) }}</div>
        <div>Alasan  : {{ $dispensasi->alasan }}</div>
        <div>Tujuan  : {{ $dispensasi->tujuan }}</div>
        @if($dispensasi->lokasi)
            <div>Lokasi  : {{ $dispensasi->lokasi }}</div>
        @endif
        <div>Keluar  : {{ $dispensasi->jam_keluar }}</div>
        <div>Kembali : {{ $dispensasi->jam_kembali }}</div>

        <div class="divider"></div>

        {{-- QR Code Section --}}
        @if(!empty($dispensasi->qr_code))
            <div class="qr-box">
                <div>QR Code Validasi</div>
                <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" width="120">
                <div>Scan di Pos Satpam</div>
            </div>
        @else
            <div class="qr-warning text-center">
                ⚠️ QR Code Tidak Ditemukan<br>
                Status saat ini: {{ strtoupper($dispensasi->status) }}<br>
                Pastikan Anda sudah mengklik tombol "Setujui & Generate QR".
            </div>
        @endif

        {{-- Tanda Tangan Guru Piket --}}
        <div class="text-center" style="margin-top: 24px;">
            <div>Guru Piket,</div>
            <div style="height: 60px;"></div>
            <div><strong>{{ $dispensasi->guruPiket?->guru?->nama_lengkap ?? '..........................' }}</strong></div>
            @if(!empty($dispensasi->guruPiket?->guru?->nip))
                <div>NIP. {{ $dispensasi->guruPiket->guru->nip }}</div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="text-center">
            <div>Struk ini sah dan ditandatangani secara elektronik</div>
            <div>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
            <div>SMK N 1 Bangsri - Jepara</div>
        </div>
    </div>

</body>
</html>