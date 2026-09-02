<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ✅ Tambahkan ini
use Illuminate\Support\Facades\Log;      // ✅ Tambahkan ini

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
     * ✅ BARU: Hapus data dispensasi beserta file terkait
     */
     /**
      * Hapus data dispensasi beserta file terkait
      */
     public function destroy(Dispensasi $dispensasi)
     {
         try {
             // 1. Hapus file QR Code jika ada
             if ($dispensasi->qr_code && Storage::disk('public')->exists($dispensasi->qr_code)) {
                 Storage::disk('public')->delete($dispensasi->qr_code);
             }

             // 2. Hapus file foto verifikasi jika ada
             if ($dispensasi->foto_verifikasi && Storage::disk('public')->exists($dispensasi->foto_verifikasi)) {
                 Storage::disk('public')->delete($dispensasi->foto_verifikasi);
             }

             // 3. Hapus data dari database
             $dispensasi->delete();

             // ✅ PERBAIKAN: Redirect ke route yang benar
             return redirect()->route('admin.semua.pengajuan')
                 ->with('success', 'Data dispensasi berhasil dihapus secara permanen.');

         } catch (\Exception $e) {
             Log::error('Gagal menghapus dispensasi: ' . $e->getMessage());

             // ✅ PERBAIKAN: Redirect ke route yang benar juga di error
             return redirect()->route('admin.semua.pengajuan')
                 ->with('error', 'Data berhasil dihapus, tetapi terjadi kesalahan: ' . $e->getMessage());
         }
     }
}
