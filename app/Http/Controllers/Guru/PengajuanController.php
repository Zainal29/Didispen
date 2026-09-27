<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
use App\Services\AuditLogService;
use App\Models\Siswa;
use App\Models\Setting;
use App\Helpers\TimeHelper;
use App\Helpers\DispensasiTimeHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PengajuanController extends Controller
{
    private ?AuditLogService $auditLogService = null;

    public function __construct(
        private NotifikasiService $notifikasiService,
        ?AuditLogService $auditLogService = null
    ) {
        $this->auditLogService = $auditLogService ?? app(AuditLogService::class);
    }

    /**
     * Daftar riwayat pengajuan yang dibuat guru
     */
    public function index(Request $request)
    {
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->paginate(15);

        return view('guru.pengajuan.index', compact('pengajuan'));
    }

    /**
     * Form buat pengajuan manual
     */
    public function create()
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Profil Guru tidak ditemukan. Silakan hubungi Administrator.');
        }

        $settings = [
            'start_time' => Setting::get('dispensasi_start_time', '07:00'),
            'end_time' => Setting::get('dispensasi_end_time', '15:00'),
            'end_time_friday' => Setting::get('dispensasi_end_time_friday', '14:00'),
            'allowed_days' => array_map('intval', explode(',', Setting::get('dispensasi_days', '1,2,3,4,5'))),
        ];

        $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        $maxJam = TimeHelper::getMaxJamPelajaran($dayOfWeek);
        $jadwalPelajaran = TimeHelper::getAllJadwal();
        $jadwalHariIni = TimeHelper::getJadwalHari($dayOfWeek);

        return view('guru.pengajuan.create', compact('settings', 'maxJam', 'jadwalPelajaran', 'jadwalHariIni'));
    }

    /**
     * AJAX Search Siswa untuk Select2
     */
    public function searchSiswa(Request $request)
    {
        $query = trim($request->get('q', ''));

        // Jika query kosong, kembalikan hasil kosong
        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        // Cari siswa berdasarkan NIS atau Nama Siswa
        $siswa = Siswa::with(['user', 'kelas.jurusan'])
            ->where(function($q) use ($query) {
                // Pencarian berdasarkan nama lengkap
                $q->where('nama_lengkap', 'like', "%{$query}%")
                  // ATAU pencarian berdasarkan NIS pada relasi user
                  ->orWhereHas('user', function($userQuery) use ($query) {
                      $userQuery->where('nis_nip', 'like', "%{$query}%");
                  });
            })
            ->limit(20)
            ->get();

        // Format hasil untuk Select2
        $results = $siswa->map(function($item) {
            $kelas = $item->kelas ? $item->kelas->nama_kelas : '-';
            $jurusan = ($item->kelas && $item->kelas->jurusan) ? $item->kelas->jurusan->nama_jurusan : '';
            $kelasLengkap = $jurusan ? "{$kelas} - {$jurusan}" : $kelas;
            $nis = $item->user ? $item->user->nis_nip : '-';

            return [
                'id' => $item->id,
                'text' => "{$item->nama_lengkap} | NIS: {$nis} | {$kelasLengkap}",
                'nama' => $item->nama_lengkap,
                'nis' => $nis,
                'kelas' => $kelasLengkap,
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Simpan pengajuan baru oleh guru
     */
    public function store(Request $request)
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Profil Guru tidak ditemukan. Silakan hubungi Administrator.');
        }

        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();
        if (!$timeCheck['allowed']) {
            return back()->withInput()->with('error', $timeCheck['reason']);
        }

        $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        $maxJam = TimeHelper::getMaxJamPelajaran($dayOfWeek);

        $validated = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'kategori'        => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan'          => 'required|string|min:10|max:1000',
            'tujuan'          => 'required|string|max:255',
            'lokasi'          => 'nullable|string|max:255',
            'jam_keluar'      => "required|integer|min:1|max:{$maxJam}",
            'jam_kembali'     => "required|integer|min:1|max:{$maxJam}|gt:jam_keluar",
            'foto_verifikasi' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'jam_keluar.max' => "Jam keluar maksimal adalah Jam ke-{$maxJam} untuk hari ini (" . now('Asia/Jakarta')->isoFormat('dddd') . ").",
            'jam_kembali.max' => "Jam kembali maksimal adalah Jam ke-{$maxJam} untuk hari ini (" . now('Asia/Jakarta')->isoFormat('dddd') . ").",
            'jam_kembali.gt' => 'Jam kembali harus lebih dari jam keluar.',
            'foto_verifikasi.required' => 'Foto verifikasi siswa wajib diupload.',
            'foto_verifikasi.image'    => 'File harus berupa gambar.',
            'foto_verifikasi.mimes'    => 'Format gambar harus JPEG, PNG, atau JPG.',
            'foto_verifikasi.max'      => 'Ukuran gambar maksimal 2MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_verifikasi')) {
            $foto = $request->file('foto_verifikasi');
            $filename = 'verif_' . time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
            $fotoPath = $foto->storeAs('foto_verifikasi', $filename, 'public');
        }

        $batasWaktuKembali = TimeHelper::getBatasWaktuKembali($validated['jam_kembali'], $dayOfWeek);

        try {
            $dispensasi = DB::transaction(function () use ($validated, $guru, $fotoPath, $batasWaktuKembali) {
                // Cek apakah siswa memiliki dispensasi aktif (menunggu, disetujui, keluar)
                $activeDispensasi = Dispensasi::where('siswa_id', $validated['siswa_id'])
                    ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
                    ->lockForUpdate()
                    ->first();

                if ($activeDispensasi) {
                    $statusText = match ($activeDispensasi->status) {
                        'menunggu' => 'masih menunggu persetujuan',
                        'disetujui' => 'sudah disetujui dan menunggu keluar',
                        'keluar' => 'sedang berlangsung (status: keluar)',
                        default => 'masih aktif',
                    };

                    throw ValidationException::withMessages([
                        'siswa_id' => "Siswa masih memiliki dispensasi aktif yang {$statusText}. Selesaikan atau tolak dispensasi tersebut terlebih dahulu.",
                    ]);
                }

                return Dispensasi::create([
                    'nomor_surat'             => Dispensasi::generateNomorSurat(),
                    'siswa_id'                => $validated['siswa_id'],
                    'guru_id'                 => $guru->id,
                    'kategori'                => $validated['kategori'],
                    'alasan'                  => $validated['alasan'],
                    'tujuan'                  => $validated['tujuan'],
                    'lokasi'                  => $validated['lokasi'] ?? null,
                    'jam_keluar'              => 'Jam Pelajaran ke-' . $validated['jam_keluar'],
                    'jam_kembali'             => 'Jam Pelajaran ke-' . $validated['jam_kembali'],
                    'batas_waktu_kembali'     => $batasWaktuKembali,
                    'status'                  => 'disetujui',
                    'approved_at'             => now(),
                    'rejected_at'             => null,
                    'dibuat_manual_oleh_guru' => true,
                    'qr_token'                => Str::random(64),
                    'foto_verifikasi'         => $fotoPath,
                ]);
            });
        } catch (\Throwable $e) {
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }
            throw $e;
        }

        $this->generateQRCode($dispensasi);

        return redirect()->route('guru.pengajuan.show', $dispensasi)
            ->with('success', 'Dispensasi berhasil dibuat dan langsung disetujui. QR Code telah di-generate.');
    }

    /**
     * Detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    /**
     * Setujui pengajuan dispensasi
     */
    public function approve(Request $request, Dispensasi $dispensasi)
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Profil Guru tidak ditemukan. Silakan hubungi Administrator.');
        }

        $processed = DB::transaction(function () use ($dispensasi, $guru, $request) {
    $locked = Dispensasi::whereKey($dispensasi->id)
        ->lockForUpdate()
        ->first();

    if (! $locked || $locked->status !== 'menunggu') {
        return false;
    }

    $locked->update([
        'status'        => 'disetujui',
        'guru_id'       => $guru->id,
        'approved_at'   => now(),
        'rejected_at'   => null,
        'catatan_admin' => $request->catatan_admin ?? null,
    ]);

    return $locked;
});

        if (! $processed) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $this->generateQRCode($processed);

        // Kirim Notifikasi ke Siswa
        $this->notifikasiService->send(
            $processed->siswa->user_id,
            "Pengajuan dispensasi Anda ({$processed->nomor_surat}) telah DISETUJUI oleh Guru Piket. Silakan tunjukkan QR Code ke Satpam.",
            route('siswa.pengajuan.show', $processed->id)
        );

        // Audit Log
        $this->auditLogService?->log(
            auth()->id(),
            'approve',
            'dispensasi',
            $processed->id,
            ['status' => 'menunggu'],
            ['status' => 'disetujui', 'guru_id' => $guru->id]
        );

        return redirect()->route('guru.pengajuan.index')
            ->with('success', 'Dispensasi berhasil disetujui. QR Code telah di-generate.');
    }

    /**
     * Tolak pengajuan dispensasi
     */
    public function reject(Request $request, Dispensasi $dispensasi)
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Profil Guru tidak ditemukan. Silakan hubungi Administrator.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'required|string|min:5|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            'catatan_admin.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $processed = DB::transaction(function () use ($dispensasi, $guru, $validated) {
            $locked = Dispensasi::whereKey($dispensasi->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'menunggu') {
                return false;
            }

            // 1. Ambil path foto_verifikasi sebelum field dikosongkan
            $fotoPath = $locked->foto_verifikasi;

            // 2. Update status ke ditolak, isi rejected_at, kosongkan foto_verifikasi
            $locked->update([
                'status'          => 'ditolak',
                'guru_id'         => $guru->id,
                'rejected_at'     => now(),
                'approved_at'     => null,
                'catatan_admin'   => $validated['catatan_admin'],
                'foto_verifikasi' => null,
            ]);

            // 3. Hapus file fisik dari storage public secara aman (tidak membuat transaksi gagal)
            if (! empty($fotoPath)) {
                try {
                    if (Storage::disk('public')->exists($fotoPath)) {
                        Storage::disk('public')->delete($fotoPath);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Gagal menghapus file foto_verifikasi {$fotoPath} saat reject: " . $e->getMessage());
                }
            }

            return $locked;
        });

        if (! $processed) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        // Kirim Notifikasi ke Siswa
        $alasan = $validated['catatan_admin'];
        $this->notifikasiService->send(
            $processed->siswa->user_id,
            "Pengajuan dispensasi Anda ({$processed->nomor_surat}) DITOLAK oleh Guru Piket. Alasan: {$alasan}",
            route('siswa.pengajuan.show', $processed->id)
        );

        // Audit Log
        $this->auditLogService?->log(
            auth()->id(),
            'reject',
            'dispensasi',
            $processed->id,
            ['status' => 'menunggu'],
            ['status' => 'ditolak', 'guru_id' => $guru->id, 'catatan_admin' => $validated['catatan_admin']]
        );

        return redirect()->route('guru.pengajuan.index')
            ->with('success', 'Dispensasi berhasil ditolak.');
    }

    /**
     * Helper: Generate QR Code untuk dispensasi
     */
    private function generateQRCode(Dispensasi $dispensasi)
    {
        if (empty($dispensasi->qr_token)) {
            $dispensasi->qr_token = \Illuminate\Support\Str::random(64);
        }

        $qrContent = json_encode(['token' => $dispensasi->qr_token]);

        $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qr_codes');

        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->margin(0)
            ->generate($qrContent, storage_path('app/public/' . $qrCodePath));

        $dispensasi->update(['qr_code' => $qrCodePath]);
    }
}
