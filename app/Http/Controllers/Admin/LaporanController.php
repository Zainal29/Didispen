<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query dasar dengan eager loading
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        // 1. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Filter Tanggal Dari
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        // 3. Filter Tanggal Sampai
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // 4. Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }

        // 5. Filter Jurusan
        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas.jurusan', function ($q) use ($request) {
                $q->where('id', $request->jurusan_id);
            });
        }

        // Gunakan paginate agar halaman tidak berat jika data banyak
        $dispensasi = $query->latest()->paginate(15);

        return view('admin.laporan.index', compact('dispensasi'));
    }

    public function exportPdf(Request $request)
    {
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('tanggal_dari')) $query->whereDate('created_at', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }
        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas.jurusan', function ($q) use ($request) {
                $q->where('id', $request->jurusan_id);
            });
        }

        // Batasi data agar DomPDF tidak kehabisan memori
        $dispensasi = $query->latest()->limit(500)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-dispensasi', compact('dispensasi'))
            ->setPaper('a4', 'landscape'); // Landscape agar tabel muat

        return $pdf->download('Laporan_Dispensasi_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        // Terapkan logika filter yang SAMA PERSIS
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('tanggal_dari')) $query->whereDate('created_at', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }
        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas.jurusan', function ($q) use ($request) {
                $q->where('id', $request->jurusan_id);
            });
        }

        $dispensasi = $query->latest()->get();

        // ✅ UBAH EKSTENSI MENJADI .xls
        $filename = 'Laporan_Dispensasi_' . now()->format('Y-m-d') . '.xls';

        // ✅ HEADER KHUSUS AGAR DIBACA SEBAGAI EXCEL NATIVE
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function() use ($dispensasi) {
            // 1. Deklarasi XML Excel agar dikenali sebagai file Excel asli
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '<style>
                table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11px; }
                th, td { border: 1px solid #000000; padding: 6px; text-align: left; }
                th { background-color: #d9d9d9; font-weight: bold; text-align: center; }
                .text-center { text-align: center; }
            </style>';
            echo '</head><body>';

            echo '<table>';
            echo '<thead><tr>';
            echo '<th width="3%">No</th>';
            echo '<th width="10%">No. Surat</th>';
            echo '<th width="8%">Tanggal</th>';
            echo '<th width="8%">NIS</th>';
            echo '<th width="15%">Nama Siswa</th>';
            echo '<th width="8%">Kelas</th>';
            echo '<th width="10%">Jurusan</th>';
            echo '<th width="8%">Kategori</th>';
            echo '<th width="15%">Alasan</th>';
            echo '<th width="15%">Tujuan</th>';
            echo '<th width="8%">Jam Keluar</th>';
            echo '<th width="8%">Jam Kembali</th>';
            echo '<th width="8%">Status</th>';
            echo '<th width="10%">Guru Piket</th>';
            echo '</tr></thead><tbody>';

            $no = 1;
            foreach ($dispensasi as $row) {
                echo '<tr>';
                echo '<td class="text-center">' . $no++ . '</td>';
                echo '<td>' . htmlspecialchars($row->nomor_surat) . '</td>';
                echo '<td class="text-center">' . $row->created_at->format('d-m-Y') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->user->nis_nip ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row->siswa->nama_lengkap) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->kelas->nama_kelas ?? '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->kelas->jurusan->nama_jurusan ?? '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $row->kategori))) . '</td>';
                echo '<td>' . htmlspecialchars($row->alasan) . '</td>';
                echo '<td>' . htmlspecialchars($row->tujuan) . '</td>';

                // Jam keluar & kembali (String)
                echo '<td class="text-center">' . htmlspecialchars($row->jam_keluar) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->jam_kembali) . '</td>';

                echo '<td class="text-center">' . htmlspecialchars(ucfirst($row->status)) . '</td>';
                echo '<td>' . htmlspecialchars($row->guru->nama_lengkap ?? '-') . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
