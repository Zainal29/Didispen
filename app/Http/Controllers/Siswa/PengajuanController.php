<?php

namespace App\Http\Controllers\Siswa;

    use App\Helpers\DispensasiTimeHelper;
    use App\Helpers\TimeHelper;
    use App\Http\Controllers\Controller;
    use App\Models\Dispensasi;
    use App\Models\Setting;
    use App\Services\AuditLogService;
    use App\Services\DispensasiService;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    class PengajuanController extends Controller
    {
        public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        // Jalankan auto-expire otomatis
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $baseQuery = Dispensasi::where(function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id)
              ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
                  $a->where('siswa.id', $siswa->id);
              });
        });

        $totalTerlambatSiswa = (clone $baseQuery)->terlambat()->count();
        $totalSemuaPengajuan = (clone $baseQuery)->count();

        $query = Dispensasi::with(['guru', 'siswa.kelas.jurusan', 'anggotaSiswa.kelas.jurusan'])
            ->where(function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id)
                  ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
                      $a->where('siswa.id', $siswa->id);
                  });
            });

        if ($request->filled('status')) {
            if ($request->status === 'terlambat') {
                $query->terlambat();
            } else {
                $query->where('status', $request->status);
            }
        }

        $pengajuan = $query->latest()->paginate(15)->withQueryString();

        return view('siswa.pengajuan.index', compact(
            'pengajuan',
            'totalTerlambatSiswa',
            'totalSemuaPengajuan'
        ));
    }

    /**
     * API pencarian siswa teman untuk anggota dispensasi organisasi
     */
    public function cariSiswa(Request $request)
    {
        $siswa = auth()->user()->siswa;
        $query = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');

        // Jika tidak ada kelas_id dan query kurang dari 2 karakter, return kosong
        if (empty($kelasId) && strlen($query) < 2) {
            return response()->json([]);
        }

        $results = \App\Models\Siswa::with(['kelas.jurusan', 'user'])
            ->where('status_aktif', true)
            ->when($siswa, fn($q) => $q->where('id', '!=', $siswa->id))
            ->when(!empty($kelasId), fn($q) => $q->where('kelas_id', $kelasId))
            ->when(strlen($query) >= 1, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nama_lengkap', 'like', "%{$query}%")
                      ->orWhere('nis_nip', 'like', "%{$query}%")
                      ->orWhereHas('user', fn($u) => $u->where('nis_nip', 'like', "%{$query}%"))
                      ->orWhereHas('kelas', fn($k) => $k->where('nama_kelas', 'like', "%{$query}%"));
                });
            })
            ->orderBy('nama_lengkap')
            ->limit(100)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'nis' => $s->nis_nip ?? $s->user?->nis_nip ?? '-',
                    'kelas' => $s->kelas?->nama_kelas ?? '-',
                    'jurusan' => $s->kelas?->jurusan?->nama_jurusan ?? '-',
                ];
            });

        return response()->json($results);
    }

    /**
     * API pencarian 71 guru pembina ekstra/organisasi untuk dispensasi rombongan
     */
    public function cariGuru(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        $results = \App\Models\Guru::where('status_aktif', true)
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nama_lengkap', 'like', "%{$query}%")
                        ->orWhere('nip', 'like', "%{$query}%")
                        ->orWhere('mata_pelajaran', 'like', "%{$query}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nip', 'mata_pelajaran'])
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'nama' => $g->nama_lengkap,
                    'nip' => $g->nip ?? '-',
                    'mata_pelajaran' => $g->mata_pelajaran ?? '-',
                ];
            });

        return response()->json($results);
    }

    public function create()
    {
        $siswa = auth()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $siswa->load(['kelas.jurusan', 'user']);

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

        // Hydrate data anggota rombongan dari input sebelumnya jika terjadi validasi error
        $oldAnggotaIds = (array) old('anggota_ids', []);
        $oldAnggotaList = [];
        if (! empty($oldAnggotaIds)) {
            $oldAnggotaList = \App\Models\Siswa::with(['kelas.jurusan', 'user'])
                ->whereIn('id', $oldAnggotaIds)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'nis' => $s->nis_nip ?? $s->user?->nis_nip ?? '-',
                    'kelas' => $s->kelas?->nama_kelas ?? '-',
                    'jurusan' => $s->kelas?->jurusan?->nama_jurusan ?? '-',
                ])->values();
        }

        // Daftar kelas untuk filter cepat anggota rombongan
        $daftarKelas = \App\Models\Kelas::with('jurusan')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat', 'jurusan_id']);

        return view('siswa.pengajuan.create', compact(
            'siswa', 'settings', 'maxJam', 'jadwalPelajaran', 'jadwalHariIni',
            'istirahatHariIni', 'semuaSlotHariIni', 'oldAnggotaList', 'daftarKelas'
        ));
    }

    public function store(Request $request)
    {
        $siswa = auth()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $tipePengajuan = $request->input('tipe_pengajuan', 'individu');
        if (! in_array($tipePengajuan, ['individu', 'rombongan', 'organisasi'], true)) {
            $tipePengajuan = 'individu';
        }
        if ($tipePengajuan === 'organisasi') {
            $tipePengajuan = 'rombongan';
        }

        $isSampaiPulang = $request->boolean('sampai_pulang');

        $validated = $request->validate([
            'tipe_pengajuan' => 'nullable|in:individu,rombongan,organisasi',
            'tanggal_izin' => 'nullable|date',
            'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan' => 'required|string|min:10|max:500',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'no_telepon' => [
                'required',
                'string',
                'regex:/^(?:\+?620?|0)?8[0-9]{8,12}$/',
            ],
            'jam_keluar' => 'required',
            'jam_kembali' => $isSampaiPulang ? 'nullable' : 'required',
            'foto_verifikasi' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'anggota_ids' => $tipePengajuan === 'rombongan' ? 'required|array|min:1' : 'nullable|array',
            'anggota_ids.*' => 'exists:siswa,id',
        ], [
            'alasan.min' => 'Alasan minimal 10 karakter agar lebih jelas.',
            'alasan.max' => 'Alasan maksimal 500 karakter.',
            'jam_keluar.required' => 'Jam keluar wajib diisi.',
            'jam_kembali.required' => 'Jam kembali wajib diisi jika bukan izin sampai pulang.',
            'no_telepon.regex' => 'Format nomor WhatsApp tidak valid.',
            'foto_verifikasi.required' => 'Foto verifikasi selfie wajib diambil menggunakan kamera.',
            'foto_verifikasi.max' => 'Ukuran foto verifikasi maksimal 5MB (5120 KB).',
            'anggota_ids.required' => 'Pilih minimal 1 siswa anggota rombongan.',
            'anggota_ids.min' => 'Pilih minimal 1 siswa anggota rombongan.',
        ]);

        // Resolusi Tanggal Izin (1 hari)
        $targetDate = $request->filled('tanggal_izin')
            ? Carbon::parse($request->tanggal_izin, 'Asia/Jakarta')->startOfDay()
            : now('Asia/Jakarta')->startOfDay();

        $today = now('Asia/Jakarta')->startOfDay();

        if ($targetDate->lt($today)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_izin' => 'Tanggal izin tidak boleh menggunakan tanggal yang sudah lewat.',
            ]);
        }

        $endOfMonth = now('Asia/Jakarta')->endOfMonth()->endOfDay();
        if ($targetDate->gt($endOfMonth)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_izin' => 'Pengajuan dispensasi hanya dapat dilakukan untuk bulan berjalan (maksimal ' . $endOfMonth->isoFormat('D MMMM Y') . ').',
            ]);
        }

        $targetDayOfWeek = $targetDate->dayOfWeek;
        $allowedDays = array_map('intval', explode(',', Setting::get('dispensasi_days', '1,2,3,4,5')));

        if (! in_array($targetDayOfWeek, $allowedDays, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tanggal_izin' => 'Hari yang dipilih (' . $targetDate->isoFormat('dddd') . ') bukan hari operasional sekolah yang diizinkan.',
            ]);
        }

        // Cek jam operasional pengajuan hanya jika mengajukan untuk hari ini
        if ($targetDate->equalTo($today)) {
            $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();
            if (! $timeCheck['allowed']) {
                return redirect()->route('siswa.pengajuan.index')
                    ->with('error', 'Pengajuan ditolak: ' . $timeCheck['reason']);
            }
        }

        $maxJam = TimeHelper::getMaxJamPelajaran($targetDayOfWeek);
        $semuaSlotHari = TimeHelper::getSemuaSlotHari($targetDayOfWeek);
        $scheduleEarliest = ! empty($semuaSlotHari) ? $semuaSlotHari[0]['start'] : '07:00';
        $scheduleLatest = ! empty($semuaSlotHari) ? end($semuaSlotHari)['end'] : '15:15';
        [$startH, $startM] = array_map('intval', explode(':', $scheduleEarliest));
        [$endH, $endM] = array_map('intval', explode(':', $scheduleLatest));
        $scheduleStartMin = ($startH * 60) + $startM;
        $scheduleEndMin = ($endH * 60) + $endM;

        $rawJamKeluar = trim((string) $validated['jam_keluar']);
        $rawJamKembali = $isSampaiPulang ? 'pulang' : trim((string) $validated['jam_kembali']);

        $storedJamKeluar = null;
        $storedJamKembali = null;
        $batasWaktu = null;

        if ($isSampaiPulang) {
            // Logika Sampai Pulang Sekolah
            if (is_numeric($rawJamKeluar)) {
                $jamKeluarInt = (int) $rawJamKeluar;
                if ($jamKeluarInt < 1 || $jamKeluarInt > $maxJam) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar maksimal adalah Jam ke-{$maxJam}.",
                    ]);
                }
                $storedJamKeluar = 'Jam Pelajaran ke-' . $jamKeluarInt;
            } elseif (preg_match('/^\d{2}:\d{2}$/', $rawJamKeluar)) {
                [$jamKeluarHour, $jamKeluarMinute] = array_map('intval', explode(':', $rawJamKeluar));
                $jamKeluarMinutes = ($jamKeluarHour * 60) + $jamKeluarMinute;

                if ($jamKeluarMinutes < $scheduleStartMin || $jamKeluarMinutes > $scheduleEndMin) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar ({$rawJamKeluar} WIB) berada di luar jadwal KBM sekolah ({$scheduleEarliest} - {$scheduleLatest} WIB).",
                    ]);
                }

                if ($targetDate->equalTo($today)) {
                    $nowWib = now('Asia/Jakarta');
                    $currentMinutes = ($nowWib->hour * 60) + $nowWib->minute;
                    if ($jamKeluarMinutes < $currentMinutes) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'jam_keluar' => 'Jam keluar tidak boleh menggunakan waktu yang sudah lewat.',
                        ]);
                    }
                }
                $storedJamKeluar = $rawJamKeluar;
            } else {
                $storedJamKeluar = $rawJamKeluar;
            }

            $storedJamKembali = 'Sampai Pulang Sekolah';
            $batasWaktu = TimeHelper::getWaktuSelesaiKbmTerakhir($targetDayOfWeek)
                ?? $targetDate->copy()->setTime($endH, $endM);
        } else {
            // Logika Jam Kembali Biasa
            if (is_numeric($rawJamKeluar) && is_numeric($rawJamKembali)) {
                $jamKeluarInt = (int) $rawJamKeluar;
                $jamKembaliInt = (int) $rawJamKembali;

                if ($jamKeluarInt < 1 || $jamKeluarInt > $maxJam) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar maksimal adalah Jam ke-{$maxJam}.",
                    ]);
                }

                if ($jamKembaliInt < 1 || $jamKembaliInt > $maxJam) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_kembali' => "Jam kembali maksimal adalah Jam ke-{$maxJam}.",
                    ]);
                }

                if ($jamKembaliInt <= $jamKeluarInt) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_kembali' => 'Jam kembali harus lebih besar dari jam keluar.',
                    ]);
                }

                $storedJamKeluar = 'Jam Pelajaran ke-' . $jamKeluarInt;
                $storedJamKembali = 'Jam Pelajaran ke-' . $jamKembaliInt;
                $batasWaktu = TimeHelper::getBatasWaktuKembali($storedJamKembali, $targetDayOfWeek);

            } elseif (preg_match('/^\d{2}:\d{2}$/', $rawJamKeluar) && preg_match('/^\d{2}:\d{2}$/', $rawJamKembali)) {
                [$jamKeluarHour, $jamKeluarMinute] = array_map('intval', explode(':', $rawJamKeluar));
                [$jamKembaliHour, $jamKembaliMinute] = array_map('intval', explode(':', $rawJamKembali));

                $jamKeluarMinutes = ($jamKeluarHour * 60) + $jamKeluarMinute;
                $jamKembaliMinutes = ($jamKembaliHour * 60) + $jamKembaliMinute;

                if ($jamKeluarMinutes < $scheduleStartMin || $jamKeluarMinutes > $scheduleEndMin) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_keluar' => "Jam keluar ({$rawJamKeluar} WIB) berada di luar jadwal KBM sekolah ({$scheduleEarliest} - {$scheduleLatest} WIB).",
                    ]);
                }

                if ($jamKembaliMinutes < $scheduleStartMin || $jamKembaliMinutes > $scheduleEndMin) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_kembali' => "Jam kembali ({$rawJamKembali} WIB) berada di luar jadwal KBM sekolah ({$scheduleEarliest} - {$scheduleLatest} WIB).",
                    ]);
                }

                if ($targetDate->equalTo($today)) {
                    $nowWib = now('Asia/Jakarta');
                    $currentMinutes = ($nowWib->hour * 60) + $nowWib->minute;
                    if ($jamKeluarMinutes < $currentMinutes) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'jam_keluar' => 'Jam keluar tidak boleh menggunakan waktu yang sudah lewat.',
                        ]);
                    }
                }

                if ($jamKembaliMinutes <= $jamKeluarMinutes) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jam_kembali' => 'Jam kembali harus lebih besar dari jam keluar.',
                    ]);
                }

                $storedJamKeluar = $rawJamKeluar;
                $storedJamKembali = $rawJamKembali;
                $batasWaktu = $targetDate->copy()->setTime($jamKembaliHour, $jamKembaliMinute);
            } else {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_keluar' => 'Format jam keluar tidak valid.',
                    'jam_kembali' => 'Format jam kembali tidak valid.',
                ]);
            }
        }

        $fotoPath = null;

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $request,
                $validated,
                &$fotoPath,
                $storedJamKeluar,
                $storedJamKembali,
                $batasWaktu,
                $tipePengajuan,
                $targetDate,
                $isSampaiPulang
            ) {
                $siswa = auth()->user()->siswa;
                if (! $siswa) {
                    abort(403, 'Profil siswa tidak ditemukan.');
                }

                $pendingDispensasi = Dispensasi::where('siswa_id', $siswa->id)
                    ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
                    ->whereRaw('DATE(COALESCE(tanggal_izin, created_at)) = ?', [$targetDate->toDateString()])
                    ->lockForUpdate()
                    ->first();

                if ($pendingDispensasi) {
                    $errorMessage = match ($pendingDispensasi->status) {
                        'menunggu' => 'Pengajuan dispensasi Anda untuk tanggal tersebut masih menunggu persetujuan Guru Piket.',
                        'disetujui' => 'Pengajuan dispensasi Anda untuk tanggal tersebut sudah disetujui.',
                        'keluar' => 'Anda masih memiliki dispensasi yang sedang berlangsung.',
                        default => 'Anda masih memiliki pengajuan dispensasi yang aktif untuk tanggal tersebut.',
                    };

                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'kategori' => $errorMessage,
                    ]);
                }

                $normalizedPhone = $this->normalizePhoneNumber($validated['no_telepon']);
                $siswa->update(['no_telepon' => $normalizedPhone]);

                if ($request->hasFile('foto_verifikasi')) {
                    $foto = $request->file('foto_verifikasi');
                    $filename = 'verif_' . now('Asia/Jakarta')->format('YmdHis') . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
                    $fotoPath = $foto->storeAs('foto_verifikasi', $filename, 'public');
                }

                $dispensasi = Dispensasi::create([
                    'siswa_id' => $siswa->id,
                    'guru_id' => null,
                    'nomor_surat' => Dispensasi::generateNomorSurat(),
                    'tipe_pengajuan' => $tipePengajuan,
                    'tanggal_izin' => $targetDate->toDateString(),
                    'sampai_pulang' => $isSampaiPulang,
                    'status' => 'menunggu',
                    'kategori' => $validated['kategori'],
                    'alasan' => $validated['alasan'],
                    'tujuan' => $validated['tujuan'],
                    'lokasi' => $validated['lokasi'] ?? null,
                    'jam_keluar' => $storedJamKeluar,
                    'jam_kembali' => $storedJamKembali,
                    'batas_waktu_kembali' => $batasWaktu,
                    'foto_verifikasi' => $fotoPath,
                    'dibuat_manual_oleh_guru' => false,
                ]);

                // Hubungkan anggota jika tipe rombongan
                if ($tipePengajuan === 'rombongan' && ! empty($validated['anggota_ids'])) {
                    $anggotaIds = array_diff(array_unique($validated['anggota_ids']), [$siswa->id]);
                    $dispensasi->anggotaSiswa()->sync($anggotaIds);

                    // Kirim notifikasi ke seluruh anggota
                    $notifService = app(\App\Services\NotifikasiService::class);
                    $anggotaSiswaList = \App\Models\Siswa::whereIn('id', $anggotaIds)->get();
                    $tanggalStr = $targetDate->translatedFormat('d F Y');

                    foreach ($anggotaSiswaList as $anggota) {
                        if ($anggota->user_id) {
                            try {
                                $notifService->send(
                                    $anggota->user_id,
                                    "Anda telah ditambahkan ke Dispensasi Rombongan oleh {$siswa->nama_lengkap} untuk tanggal {$tanggalStr}.",
                                    route('siswa.pengajuan.show', $dispensasi->id)
                                );
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            });

            return redirect()->route('siswa.pengajuan.index')
                ->with('success', 'Pengajuan dispensasi berhasil dibuat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            Log::error('Gagal membuat dispensasi: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan: ' . $e->getMessage());
        }
    }

    public function show(Dispensasi $dispensasi)
    {
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();

        $siswa = auth()->user()->siswa;
        $isOwner = $siswa && $dispensasi->siswa_id === $siswa->id;
        $isAnggota = $siswa && $dispensasi->anggotaSiswa()->where('siswa.id', $siswa->id)->exists();

        if (! $siswa || (! $isOwner && ! $isAnggota)) {
            abort(403, 'Akses ditolak.');
        }

        $dispensasi->load([
            'guru',
            'siswa.kelas.jurusan',
            'siswa.user',
            'anggotaSiswa.kelas.jurusan',
            'anggotaSiswa.user',
        ]);

        // ============================================================
    // FITUR: Hubungi Guru Piket
    // Popup hanya tersedia selama 3 menit sejak created_at
    // ============================================================
    $popupEligible = false;
    $popupSecondsLeft = 0;
    $daftarGuruPiket = collect();
    $hubungiGuru = null;
    $hubungiError = null;

    if ($dispensasi->status === 'menunggu') {
        $expiredAt = $dispensasi->created_at
            ->copy()
            ->addMinutes(Dispensasi::DURASI_KADALUARSA_MENUNGGU_MENIT);

    $now = now('Asia/Jakarta');

   $popupSecondsLeft = 0;

        if ($now->lt($expiredAt)) {
            $popupEligible = true;
            $popupSecondsLeft = max(
                0,
                $expiredAt->timestamp - $now->timestamp
            );
        

            /*
             * Ambil daftar guru piket yang sedang bertugas pada sesi saat ini.
             * Sesuai jadwal yang diatur oleh admin.
             */

            // 1. Ambil seluruh petugas dari sesi piket yang sedang berlangsung saat ini
            try {
                $guruPiketService = app(\App\Services\GuruPiketService::class);
                $infoSesi = $guruPiketService->getInformasiSesi();

                if (! ($infoSesi['conflict'] ?? false) && ! empty($infoSesi['petugas'])) {
                    foreach ($infoSesi['petugas'] as $p) {
                        $guru = $p['guru'] ?? null;
                        if ($guru && $guru->status_aktif) {
                            $daftarGuruPiket->push([
                                'guru' => $guru,
                                'status' => $p['status'] ?? 'Sedang Bertugas',
                                'is_pengganti' => $p['is_pengganti'] ?? false,
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    'Gagal resolve Guru Piket untuk fitur Hubungi Guru.',
                    [
                        'dispensasi_id' => $dispensasi->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            // 2. Jika sesi tidak ada petugas, gunakan guru yang tersimpan di permohonan
            if ($daftarGuruPiket->isEmpty() && $dispensasi->guru && $dispensasi->guru->status_aktif) {
                $daftarGuruPiket->push([
                    'guru' => $dispensasi->guru,
                    'status' => 'Guru Terjadwal',
                    'is_pengganti' => false,
                ]);
            }

            // 3. Fallback Guru dari Settings Admin
            if ($daftarGuruPiket->isEmpty()) {
                $fallbackGuruId = \App\Models\Setting::get(
                    'fallback_guru_piket_id'
                ) ?? \App\Models\Setting::get('guru_piket_fallback_id');

                if ($fallbackGuruId) {
                    $fallbackGuru = \App\Models\Guru::query()
                        ->whereKey($fallbackGuruId)
                        ->where('status_aktif', true)
                        ->first();
                    if ($fallbackGuru) {
                        $daftarGuruPiket->push([
                            'guru' => $fallbackGuru,
                            'status' => 'Petugas Piket',
                            'is_pengganti' => false,
                        ]);
                    }
                }
            }

            // 4. Fallback Guru Piket yang memiliki jadwal hari ini
            if ($daftarGuruPiket->isEmpty()) {
                try {
                    $jadwalHariIni = $guruPiketService->getJadwalUntukTanggal();
                    foreach ($jadwalHariIni as $j) {
                        if ($j->guru && $j->guru->status_aktif && !empty($j->guru->no_telepon)) {
                            $daftarGuruPiket->push([
                                'guru' => $j->guru,
                                'status' => 'Guru Piket Hari Ini',
                                'is_pengganti' => false,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {}
            }

            // 5. Fallback Guru aktif manapun yang memiliki nomor telepon
            if ($daftarGuruPiket->isEmpty()) {
                $guruAktif = \App\Models\Guru::where('status_aktif', true)
                    ->whereNotNull('no_telepon')
                    ->where('no_telepon', '!=', '')
                    ->first();
                if ($guruAktif) {
                    $daftarGuruPiket->push([
                        'guru' => $guruAktif,
                        'status' => 'Petugas Piket',
                        'is_pengganti' => false,
                    ]);
                }
            }

            if ($daftarGuruPiket->isNotEmpty()) {
                $hubungiGuru = $daftarGuruPiket->first()['guru'];
            }

            if ($daftarGuruPiket->isEmpty()) {
                $hubungiError = 'Guru Piket belum tersedia untuk pengajuan ini.';
            }
        }
    }

    return view('siswa.pengajuan.show', compact(
        'dispensasi',
        'popupEligible',
        'popupSecondsLeft',
        'daftarGuruPiket',
        'hubungiGuru',
        'hubungiError',
    ));
}

public function hubungiGuruPiket(\Illuminate\Http\Request $request, Dispensasi $dispensasi)
{
    $siswa = auth()->user()->siswa;

    if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
        abort(403, 'Akses ditolak.');
    }

    if ($dispensasi->status !== 'menunggu') {
        return redirect()
            ->route('siswa.pengajuan.show', $dispensasi)
            ->with('error', 'Pengajuan ini sudah tidak dapat digunakan untuk menghubungi Guru Piket.');
    }

    $expiredAt = $dispensasi->created_at
        ->copy()
        ->addMinutes(Dispensasi::DURASI_KADALUARSA_MENUNGGU_MENIT);

    if (! now('Asia/Jakarta')->lt($expiredAt)) {
        return redirect()
            ->route('siswa.pengajuan.show', $dispensasi)
            ->with('error', 'Waktu untuk menghubungi Guru Piket telah berakhir.');
    }

    $dispensasi->load([
        'guru',
        'siswa.kelas.jurusan',
        'siswa.user',
    ]);

    $hubungiGuru = null;

    // 0. Cek jika spesifik guru_id dikirimkan dari modal
    $guruId = $request->query('guru_id');
    if ($guruId) {
        $hubungiGuru = \App\Models\Guru::where('id', $guruId)
            ->where('status_aktif', true)
            ->first();
    }

    // 1. Guru Piket aktif dari sesi saat ini jika guru_id belum terpilih
    if (! $hubungiGuru) {
        try {
            $guruPiketService = app(\App\Services\GuruPiketService::class);
            $infoSesi = $guruPiketService->getInformasiSesi();

            if (
                ! ($infoSesi['conflict'] ?? false)
                && ! empty($infoSesi['petugas'])
            ) {
                $petugas = collect($infoSesi['petugas']);

                // Prioritas: guru yang sedang bertugas
                $sedangBertugas = $petugas->first(function ($p) {
                    return isset($p['guru'])
                        && $p['guru']
                        && $p['guru']->status_aktif
                        && ($p['status'] ?? null) === 'Sedang Bertugas';
                });

                if ($sedangBertugas) {
                    $hubungiGuru = $sedangBertugas['guru'];
                }

                // Fallback deterministic dari petugas aktual
                if (! $hubungiGuru) {
                    $petugasAktual = $petugas->first(function ($p) {
                        return isset($p['guru'])
                            && $p['guru']
                            && $p['guru']->status_aktif;
                    });

                    if ($petugasAktual) {
                        $hubungiGuru = $petugasAktual['guru'];
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Gagal resolve Guru Piket untuk WhatsApp.',
                [
                    'dispensasi_id' => $dispensasi->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    // 2. Guru yang tersimpan pada pengajuan
    if (! $hubungiGuru && $dispensasi->guru && $dispensasi->guru->status_aktif) {
        $hubungiGuru = $dispensasi->guru;
    }

    // 3. Fallback Guru Piket dari Settings Admin
    if (! $hubungiGuru) {
        $fallbackGuruId = \App\Models\Setting::get(
            'fallback_guru_piket_id'
        ) ?? \App\Models\Setting::get('guru_piket_fallback_id');

        if ($fallbackGuruId) {
            $hubungiGuru = \App\Models\Guru::query()
                ->whereKey($fallbackGuruId)
                ->where('status_aktif', true)
                ->first();
        }
    }

    // 4. Fallback Guru Piket yang memiliki jadwal hari ini
    if (! $hubungiGuru) {
        try {
            $guruPiketService = app(\App\Services\GuruPiketService::class);
            $jadwalHariIni = $guruPiketService->getJadwalUntukTanggal();
            $petugasHariIni = $jadwalHariIni->first(fn($j) => $j->guru && $j->guru->status_aktif && !empty($j->guru->no_telepon));
            if ($petugasHariIni) {
                $hubungiGuru = $petugasHariIni->guru;
            }
        } catch (\Throwable $e) {}
    }

    // 5. Fallback Guru aktif manapun yang memiliki nomor telepon
    if (! $hubungiGuru) {
        $hubungiGuru = \App\Models\Guru::where('status_aktif', true)
            ->whereNotNull('no_telepon')
            ->where('no_telepon', '!=', '')
            ->first();
    }

    if (! $hubungiGuru) {
        return redirect()
            ->route('siswa.pengajuan.show', $dispensasi)
            ->with('error', 'Guru Piket belum tersedia untuk pengajuan ini.');
    }

    $waService = app(\App\Services\WhatsappMessageService::class);

    $result = $waService->generateHubungiGuruPiketWaLink(
        $dispensasi,
        $hubungiGuru
    );

    if (! $result['url']) {
        return redirect()
            ->route('siswa.pengajuan.show', $dispensasi)
            ->with('error', $result['error'] ?? 'WhatsApp Guru Piket belum tersedia.');
    }

    return redirect()->away($result['url']);
}

        /**
        * <i class="fas fa-check-circle"></i> PERBAIKAN: Generate QR Code hanya berisi Token JSON, dan kembalikan URL absolut
        */
        public function getQRCode(Dispensasi $dispensasi)
        {
            $siswa = auth()->user()->siswa;
            $isOwner = $siswa && $dispensasi->siswa_id === $siswa->id;
            $isAnggota = $siswa && $dispensasi->anggotaSiswa()->where('siswa.id', $siswa->id)->exists();

            if (! $siswa || (! $isOwner && ! $isAnggota)) {
                abort(403, 'Akses ditolak.');
            }

            if (! in_array($dispensasi->status, ['disetujui', 'keluar'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code hanya tersedia untuk pengajuan yang sudah disetujui atau sedang keluar.',
                ], 400);
            }

            if (empty($dispensasi->qr_code)) {
                if (empty($dispensasi->qr_token)) {
                    $dispensasi->qr_token = Str::random(64);
                }

                // <i class="fas fa-check-circle"></i> HANYA TOKEN DALAM FORMAT JSON (Agnostik terhadap domain)
                $qrContent = json_encode(['token' => $dispensasi->qr_token]);
                $qrCodePath = 'qr_codes/dispensasi_'.$dispensasi->id.'.png';

                Storage::disk('public')->makeDirectory('qr_codes');

                QrCode::format('png')->size(300)->generate(
                    $qrContent,
                    storage_path('app/public/'.$qrCodePath)
                );

                $dispensasi->qr_code = $qrCodePath;
                $dispensasi->save();
            }

            return response()->json([
                'success' => true,
                'qr_code' => $dispensasi->qr_code,
                // <i class="fas fa-check-circle"></i> URL ABSOLUT agar frontend bisa menampilkannya di localhost MAUPUN production
                'qr_code_url' => asset('storage/' . $dispensasi->qr_code),
                'nomor_surat' => $dispensasi->nomor_surat,
                'jam_keluar' => $dispensasi->jam_keluar,
                'jam_kembali' => $dispensasi->jam_kembali,
            ]);
        }

        // private function generateNomorSurat(): string
        // {
        //     $tanggal = now()->format('Ymd');
        //     $random = strtoupper(substr(md5(uniqid()), 0, 6));
        //     return "DISP/{$tanggal}/{$random}";
        // }

        private function normalizePhoneNumber(string $phone): string
        {
            $digits = preg_replace('/[^0-9]/', '', $phone);

            if (str_starts_with($digits, '620')) {
                $digits = '62' . substr($digits, 3);
            } elseif (str_starts_with($digits, '0')) {
                $digits = '62' . substr($digits, 1);
            } elseif (str_starts_with($digits, '8')) {
                $digits = '62' . $digits;
            }

            return $digits;
        }




        /**
        * Upload Foto Bukti (Siswa)
        */
        public function uploadFotoBukti(Request $request, Dispensasi $dispensasi)
        {
            $request->validate([
                'foto_bukti' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB (aman karena dikompres client-side)
            ], [
                'foto_bukti.required' => 'Foto bukti wajib diupload.',
            ]);

            $siswa = auth()->user()->siswa;
            if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
                abort(403, 'Akses ditolak.');
            }

            if ($dispensasi->status !== 'keluar') {
                return response()->json(['success' => false, 'message' => 'Hanya bisa upload saat status Keluar'], 400);
            }

            // Hapus foto lama jika ada
            if ($dispensasi->foto_bukti) {
                Storage::disk('public')->delete($dispensasi->foto_bukti);
            }

            $fotoPath = $request->file('foto_bukti')->store('foto-bukti', 'public');

            $dispensasi->update([
                'foto_bukti' => $fotoPath,
                'foto_bukti_uploaded_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto bukti berhasil diupload',
                'foto_url' => asset('storage/' . $fotoPath),
            ]);
        }

        /**
        * Hapus Foto Bukti (Siswa)
        */
        public function hapusFotoBukti(Dispensasi $dispensasi)
        {
            $siswa = auth()->user()->siswa;
            if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
                abort(403, 'Akses ditolak.');
            }

            if ($dispensasi->foto_bukti) {
                Storage::disk('public')->delete($dispensasi->foto_bukti);
            }

            $dispensasi->update([
                'foto_bukti' => null,
                'foto_bukti_uploaded_at' => null,
            ]);

            return response()->json(['success' => true, 'message' => 'Foto bukti berhasil dihapus']);
        }

        /**
         * Siswa membatalkan dispensasi yang sudah disetujui (sebelum scan keluar di gerbang satpam)
         */
        public function batalKeluar(Request $request, Dispensasi $dispensasi)
        {
            $siswa = auth()->user()->siswa;
            if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
                abort(403, 'Akses ditolak.');
            }

            if ($dispensasi->status !== 'disetujui' || ! empty($dispensasi->waktu_keluar_aktual)) {
                return back()->with('error', 'Dispensasi tidak dapat dibatalkan karena sudah discan keluar oleh satpam atau status telah berubah.');
            }

            $validated = $request->validate([
                'alasan_batal' => 'nullable|string|max:255',
            ]);

            $alasan = $validated['alasan_batal'] ?? 'Siswa tidak jadi keluar / membatalkan izin.';

            \Illuminate\Support\Facades\DB::transaction(function () use ($dispensasi, $alasan) {
                $locked = Dispensasi::whereKey($dispensasi->id)
                    ->where('status', 'disetujui')
                    ->whereNull('waktu_keluar_aktual')
                    ->lockForUpdate()
                    ->first();

                if (! $locked) {
                    throw new \RuntimeException('Dispensasi tidak dapat dibatalkan atau sudah diproses.');
                }

                $locked->update([
                    'status' => 'dibatalkan',
                    'catatan_admin' => 'Dibatalkan oleh siswa: ' . $alasan,
                ]);

                DispensasiService::cleanupCompletedDispensasiFiles($locked);
            });

            // Audit Log
            app(AuditLogService::class)->log(
                auth()->id(),
                'batal_keluar_siswa',
                'dispensasi',
                $dispensasi->id,
                ['status' => 'disetujui'],
                ['status' => 'dibatalkan', 'alasan' => $alasan]
            );

            return redirect()->route('siswa.pengajuan.index')
                ->with('success', 'Dispensasi berhasil dibatalkan. Anda tercatat tidak jadi keluar sekolah.');
        }
    }

