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
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .data-table th, .data-table td { 
            border: 1px solid #ccc; 
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: top; 
        }
        .data-table th { 
            background-color: #f3f4f6; 
            font-weight: bold; 
            text-align: center; 
        }
        .text-center { text-align: center; }
        .capitalize { text-transform: capitalize; }
        
        /* Layout Footer Tanda Tangan */
        .footer-table {
            width: 100%;
            margin-top: 40px;
            font-size: 11px;
            page-break-inside: avoid; /* Mencegah terpotong antar halaman */
        }
        .footer-table td {
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <h2>Laporan Dispensasi Siswa</h2>
    <p class="sub-header">Dicetak oleh: {{ $guru->nama_lengkap ?? 'Administrator' }} | Tanggal: {{ now()->format('d F Y, H:i') }} WIB</p>
    
    <table class="data-table">
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
                <td class="text-center capitalize">{{ $d->status }}</td>
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

    {{-- BAGIAN TANDA TANGAN MANUAL (Rapi di Kanan Bawah) --}}
    <table class="footer-table" border="0">
        <tr>
            <td width="65%"></td> <!-- Spacer Kosong di Kiri -->
            <td width="35%">
                Jepara, {{ now()->format('d F Y') }}<br>
                Mengetahui,<br>
                Guru Piket<br>
                <br><br><br><br><br> <!-- Space untuk tanda tangan pulpen -->
                <strong><u>{{ $guru->nama_lengkap ?? '________________________' }}</u></strong><br>
                @if(!empty($guru->nip)) NIP. {{ $guru->nip }} @endif
            </td>
        </tr>
    </table>
</body>
</html>