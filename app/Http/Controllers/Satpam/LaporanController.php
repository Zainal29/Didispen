<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\LaporanSatpam;
use App\Models\Siswa;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function __construct(
        private NotifikasiService $notifikasiService
    ) {}

    /**
     * Tampilkan daftar laporan kejadian/temuan gerbang oleh satpam
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $query = LaporanSatpam::with(['siswa.kelas.jurusan', 'dispensasi', 'petugasTindakLanjut', 'satpam'])
            ->latest();

        if ($status && in_array($status, ['menunggu', 'ditindaklanjuti', 'selesai'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhereHas('user', fn($uq) => $uq->where('nis_nip', 'like', "%{$search}%"));
                })->orWhereHas('dispensasi', function ($dq) use ($search) {
                    $dq->where('nomor_surat', 'like', "%{$search}%");
                })->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $laporan = $query->paginate(15)->withQueryString();

        $stats = [
            'total'           => LaporanSatpam::count(),
            'hari_ini'        => LaporanSatpam::whereDate('created_at', today())->count(),
            'menunggu'        => LaporanSatpam::where('status', 'menunggu')->count(),
            'ditindaklanjuti' => LaporanSatpam::whereIn('status', ['ditindaklanjuti', 'selesai'])->count(),
        ];

        // Ambil daftar siswa yang sedang berstatus 'keluar' hari ini untuk tombol cepat
        $siswaKeluarHariIni = Dispensasi::where('status', 'keluar')
            ->whereDate('created_at', today())
            ->with(['siswa.kelas.jurusan', 'anggotaSiswa.kelas.jurusan'])
            ->get();

        return view('satpam.laporan.index', compact('laporan', 'stats', 'siswaKeluarHariIni'));
    }

    /**
     * Cari siswa secara dinamis (AJAX auto-complete)
     */
    public function searchSiswa(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $siswa = Siswa::with(['kelas.jurusan', 'user'])
            ->where(function ($sub) use ($q) {
                $sub->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('nis_nip', 'like', "%{$q}%"));
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id'           => $s->id,
                    'nama_lengkap' => $s->nama_lengkap,
                    'nis'          => $s->nis_nip ?? $s->user?->nis_nip ?? '-',
                    'kelas'        => $s->kelas?->nama_kelas ?? '-',
                    'jurusan'      => $s->kelas?->jurusan?->nama_jurusan ?? '-',
                ];
            });

        return response()->json($siswa);
    }

    /**
     * Simpan laporan temuan gerbang baru oleh satpam
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'      => 'required|exists:siswa,id',
            'dispensasi_id' => 'nullable|exists:dispensasi,id',
            'kategori'      => 'required|string|in:rombongan_tidak_kembali,terobos_gerbang,terlambat_kembali,pelanggaran_lain',
            'keterangan'    => 'required|string|min:5|max:1000',
        ], [
            'siswa_id.required'   => 'Pilih siswa yang bersangkutan.',
            'kategori.required'   => 'Pilih kategori laporan.',
            'keterangan.required' => 'Tuliskan keterangan kejadian/alasan secara jelas.',
            'keterangan.min'      => 'Keterangan minimal 5 karakter.',
        ]);

        $laporan = LaporanSatpam::create([
            'satpam_id'      => auth()->id(),
            'siswa_id'       => $validated['siswa_id'],
            'dispensasi_id'  => $validated['dispensasi_id'] ?? null,
            'kategori'       => $validated['kategori'],
            'keterangan'     => $validated['keterangan'],
            'status'         => 'menunggu',
        ]);

        $laporan->load(['siswa.kelas', 'dispensasi']);
        $namaSiswa = $laporan->siswa?->nama_lengkap ?? 'Siswa';
        $kelasSiswa = $laporan->siswa?->kelas?->nama_kelas ?? '';
        $kategoriLabel = $laporan->kategori_label;

        // Notifikasi ke seluruh Admin & Guru Piket
        try {
            $notifMsg = "⚠️ Laporan Satpam Baru: {$namaSiswa} ({$kelasSiswa}) — {$kategoriLabel}. Menunggu tindak lanjut.";
            
            $adminUsers = User::whereIn('role', ['admin', 'superadmin'])->get();
            foreach ($adminUsers as $admin) {
                $this->notifikasiService->send($admin->id, $notifMsg, route('admin.laporan-satpam.index'));
            }

            // Jika ada dispensasi terkait, kirimkan juga ke guru pemohon/piket
            if ($laporan->dispensasi?->guru?->user_id) {
                $this->notifikasiService->send($laporan->dispensasi->guru->user_id, $notifMsg, route('guru.laporan-satpam.index'));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi laporan satpam: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan temuan berhasil dikirim ke Admin & Guru Piket.',
                'data'    => $laporan,
            ]);
        }

        return redirect()->route('satpam.laporan.index')
            ->with('success', 'Laporan temuan gerbang berhasil disimpan dan terkirim ke Admin & Guru Piket.');
    }
}
