<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Surat Dispensasi {{ $dispensasi->nomor_surat }}</title>
<style>
    body { font-family: 'Times New Roman', Arial, sans-serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 40px; }
    .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 25px; }
    .header h2 { margin: 0; font-size: 16pt; text-transform: uppercase; }
    .header p { margin: 3px 0; font-size: 11pt; }
    .title { text-align: center; margin: 25px 0; font-weight: bold; text-decoration: underline; font-size: 14pt; }
    .content { line-height: 1.6; }
    .table-data { width: 100%; margin: 15px 0; border-collapse: collapse; }
    .table-data td { padding: 4px 8px; vertical-align: top; }
    .table-data td:first-child { width: 160px; font-weight: bold; }
    .signature { margin-top: 60px; text-align: right; page-break-inside: avoid; }
    .signature img { max-width: 150px; max-height: 80px; margin-bottom: 5px; }
    .signature-name { font-weight: bold; text-decoration: underline; margin-top: 60px; display: block; }
    .footer { font-size: 9pt; color: #555; margin-top: 50px; border-top: 1px dashed #999; padding-top: 8px; text-align: center; }
</style>
</head>
<body>
    <div class="header">
        <h2>SEKOLAH MENENGAH KEJURUAN</h2>
        <p>Jl. Pendidikan No. 1, Kota | Telp. (021) 1234567</p>
        <p>Email: info@smk.sch.id | Website: www.smk.sch.id</p>
    </div>

    <div class="title">
        SURAT DISPENSASI<br>
        <span style="font-size: 12pt; font-weight: normal; text-decoration: none;">Nomor: {{ $dispensasi->nomor_surat }}</span>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Guru Piket, menerangkan bahwa:</p>

        <table class="table-data">
            <tr><td>Nama Siswa</td><td>: {{ $dispensasi->siswa->nama_lengkap }}</td></tr>
            <tr><td>NIS / NISN</td><td>: {{ $dispensasi->siswa->user->nis_nip ?? '-' }}</td></tr>
            <tr><td>Kelas / Jurusan</td><td>: {{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }} ({{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }})</td></tr>
            <tr><td>Kategori Izin</td><td>: {{ ucfirst(str_replace('_', ' ', $dispensasi->kategori)) }}</td></tr>
            <tr><td>Alasan</td><td>: {{ $dispensasi->alasan }}</td></tr>
            <tr><td>Tujuan</td><td>: {{ $dispensasi->tujuan }}</td></tr>
            <tr><td>Lokasi</td><td>: {{ $dispensasi->lokasi ?? '-' }}</td></tr>
            <tr><td>Jam Keluar</td><td>: <strong>{{ $dispensasi->jam_keluar }}</strong></td></tr>
            <tr><td>Jam Kembali</td><td>: <strong>{{ $dispensasi->jam_kembali }}</strong></td></tr>
        </table>

        @if($dispensasi->catatan_admin)
        <p style="margin-top: 15px;"><strong>Catatan Guru:</strong> <em>"{{ $dispensasi->catatan_admin }}"</em></p>
        @endif

        <p style="margin-top: 20px;">Demikian surat dispensasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <p>Jakarta, {{ now()->format('d F Y') }}<br>Guru Piket</p>
        
        @if(isset($dispensasi->guruPiket) && $dispensasi->guruPiket->guru && $dispensasi->guruPiket->guru->digital_signature)
            <img src="{{ public_path('storage/' . $dispensasi->guruPiket->guru->digital_signature) }}" alt="Tanda Tangan Digital">
        @else
            <div style="height: 60px;"></div>
        @endif
        
        <span class="signature-name">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? 'Nama Guru Piket' }}</span>
        <span>NIP. {{ $dispensasi->guruPiket->guru->nip ?? '-' }}</span>
    </div>

    <div class="footer">
        Dokumen ini dicetak secara otomatis oleh sistem. | 
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>
</body>
</html>