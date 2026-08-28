<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; //

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        $stats = [
            'total' => Dispensasi::whereDate('created_at', $today)->count(),
            'menunggu_keluar' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
        ];

        $menungguKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $siswaKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $selesai = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $dihubungi = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('is_warned', true)
            ->whereDate('warned_at', today())
            ->latest('warned_at')
            ->limit(30)
            ->get();

        return view('satpam.dashboard', compact('stats', 'menungguKeluar', 'siswaKeluar', 'selesai', 'dihubungi'));
    }

    /**
     * ✅ PENCARIAN MANUAL DISPENSASI (Untuk Verifikasi Satpam)
     */
     /**
     /**
      * Search dispensasi untuk verifikasi manual
      */
     public function searchDispensasi(Request $request)
     {
         try {
             $request->validate([
                 'query' => 'required|string|min:2|max:255'
             ]);

             $query = $request->input('query');

             $dispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas'])
                 ->whereDate('created_at', now()->toDateString())
                 ->where(function($q) use ($query) {
                     $q->where('nomor_surat', 'like', "%{$query}%")
                       ->orWhereHas('siswa', function($q2) use ($query) {
                           $q2->where('nama_lengkap', 'like', "%{$query}%")
                              ->orWhereHas('user', function($q3) use ($query) {
                                  $q3->where('nis_nip', 'like', "%{$query}%");
                              });
                       });
                 })
                 ->latest()
                 ->limit(5)
                 ->get();

             if ($dispensasi->isEmpty()) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Dispensasi tidak ditemukan'
                 ], 404);
             }

             $results = $dispensasi->map(function($d) {
                 return [
                     'id' => $d->id,
                     'nomor_surat' => $d->nomor_surat,
                     'status' => $d->status,
                     'siswa_nama' => $d->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                     'siswa_nis' => $d->siswa?->user?->nis_nip ?? '-',
                     'siswa_kelas' => $d->siswa?->kelas?->nama_kelas ?? '-',
                     'jam_keluar' => $d->jam_keluar,
                     'jam_kembali' => $d->jam_kembali,
                 ];
             });

             return response()->json([
                 'success' => true,
                 'data' => $results
             ]);

         } catch (\Exception $e) {
             \Log::error('Search Dispensasi Error: ' . $e->getMessage());
             \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());

             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ], 500);
         }
     }

    /**
     * Konfirmasi keluar (Mendukung AJAX & Form)
     */
    public function konfirmasiKeluar(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'disetujui') {
            $message = 'Dispensasi harus dalam status disetujui untuk dikonfirmasi keluar.';
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message])
                : redirect()->back()->with('error', $message);
        }

        $dispensasi->update([
            'status' => 'keluar',
            'waktu_keluar_aktual' => now(),
            'satpam_keluar_id' => auth()->id(),
        ]);

        $message = "Siswa {$dispensasi->siswa->nama_lengkap} berhasil dikonfirmasi KELUAR.";

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->back()->with('success', $message);
    }

    /**
     * Konfirmasi kembali (Mendukung AJAX & Form)
     */
    public function konfirmasiKembali(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'keluar') {
            $message = 'Dispensasi harus dalam status keluar untuk dikonfirmasi kembali.';
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message])
                : redirect()->back()->with('error', $message);
        }

        // Hapus foto verifikasi jika ada
        if ($dispensasi->foto_verifikasi) {
            Storage::disk('public')->delete($dispensasi->foto_verifikasi);
        }

        $dispensasi->update([
            'status' => 'selesai',
            'waktu_kembali_aktual' => now(),
            'satpam_kembali_id' => auth()->id(),
            'foto_verifikasi' => null,
        ]);

        app(NotifikasiService::class)->send(
            $dispensasi->siswa->user_id,
            "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI. Terima kasih sudah kembali ke sekolah.",
            route('siswa.pengajuan.show', $dispensasi, false)
        );

        $message = "Siswa {$dispensasi->siswa->nama_lengkap} berhasil dikonfirmasi KEMBALI.";

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->back()->with('success', $message);
    }

    public function showDetail(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.kelas.jurusan', 'guru']);
        return view('satpam.dispensasi-detail', compact('dispensasi'));
    }

    public function markWaContacted(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'keluar') {
            return response()->json(['success' => false, 'message' => 'Dispensasi tidak valid']);
        }

        $dispensasi->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
