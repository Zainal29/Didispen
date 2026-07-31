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
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

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
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // Terapkan logika filter yang SAMA PERSIS dengan method index
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
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-dispensasi', compact('dispensasi'));
        return $pdf->download('laporan-dispensasi-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

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

        $filename = 'laporan-dispensasi-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($dispensasi) {
            // BOM untuk memastikan karakter UTF-8 terbaca benar di Excel
            echo "\xEF\xBB\xBF"; 
            
            $file = fopen('php://output', 'w');
            
            // 1. Tulis Header Kolom
            fputcsv($file, [
                'No', 'No. Surat', 'Tanggal', 'NIS', 'Nama Siswa', 
                'Kelas', 'Jurusan', 'Kategori', 'Alasan', 'Tujuan', 
                'Jam Keluar', 'Jam Kembali', 'Status', 'Guru Piket'
            ]);

            // 2. Tulis Data
            $no = 1;
            foreach ($dispensasi as $row) {
                fputcsv($file, [
                    $no++,
                    $row->nomor_surat,
                    $row->created_at->format('d-m-Y H:i'),
                    $row->siswa->user->nis_nip ?? '-',
                    $row->siswa->nama_lengkap,
                    $row->siswa->kelas->nama_kelas ?? '-',
                    $row->siswa->kelas->jurusan->nama_jurusan ?? '-',
                    ucfirst(str_replace('_', ' ', $row->kategori)),
                    $row->alasan,
                    $row->tujuan,
                    
                    // ✅ PERBAIKAN PENTING: 
                    // jam_keluar dan jam_kembali sekarang adalah STRING (VARCHAR), 
                    // jadi TIDAK BOLEH pakai ->format(). Langsung panggil saja.
                    $row->jam_keluar, 
                    $row->jam_kembali,
                    
                    ucfirst($row->status),
                    $row->guruPiket->guru->nama_lengkap ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}