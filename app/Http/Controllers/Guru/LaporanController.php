<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan dispensasi untuk Guru
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;

        // Query dasar: hanya dispensasi yang diproses oleh guru yang login
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->whereHas('guruPiket', function ($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });

        // Fitur Filter
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ambil data dengan pagination
        $dispensasi = $query->latest()->paginate(15);

        // Hitung statistik (gunakan clone query agar filter tetap berlaku)
        $stats = [
            'total' => (clone $query)->count(),
            'disetujui' => (clone $query)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $query)->where('status', 'ditolak')->count(),
            'selesai' => (clone $query)->where('status', 'selesai')->count(),
        ];

        return view('guru.laporan.index', compact('dispensasi', 'stats'));
    }

    /**
     * Export Laporan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $guru = auth()->user()->guru;

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->whereHas('guruPiket', function ($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });

        // Terapkan filter yang sama dengan method index
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dispensasi = $query->latest()->get();

        // Menggunakan DomPDF untuk generate tampilan cetak yang rapi
        $pdf = Pdf::loadView('pdf.laporan-dispensasi', compact('dispensasi', 'guru'))
            ->setPaper('a4', 'landscape'); // Menggunakan format Landscape agar tabel muat

        return $pdf->download('Laporan_Dispensasi_Guru_'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export Laporan ke Excel (.xls) Native
     */
    public function exportExcel(Request $request)
    {
        $guru = auth()->user()->guru;

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->whereHas('guruPiket', function ($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });

        // Terapkan filter
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dispensasi = $query->latest()->get();

        // Ekstensi diubah menjadi .xls
        $filename = 'Laporan_Dispensasi_Guru_'.now()->format('Y-m-d').'.xls';

        // Header khusus untuk memicu download format Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Format data menggunakan struktur tabel HTML agar dibaca rapi oleh Excel
        $callback = function () use ($dispensasi) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<thead style="background-color: #f2f2f2;">';
            echo '<tr>';
            echo '<th><b>No</b></th>';
            echo '<th><b>No. Surat</b></th>';
            echo '<th><b>Tanggal Pengajuan</b></th>';
            echo '<th><b>Kategori</b></th>';
            echo '<th><b>Nama Siswa</b></th>';
            echo '<th><b>Kelas</b></th>';
            echo '<th><b>Alasan</b></th>';
            echo '<th><b>Jam Keluar</b></th>';
            echo '<th><b>Jam Kembali</b></th>';
            echo '<th><b>Status</b></th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $no = 1;
            foreach ($dispensasi as $row) {
                echo '<tr>';
                echo '<td>'.$no++.'</td>';
                echo '<td>'.$row->nomor_surat.'</td>';
                echo '<td>'.$row->created_at->format('d/m/Y H:i').'</td>';
                echo '<td>'.str_replace('_', ' ', strtoupper($row->kategori)).'</td>';
                echo '<td>'.($row->siswa->nama_lengkap ?? '-').'</td>';
                echo '<td>'.($row->siswa->kelas->nama_kelas ?? '-').'</td>';
                echo '<td>'.$row->alasan.'</td>';
                echo '<td>'.$row->jam_keluar.'</td>';
                echo '<td>'.$row->jam_kembali.'</td>';
                echo '<td>'.strtoupper($row->status).'</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
