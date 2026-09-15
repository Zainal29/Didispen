<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <i class="fas fa-check-circle"></i> Tambahkan ini
use Illuminate\Support\Facades\Log;      // <i class="fas fa-check-circle"></i> Tambahkan ini

class DispensasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru.user']);

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

        $dispensasi = $query->latest()->paginate(15);
        $kelas = Kelas::all();
        $jurusans = Jurusan::all();

        return view('admin.dispensasi.index', compact('dispensasi', 'kelas', 'jurusans'));
    }

    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru.user']);
        return view('admin.dispensasi.show', compact('dispensasi'));
    }

    /**
     * <i class="fas fa-check-circle"></i> BARU: Hapus data dispensasi beserta file terkait
     */
     /**
      * Hapus data dispensasi beserta file terkait
      */
      public function destroy(Dispensasi $dispensasi)
      {
          try {
              \Illuminate\Support\Facades\DB::transaction(function () use ($dispensasi) {
                  $files = array_filter([
                      $dispensasi->qr_code,
                      $dispensasi->foto_verifikasi,
                      $dispensasi->foto_bukti,
                  ]);

                  foreach ($files as $file) {
                      if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                          \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                      }
                  }
                  $dispensasi->delete();
              });

              return redirect()->route('admin.semua.pengajuan')
                  ->with('success', 'Data dispensasi berhasil dihapus secara permanen.');
          } catch (\Exception $e) {
              \Illuminate\Support\Facades\Log::error('Gagal menghapus dispensasi: ' . $e->getMessage());
              return redirect()->route('admin.semua.pengajuan')
                  ->with('error', 'Terjadi kesalahan saat menghapus data.');
          }
      }
}
