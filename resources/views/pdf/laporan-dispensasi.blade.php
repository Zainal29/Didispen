<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $statusLabel ?? 'Laporan Dispensasi Siswa' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .meta {
            font-size: 8.5pt;
            color: #475569;
        }
        .badge-info {
            display: inline-block;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th, td {
            border: 0.5pt solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: "Courier New", Courier, monospace; }
        .font-bold { font-weight: bold; }
        
        .status-pill {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
        }
        .status-early { background-color: #ecfdf5; color: #047857; border: 0.5pt solid #a7f3d0; }
        .status-late { background-color: #fef2f2; color: #b91c1c; border: 0.5pt solid #fecaca; }
        .status-out { background-color: #f0f9ff; color: #0369a1; border: 0.5pt solid #bae6fd; }
        .status-home { background-color: #f5f3ff; color: #6d28d9; border: 0.5pt solid #ddd6fe; }
        .status-default { background-color: #f1f5f9; color: #334155; border: 0.5pt solid #cbd5e1; }

        .footer {
            margin-top: 15px;
            font-size: 8pt;
            color: #64748b;
        }
        .footer-table {
            width: 100%;
            border: none;
            margin-top: 20px;
        }
        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- KOP LAPORAN --}}
    <div class="header">
        <h1>Laporan Rekapitulasi Dispensasi Siswa</h1>
        <div class="meta">
            <span class="badge-info">Filter: {{ $statusLabel ?? 'Semua Realisasi Siswa Keluar' }}</span>
            &nbsp;•&nbsp;
            @if(request('tanggal_dari') || request('tanggal_sampai'))
                <span>Periode: <strong>{{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d/m/Y') : 'Awal' }}</strong> s/d <strong>{{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d/m/Y') : 'Sekarang' }}</strong></span>
                &nbsp;•&nbsp;
            @endif
            <span>Dicetak pada: <strong>{{ now()->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</strong></span>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="11%">No. Surat</th>
                <th width="8%">Tanggal</th>
                <th width="8%">NIS</th>
                <th width="15%">Nama Siswa</th>
                <th width="8%">Kelas</th>
                <th width="9%">Kategori</th>
                <th width="13%">Tujuan Dispensasi</th>
                <th width="8%">Scan Keluar</th>
                <th width="8%">Scan Kembali</th>
                <th width="14%">Status Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensasi as $i => $d)
                @php
                    $badge = $d->status_badge;
                    $scanKeluar = $d->waktu_keluar_aktual
                        ? \Carbon\Carbon::parse($d->waktu_keluar_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                        : '-';
                    $scanKembali = $d->waktu_kembali_aktual
                        ? \Carbon\Carbon::parse($d->waktu_kembali_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                        : ($d->sampai_pulang ? 'Sampai Pulang' : '-');

                    $statusClass = 'status-default';
                    if ($d->status === 'keluar') {
                        $statusClass = $d->isOverdue() ? 'status-late' : 'status-out';
                    } elseif ($d->isNotReturned() || $d->isReturnedLate()) {
                        $statusClass = 'status-late';
                    } elseif ($d->isReturnedEarly()) {
                        $statusClass = 'status-early';
                    } elseif ($d->sampai_pulang) {
                        $statusClass = 'status-home';
                    }
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $i + 1 }}</td>
                    <td class="font-mono font-bold">{{ $d->nomor_surat }}</td>
                    <td class="text-center">{{ $d->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-center font-mono">{{ $d->siswa?->user?->nis_nip ?? '-' }}</td>
                    <td class="font-bold">{{ $d->siswa?->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">{{ $d->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                    <td class="text-center">{{ ucfirst(str_replace('_', ' ', $d->kategori)) }}</td>
                    <td>{{ Str::limit($d->tujuan, 35) }}</td>
                    <td class="text-center font-mono font-bold">{{ $scanKeluar }}</td>
                    <td class="text-center font-mono font-bold">{{ $scanKembali }}</td>
                    <td class="text-center">
                        <span class="status-pill {{ $statusClass }}">
                            {{ $badge['text'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data dispensasi yang sesuai dengan kriteria filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER / RINGKASAN --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    <p style="margin: 0;">Total Berkas: <strong>{{ $dispensasi->count() }} Data Dispensasi</strong></p>
                    <p style="margin: 3px 0 0 0; font-size: 7.5pt; color: #94a3b8;">Sistem Informasi Manajemen Dispensasi Sekolah (DIDISPEN)</p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <p style="margin: 0;">Dicetak oleh: <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong></p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
