<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
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
            ->whereHas('guruPiket', function($q) use ($guru) {
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
            ->whereHas('guruPiket', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });

        // Terapkan filter yang sama dengan method index
        if ($request->filled('tanggal_dari')) $query->whereDate('created_at', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $dispensasi = $query->latest()->get();

        // Pastikan nama view PDF sesuai dengan yang Anda buat
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-dispensasi', compact('dispensasi', 'guru'));
        
        return $pdf->download('Laporan_Dispensasi_Guru_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Laporan ke Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $guru = auth()->user()->guru;
        
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->whereHas('guruPiket', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });

        // Terapkan filter yang sama dengan method index
        if ($request->filled('tanggal_dari')) $query->whereDate('created_at', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        if ($request->filled('status')) $query->where('status', $request->status);

        $dispensasi = $query->latest()->get();

        $filename = 'Laporan_Dispensasi_Guru_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($dispensasi) {
            echo "\xEF\xBB\xBF"; // BOM untuk memastikan karakter UTF-8 terbaca benar di Excel
            $file = fopen('php://output', 'w');
            
            // Header Kolom
            fputcsv($file, [
                'No', 'No. Surat', 'Tanggal', 'Nama Siswa', 'Kelas', 
                'Jam Keluar', 'Jam Kembali', 'Status', 'Tanda Tangan Digital'
            ]);

            $no = 1;
            foreach ($dispensasi as $row) {
                // Cek apakah guru piket punya tanda tangan digital
                $hasSignature = ($row->guruPiket && $row->guruPiket->guru && $row->guruPiket->guru->digital_signature) 
                    ? 'Ada (Valid)' 
                    : 'Belum Diupload';

                fputcsv($file, [
                    $no++,
                    $row->nomor_surat,
                    $row->created_at->format('d-m-Y'),
                    $row->siswa->nama_lengkap,
                    $row->siswa->kelas->nama_kelas ?? '-',
                    $row->jam_keluar,      // Langsung string, tanpa ->format()
                    $row->jam_kembali,     // Langsung string, tanpa ->format()
                    ucfirst($row->status),
                    $hasSignature
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}