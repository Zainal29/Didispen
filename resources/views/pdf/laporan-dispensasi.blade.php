<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Dispensasi Siswa</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            color: #333; 
            margin: 20px; 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 5px; 
            text-transform: uppercase; 
            font-size: 16px;
        }
        .sub-header { 
            text-align: center; 
            margin-bottom: 20px; 
            font-size: 10px; 
            color: #666; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: top; 
        }
        th { 
            background-color: #f3f4f6; 
            font-weight: bold; 
            text-align: center; 
        }
        .text-center { text-align: center; }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 11px;
            page-break-inside: avoid; /* Mencegah tanda tangan terpotong antar halaman */
        }
        .signature-box {
            margin-top: 10px;
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        .signature-img {
            max-height: 60px;
            max-width: 150px;
            margin-bottom: 5px;
        }
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Laporan Dispensasi Siswa</h2>
    <p class="sub-header">Dicetak oleh: {{ $guru->nama_lengkap ?? 'Administrator' }} | Tanggal: {{ now()->format('d F Y, H:i') }} WIB</p>
    
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">No. Surat</th>
                <th width="9%">Tanggal</th>
                <th width="15%">Nama Siswa</th>
                <th width="8%">Kelas</th>
                <th width="12%">Jam Keluar</th>
                <th width="12%">Jam Kembali</th>
                <th width="15%">Tujuan</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensasi as $i => $d)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $d->nomor_surat }}</td>
                <td class="text-center">{{ $d->created_at->format('d/m/Y') }}</td>
                <td>{{ $d->siswa->nama_lengkap }}</td>
                <td class="text-center">{{ $d->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td class="text-center">{{ $d->jam_keluar ?? '-' }}</td>
                <td class="text-center">{{ $d->jam_kembali ?? '-' }}</td>
                <td>{{ $d->tujuan }}</td>
                <td class="text-center" style="text-transform: capitalize;">{{ $d->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #666;">
                    Tidak ada data dispensasi untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- BAGIAN TANDA TANGAN DIGITAL --}}
    <div class="footer">
        <p>Mengetahui,</p>
        <p>Guru Piket / Administrator</p>
        
        @if(isset($guru) && $guru->digital_signature)
            {{-- ✅ INI KUNCINYA: Gunakan public_path() agar DomPDF bisa membaca gambar --}}
            <img src="{{ public_path('storage/' . $guru->digital_signature) }}" class="signature-img" alt="Tanda Tangan Digital">
            <div class="signature-line">
                {{ $guru->nama_lengkap }}<br>
                <small style="font-weight: normal;">NIP. {{ $guru->nip ?? '-' }}</small>
            </div>
        @else
            <div class="signature-line" style="margin-top: 50px;">
                {{ $guru->nama_lengkap ?? '________________' }}<br>
                <small style="font-weight: normal;">NIP. {{ $guru->nip ?? '-' }}</small>
            </div>
            <p style="font-size: 9px; color: red; margin-top: 5px;">* Tanda tangan digital belum diupload</p>
        @endif
    </div>
</body>
</html>