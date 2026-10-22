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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\GuruPiketService;

class PengajuanController extends Controller
{
    private ?AuditLogService $auditLogService = null;

    public function __construct(
        private NotifikasiService $notifikasiService,
        ?AuditLogService $auditLogService = null,
        private ?GuruPiketService $guruPiketService = null
    ) {
        $this->auditLogService = $auditLogService ?? app(AuditLogService::class);
        $this->guruPiketService = $guruPiketService ?? app(GuruPiketService::class);
    }

    /**
     * Daftar riwayat pengajuan yang dibuat guru
     */
    public function index(Request $request)
    {
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru', 'anggotaSiswa'])
            ->withCount('anggotaSiswa')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->paginate(15);
        $currentGuru = auth()->user()?->guru;
        $isSedangBertugasAktif = $currentGuru ? $this->guruPiketService->isGuruBertugasSaatIni($currentGuru) : false;

        return view('guru.pengajuan.index', compact('pengajuan', 'isSedangBertugasAktif'));
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

        $authCheck = $this->guruPiketService->canGuruCreateManualDispensasi($guru);
        $isSedangBertugasAktif = $authCheck['allowed'];
        $kunciReason = $isSedangBertugasAktif ? null : ($authCheck['reason'] ?? 'Pembuatan dispensasi manual hanya dapat dilakukan oleh guru piket yang sedang bertugas.');

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
        $istirahatHariIni = TimeHelper::getIstirahatHari($dayOfWeek);
        $semuaSlotHariIni = TimeHelper::getSemuaSlotHari($dayOfWeek);

        return view('guru.pengajuan.create', compact(
            'settings', 'maxJam', 'jadwalPelajaran', 'jadwalHariIni', 'istirahatHariIni', 'semuaSlotHariIni',
            'isSedangBertugasAktif', 'kunciReason'
        ));
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

        // Cari siswa aktif berdasarkan NIS, nama, atau kelas
        $siswa = Siswa::with(['user', 'kelas.jurusan'])
            ->where('status_aktif', true)
            ->where(function($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                  ->orWhere('nis_nip', 'like', "%{$query}%")
                  ->orWhereHas('user', function($userQuery) use ($query) {
                      $userQuery->where('nis_nip', 'like', "%{$query}%");
                  })
                  ->orWhereHas('kelas', function($kelasQuery) use ($query) {
                      $kelasQuery->where('nama_kelas', 'like', "%{$query}%");
                  });
            })
            ->limit(20)
            ->get();

        // Format hasil untuk Select2
        $results = $siswa->map(function($item) {
            $kelas = $item->kelas ? $item->kelas->nama_kelas : '-';
            $jurusan = ($item->kelas && $item->kelas->jurusan) ? $item->kelas->jurusan->nama_jurusan : '-';
            $kelasLengkap = $jurusan !== '-' ? "{$kelas} - {$jurusan}" : $kelas;
            $nis = $item->nis_nip ?? ($item->user ? $item->user->nis_nip : '-');

            return [
                'id' => $item->id,
                'text' => "{$item->nama_lengkap} | NIS: {$nis} | {$kelasLengkap}",
                'nama' => $item->nama_lengkap,
                'nis' => $nis,
                'kelas' => $kelasLengkap,
                'jurusan' => $jurusan,
                'no_telepon' => $item->no_telepon ?? '',
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Simpan pengajuan baru oleh guru
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $guru = $user?->guru;

        if (! $guru) {
            abort(403, 'Profil Guru tidak ditemukan. Silakan hubungi Administrator.');
        }

        $authCheck = $this->guruPiketService->canGuruCreateManualDispensasi($guru);
        if (! $authCheck['allowed']) {
            return redirect()->route('guru.dashboard')
                ->with('error', $authCheck['reason'] ?? 'Pembuatan dispensasi manual hanya dapat dilakukan oleh guru piket yang sedang bertugas.');
        }

        /*
         * Waktu dispensasi menggunakan timezone Asia/Jakarta.
         * Jangan menggunakan timezone server secara langsung.
         */
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();

        if (! $timeCheck['allowed']) {
            return back()
                ->withInput()
                ->with('error', $timeCheck['reason'] ?? 'Pengajuan dispensasi tidak dapat dilakukan saat ini.');
        }

        $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        $maxJam = TimeHelper::getMaxJamPelajaran($dayOfWeek);
        $isSampaiPulang = $request->boolean('sampai_pulang');

        $validated = $request->validate([
            'siswa_id'        => [
                'required',
                Rule::exists('siswa', 'id')->where(function ($query) {
                    $query->where('status_aktif', true);
                }),
            ],
            'kategori'        => ['required', 'in:sakit,izin,keperluan_sekolah,lainnya'],
            'alasan'          => ['required', 'string', 'min:10', 'max:500'],
            'tujuan'          => ['required', 'string', 'max:255'],
            'lokasi'          => ['nullable', 'string', 'max:255'],
            'no_telepon'      => ['nullable', 'string', 'regex:/^(?:\+?620?|0)?8[0-9]{8,12}$/'],
            'jam_keluar'      => ['required'],
            'jam_kembali'     => [$isSampaiPulang ? 'nullable' : 'required'],
            'foto_verifikasi' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'siswa_id.exists'          => 'Siswa yang dipilih tidak ditemukan atau sudah tidak aktif.',
            'alasan.min'               => 'Alasan dispensasi minimal 10 karakter.',
            'alasan.max'               => 'Alasan dispensasi maksimal 500 karakter.',
            'no_telepon.regex'         => 'Format nomor WhatsApp tidak valid.',
            'foto_verifikasi.image'    => 'File harus berupa gambar.',
            'foto_verifikasi.mimes'    => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'foto_verifikasi.max'      => 'Ukuran gambar maksimal 5MB.',
        ]);

        $rawJamKeluar = (string) $request->input('jam_keluar');
        $rawJamKembali = $isSampaiPulang ? 'pulang' : (string) $request->input('jam_kembali');

        $semuaSlotHariIni = TimeHelper::getSemuaSlotHari($dayOfWeek);
        $scheduleEarliest = ! empty($semuaSlotHariIni) ? $semuaSlotHariIni[0]['start'] : '07:00';
        $scheduleLatest = ! empty($semuaSlotHariIni) ? end($semuaSlotHariIni)['end'] : '15:15';
        [$startH, $startM] = array_map('intval', explode(':', $scheduleEarliest));
        [$endH, $endM] = array_map('intval', explode(':', $scheduleLatest));

        $storedJamKeluar = null;
        $storedJamKembali = null;
        $batasWaktuKembali = null;

        if ($isSampaiPulang) {
            // Mode Izin Sampai Pulang Sekolah
            if (is_numeric($rawJamKeluar)) {
                $jamKeluar = (int) $rawJamKeluar;
                if ($jamKeluar < 1 || $jamKeluar > $maxJam) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar maksimal adalah Jam ke-{$maxJam} untuk hari ini (" .
                            now('Asia/Jakarta')->isoFormat('dddd') . ').',
                    ]);
                }
                $storedJamKeluar = 'Jam Pelajaran ke-' . $jamKeluar;
            } elseif (preg_match('/^\d{2}:\d{2}$/', $rawJamKeluar)) {
                $nowWib = now('Asia/Jakarta');
                $currentMinutes = ($nowWib->hour * 60) + $nowWib->minute;
                [$jamKeluarHour, $jamKeluarMinute] = array_map('intval', explode(':', $rawJamKeluar));
                $jamKeluarMinutes = ($jamKeluarHour * 60) + $jamKeluarMinute;

                if ($jamKeluarMinutes < $currentMinutes) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => 'Jam keluar tidak boleh menggunakan waktu yang sudah lewat.',
                    ]);
                }

                $waktuKeluarValid = false;
                foreach ($semuaSlotHariIni as $slot) {
                    if (empty($slot['start']) || empty($slot['end'])) {
                        continue;
                    }
                    [$slotStartH, $slotStartM] = array_map('intval', explode(':', substr($slot['start'], 0, 5)));
                    [$slotEndH, $slotEndM] = array_map('intval', explode(':', substr($slot['end'], 0, 5)));
                    $slotStart = ($slotStartH * 60) + $slotStartM;
                    $slotEnd = ($slotEndH * 60) + $slotEndM;
                    if ($jamKeluarMinutes >= $slotStart && $jamKeluarMinutes <= $slotEnd) {
                        $waktuKeluarValid = true;
                        break;
                    }
                }

                if (! $waktuKeluarValid) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar ({$rawJamKeluar} WIB) harus berada dalam jadwal sekolah yang tersedia hari ini ({$scheduleEarliest} - {$scheduleLatest} WIB).",
                    ]);
                }

                $storedJamKeluar = $rawJamKeluar;
            } else {
                throw ValidationException::withMessages([
                    'jam_keluar' => 'Format jam keluar tidak valid.',
                ]);
            }

            $storedJamKembali = 'Sampai Pulang Sekolah';
            $batasWaktuKembali = TimeHelper::getWaktuSelesaiKbmTerakhir($dayOfWeek)
                ?? now('Asia/Jakarta')->setTime($endH, $endM);
        } else {
            // Mode Normal (Wajib Jam Kembali)
            if (is_numeric($rawJamKeluar) && is_numeric($rawJamKembali)) {
                $jamKeluar = (int) $rawJamKeluar;
                $jamKembali = (int) $rawJamKembali;

                if ($jamKeluar < 1 || $jamKeluar > $maxJam) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar maksimal adalah Jam ke-{$maxJam} untuk hari ini (" .
                            now('Asia/Jakarta')->isoFormat('dddd') . ').',
                    ]);
                }

                if ($jamKembali < 1 || $jamKembali > $maxJam) {
                    throw ValidationException::withMessages([
                        'jam_kembali' => "Jam kembali maksimal adalah Jam ke-{$maxJam} untuk hari ini (" .
                            now('Asia/Jakarta')->isoFormat('dddd') . ').',
                    ]);
                }

                if ($jamKembali <= $jamKeluar) {
                    throw ValidationException::withMessages([
                        'jam_kembali' => 'Jam kembali harus lebih dari jam keluar.',
                    ]);
                }

                $storedJamKeluar = 'Jam Pelajaran ke-' . $jamKeluar;
                $storedJamKembali = 'Jam Pelajaran ke-' . $jamKembali;
                $batasWaktuKembali = TimeHelper::getBatasWaktuKembali($jamKembali, $dayOfWeek);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $rawJamKeluar) && preg_match('/^\d{2}:\d{2}$/', $rawJamKembali)) {
                $nowWib = now('Asia/Jakarta');
                $currentMinutes = ($nowWib->hour * 60) + $nowWib->minute;

                [$jamKeluarHour, $jamKeluarMinute] = array_map('intval', explode(':', $rawJamKeluar));
                [$jamKembaliHour, $jamKembaliMinute] = array_map('intval', explode(':', $rawJamKembali));

                $jamKeluarMinutes = ($jamKeluarHour * 60) + $jamKeluarMinute;
                $jamKembaliMinutes = ($jamKembaliHour * 60) + $jamKembaliMinute;

                if ($jamKeluarMinutes < $currentMinutes) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => 'Jam keluar tidak boleh menggunakan waktu yang sudah lewat.',
                    ]);
                }

                if ($jamKembaliMinutes <= $jamKeluarMinutes) {
                    throw ValidationException::withMessages([
                        'jam_kembali' => 'Jam kembali harus lebih besar dari jam keluar.',
                    ]);
                }

                $waktuKeluarValid = false;
                $waktuKembaliValid = false;

                foreach ($semuaSlotHariIni as $slot) {
                    if (empty($slot['start']) || empty($slot['end'])) {
                        continue;
                    }

                    [$startHour, $startMinute] = array_map('intval', explode(':', substr($slot['start'], 0, 5)));
                    [$endHour, $endMinute] = array_map('intval', explode(':', substr($slot['end'], 0, 5)));

                    $slotStart = ($startHour * 60) + $startMinute;
                    $slotEnd = ($endHour * 60) + $endMinute;

                    if ($jamKeluarMinutes >= $slotStart && $jamKeluarMinutes <= $slotEnd) {
                        $waktuKeluarValid = true;
                    }

                    if ($jamKembaliMinutes >= $slotStart && $jamKembaliMinutes <= $slotEnd) {
                        $waktuKembaliValid = true;
                    }
                }

                if (! $waktuKeluarValid) {
                    throw ValidationException::withMessages([
                        'jam_keluar' => 'Jam keluar harus berada dalam jadwal sekolah yang tersedia hari ini.',
                    ]);
                }

                if (! $waktuKembaliValid) {
                    throw ValidationException::withMessages([
                        'jam_kembali' => 'Jam kembali harus berada dalam jadwal sekolah yang tersedia hari ini.',
                    ]);
                }

                $storedJamKeluar = $rawJamKeluar;
                $storedJamKembali = $rawJamKembali;
                $batasWaktuKembali = now('Asia/Jakarta')->setTime($jamKembaliHour, $jamKembaliMinute);
            } else {
                throw ValidationException::withMessages([
                    'jam_keluar' => 'Format jam keluar tidak valid.',
                    'jam_kembali' => 'Format jam kembali tidak valid.',
                ]);
            }
        }

        $fotoPath = null;

        try {
            /*
             * Simpan foto verifikasi jika diunggah.
             */
            if ($request->hasFile('foto_verifikasi')) {
                $foto = $request->file('foto_verifikasi');

                $filename = 'verif_' .
                    now('Asia/Jakarta')->format('YmdHis') .
                    '_' .
                    Str::random(10) .
                    '.' .
                    $foto->getClientOriginalExtension();

                $fotoPath = $foto->storeAs(
                    'foto_verifikasi',
                    $filename,
                    'public'
                );
            }

            if (! empty($validated['no_telepon'])) {
                $normalizedPhone = preg_replace('/[^0-9]/', '', $validated['no_telepon']);
                if (str_starts_with($normalizedPhone, '0')) {
                    $normalizedPhone = '62' . substr($normalizedPhone, 1);
                } elseif (str_starts_with($normalizedPhone, '8')) {
                    $normalizedPhone = '62' . $normalizedPhone;
                }
                Siswa::where('id', $validated['siswa_id'])->update(['no_telepon' => $normalizedPhone]);
            }

            $dispensasi = DB::transaction(function () use (
                $validated,
                $guru,
                $fotoPath,
                $batasWaktuKembali,
                $storedJamKeluar,
                $storedJamKembali,
                $isSampaiPulang
            ) {
                /*
                 * Satu siswa tidak boleh memiliki dispensasi aktif
                 * lebih dari satu pada waktu yang sama.
                 */
                $activeDispensasi = Dispensasi::where(
                        'siswa_id',
                        $validated['siswa_id']
                    )
                    ->whereIn('status', [
                        'menunggu',
                        'disetujui',
                        'keluar',
                    ])
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
                        'siswa_id' =>
                            "Siswa masih memiliki dispensasi aktif yang {$statusText}. " .
                            "Selesaikan atau tolak dispensasi tersebut terlebih dahulu.",
                    ]);
                }

                return Dispensasi::create([
                    'nomor_surat'             => Dispensasi::generateNomorSurat(),
                    'siswa_id'                => $validated['siswa_id'],
                    'guru_id'                 => $guru->id,
                    'tipe_pengajuan'          => 'individu',
                    'kategori'                => $validated['kategori'],
                    'alasan'                  => $validated['alasan'],
                    'tujuan'                  => $validated['tujuan'],
                    'lokasi'                  => $validated['lokasi'] ?? null,
                    'tanggal_izin'            => now('Asia/Jakarta')->toDateString(),
                    'tanggal_selesai'         => now('Asia/Jakarta')->toDateString(),
                    'durasi_hari'             => 1,
                    'sampai_pulang'           => $isSampaiPulang,
                    'jam_keluar'              => $storedJamKeluar,
                    'jam_kembali'             => $storedJamKembali,
                    'batas_waktu_kembali'     => $batasWaktuKembali,
                    'status'                  => 'disetujui',
                    'approved_at'             => now('Asia/Jakarta'),
                    'rejected_at'             => null,
                    'dibuat_manual_oleh_guru' => true,
                    'qr_token'                => Str::random(64),
                    'foto_verifikasi'         => $fotoPath,
                ]);
            });

        /*
         * Generate QR setelah transaksi berhasil.
         */
        $this->generateQRCode($dispensasi);

        return redirect()
            ->route('guru.pengajuan.show', $dispensasi)
            ->with(
                'success',
                'Dispensasi berhasil dibuat dan langsung disetujui. QR Code telah di-generate.'
            );

    } catch (ValidationException $e) {
        /*
         * Validasi bisnis seperti siswa masih memiliki
         * dispensasi aktif harus dikembalikan ke form.
         */
        if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
            Storage::disk('public')->delete($fotoPath);
        }

        throw $e;

    } catch (\Throwable $e) {
        /*
         * Kalau database/storage gagal, foto jangan ditinggalkan.
         */
        if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
            Storage::disk('public')->delete($fotoPath);
        }

        Log::error('Gagal membuat dispensasi manual oleh guru', [
            'user_id' => auth()->id(),
            'guru_id' => $guru->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'Pengajuan dispensasi gagal dibuat. Silakan coba lagi.');
    }
}

    /**
     * Detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();

        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru', 'anggotaSiswa.kelas.jurusan', 'anggotaSiswa.user']);
        $currentGuru = auth()->user()?->guru;
        $isSedangBertugasAktif = $currentGuru ? $this->guruPiketService->isGuruBertugasSaatIni($currentGuru) : false;

        return view('guru.pengajuan.show', compact('dispensasi', 'isSedangBertugasAktif'));
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

        $authCheck = $this->guruPiketService->canGuruApproveDispensasi($guru, $dispensasi);
        if (! $authCheck['allowed']) {
            return back()->with('error', $authCheck['reason'] ?? 'Anda tidak memiliki wewenang untuk menyetujui pengajuan ini.');
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

        // Kirim Notifikasi ke Siswa Utama
        if ($processed->siswa?->user_id) {
            $this->notifikasiService->send(
                $processed->siswa->user_id,
                "Pengajuan dispensasi Anda ({$processed->nomor_surat}) telah DISETUJUI oleh Guru Piket. Silakan tunjukkan QR Code ke Satpam.",
                route('siswa.pengajuan.show', $processed->id)
            );
        }

        // Kirim Notifikasi ke Seluruh Anggota Rombongan jika Organisasi
        $processed->loadMissing('anggotaSiswa');
        foreach ($processed->anggotaSiswa as $anggota) {
            if ($anggota->user_id) {
                try {
                    $this->notifikasiService->send(
                        $anggota->user_id,
                        "Dispensasi Rombongan ({$processed->nomor_surat}) telah DISETUJUI oleh Guru Piket.",
                        route('siswa.pengajuan.show', $processed->id)
                    );
                } catch (\Throwable $e) {}
            }
        }

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

        $authCheck = $this->guruPiketService->canGuruApproveDispensasi($guru, $dispensasi);
        if (! $authCheck['allowed']) {
            return back()->with('error', $authCheck['reason'] ?? 'Anda tidak memiliki wewenang untuk menolak pengajuan ini.');
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

            // Hapus file fisik foto verifikasi jika ada
            if ($fotoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($fotoPath);
            }

            // 2. Update status ke ditolak, isi rejected_at, kosongkan foto_verifikasi
            $locked->update([
                'status'          => 'ditolak',
                'guru_id'         => $guru->id,
                'rejected_at'     => now(),
                'catatan_admin'   => $validated['catatan_admin'],
                'foto_verifikasi' => null,
            ]);

            // 3. Hapus file fisik lainnya (QR Code jika ada)
            \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($locked);

            return $locked;
        });

        if (! $processed) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        // Kirim Notifikasi ke Siswa
        $alasan = $validated['catatan_admin'];
        $statusLabel = $processed->status === 'dibatalkan' ? 'DIBATALKAN' : 'DITOLAK';
        $this->notifikasiService->send(
            $processed->siswa->user_id,
            "Pengajuan dispensasi Anda ({$processed->nomor_surat}) {$statusLabel} oleh Guru Piket. Alasan: {$alasan}",
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
