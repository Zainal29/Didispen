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
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $arsipFilter = $request->get('arsip', 'aktif');

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru.user', 'anggotaSiswa'])
            ->withCount('anggotaSiswa');

        if ($arsipFilter === 'arsip') {
            $query->onlyTrashed();
        } elseif ($arsipFilter === 'semua') {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

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

        $dispensasi = $query->latest()->paginate(15)->withQueryString();
        $kelas = Kelas::all();
        $jurusans = Jurusan::all();

        // Hitung total untuk badge tab
        $countAktif = Dispensasi::withoutTrashed()->count();
        $countArsip = Dispensasi::onlyTrashed()->count();
        $countSemua = Dispensasi::withTrashed()->count();

        return view('admin.dispensasi.index', compact(
            'dispensasi',
            'kelas',
            'jurusans',
            'arsipFilter',
            'countAktif',
            'countArsip',
            'countSemua'
        ));
    }

    public function show($id)
    {
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $dispensasi = Dispensasi::withTrashed()
            ->with(['siswa.user', 'siswa.kelas.jurusan', 'guru.user', 'anggotaSiswa.kelas.jurusan', 'anggotaSiswa.user'])
            ->findOrFail($id);

        return view('admin.dispensasi.show', compact('dispensasi'));
    }

    /**
     * Soft delete data dispensasi (pindahkan ke arsip)
     */
    public function destroy(Dispensasi $dispensasi)
    {
        try {
            $nomorSurat = $dispensasi->nomor_surat;
            $dispensasi->delete(); // Soft delete, file tetap disimpan

            return redirect()->back()
                ->with('success', "Dispensasi #{$nomorSurat} berhasil dipindahkan ke arsip.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengarsipkan dispensasi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengarsipkan data: ' . $e->getMessage());
        }
    }

    /**
     * Pulihkan data dispensasi dari arsip
     */
    public function restore($id)
    {
        try {
            $dispensasi = Dispensasi::onlyTrashed()->findOrFail($id);
            $dispensasi->restore();

            return redirect()->back()
                ->with('success', "Dispensasi #{$dispensasi->nomor_surat} berhasil dipulihkan.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal memulihkan dispensasi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memulihkan data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus permanen data dispensasi beserta file storage terkait
     */
    public function forceDelete($id)
    {
        try {
            $dispensasi = Dispensasi::withTrashed()->findOrFail($id);
            $nomorSurat = $dispensasi->nomor_surat;

            // forceDelete() akan otomatis memicu booted deleted event yang menghapus file karena isForceDeleting() bernilai true
            $dispensasi->forceDelete();

            return redirect()->back()
                ->with('success', "Dispensasi #{$nomorSurat} berhasil dihapus permanen beserta filenya.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menghapus permanen dispensasi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data permanen: ' . $e->getMessage());
        }
    }
}
