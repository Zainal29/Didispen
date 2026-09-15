<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Dispensasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
        }
        h2 {
            text-align: center;
            text-transform: uppercase;
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .sub-header {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9pt;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>LAPORAN DISPENSASI SISWA</h2>
    <p class="sub-header">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Surat</th>
                <th width="10%">Tanggal</th>
                <th width="8%">NIS</th>
                <th width="15%">Nama</th>
                <th width="8%">Kelas</th>
                <th width="10%">Kategori</th>
                <th width="12%">Tujuan</th>
                <th width="10%">Jam Keluar</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensasi as $i => $d)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $d->nomor_surat }}</td>
                <td class="text-center">{{ $d->created_at->format('d/m/Y') }}</td>
                <td class="text-center">{{ $d->siswa->user->nis_nip ?? '-' }}</td>
                <td>{{ $d->siswa->nama_lengkap }}</td>
                <td class="text-center">{{ $d->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td class="text-center">{{ ucfirst(str_replace('_', ' ', $d->kategori)) }}</td>
                <td>{{ Str::limit($d->tujuan, 20) }}</td>
                <td class="text-center">{{ $d->jam_keluar }}</td>
                <td class="text-center">{{ ucfirst($d->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:20px; font-size:8pt; text-align:right;">
        Total: {{ $dispensasi->count() }} record
    </p>
</body>
</html>
