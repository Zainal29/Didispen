<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class DispensasiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai query dasar dengan eager loading
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru.user']);

        // 2. PENGGANTI ->filter() agar tidak error
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

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

        // 3. Eksekusi query
        $dispensasi = $query->latest()->paginate(15);

        // 4. Ambil data untuk dropdown filter
        $kelas = Kelas::all();
        $jurusans = Jurusan::all();

        return view('admin.dispensasi.index', compact('dispensasi', 'kelas', 'jurusans'));
    }

    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru.user']);
        return view('admin.dispensasi.show', compact('dispensasi'));
    }
}