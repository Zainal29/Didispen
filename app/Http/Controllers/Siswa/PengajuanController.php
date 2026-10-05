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

            $query = Dispensasi::with(['guru', 'siswa.kelas.jurusan'])
                ->where('siswa_id', $siswa->id);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $pengajuan = $query->latest()->paginate(15);

            return view('siswa.pengajuan.index', compact('pengajuan'));
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

            return view('siswa.pengajuan.create', compact('siswa', 'settings', 'maxJam', 'jadwalPelajaran', 'jadwalHariIni', 'istirahatHariIni', 'semuaSlotHariIni'));
        }

    public function store(Request $request)
    {
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();

        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Pengajuan ditolak: ' . $timeCheck['reason']);
        }

        $siswa = auth()->user()->siswa;

        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        $maxJam = TimeHelper::getMaxJamPelajaran($dayOfWeek);
        $jadwalHariIni = TimeHelper::getJadwalHari($dayOfWeek);

        if (empty($jadwalHariIni)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jam_keluar' => 'Jadwal pelajaran hari ini tidak tersedia.',
            ]);
        }

        $validated = $request->validate([
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
            'jam_kembali' => 'required',
            'foto_verifikasi' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'alasan.min' => 'Alasan minimal 10 karakter agar lebih jelas.',
            'alasan.max' => 'Alasan maksimal 500 karakter.',
            'jam_keluar.required' => 'Jam keluar wajib diisi.',
            'jam_kembali.required' => 'Jam kembali wajib diisi.',
            'no_telepon.regex' => 'Format nomor WhatsApp tidak valid.',
            'foto_verifikasi.required' => 'Foto verifikasi selfie wajib diambil menggunakan kamera.',
            'foto_verifikasi.max' => 'Ukuran foto verifikasi maksimal 2048 KB (2MB).',
        ]);

        $rawJamKeluar = trim((string) $validated['jam_keluar']);
        $rawJamKembali = trim((string) $validated['jam_kembali']);

        $storedJamKeluar = null;
        $storedJamKembali = null;
        $batasWaktu = null;

        // Mendukung input berupa nomor jam pelajaran (integer) maupun jam aktual format HH:MM
        if (is_numeric($rawJamKeluar) && is_numeric($rawJamKembali)) {
            $jamKeluarInt = (int) $rawJamKeluar;
            $jamKembaliInt = (int) $rawJamKembali;

            if ($jamKeluarInt < 1 || $jamKeluarInt > $maxJam) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_keluar' => "Jam keluar maksimal adalah Jam ke-{$maxJam} untuk hari ini.",
                ]);
            }

            if ($jamKembaliInt < 1 || $jamKembaliInt > $maxJam) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_kembali' => "Jam kembali maksimal adalah Jam ke-{$maxJam} untuk hari ini.",
                ]);
            }

            if ($jamKembaliInt <= $jamKeluarInt) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_kembali' => 'Jam kembali harus lebih besar dari jam keluar.',
                ]);
            }

            $storedJamKeluar = 'Jam Pelajaran ke-' . $jamKeluarInt;
            $storedJamKembali = 'Jam Pelajaran ke-' . $jamKembaliInt;
            $batasWaktu = TimeHelper::getBatasWaktuKembali($storedJamKembali, $dayOfWeek);

        } elseif (preg_match('/^\d{2}:\d{2}$/', $rawJamKeluar) && preg_match('/^\d{2}:\d{2}$/', $rawJamKembali)) {
            $nowWib = now('Asia/Jakarta');
            $currentMinutes = ($nowWib->hour * 60) + $nowWib->minute;

            [$jamKeluarHour, $jamKeluarMinute] = array_map('intval', explode(':', $rawJamKeluar));
            [$jamKembaliHour, $jamKembaliMinute] = array_map('intval', explode(':', $rawJamKembali));

            $jamKeluarMinutes = ($jamKeluarHour * 60) + $jamKeluarMinute;
            $jamKembaliMinutes = ($jamKembaliHour * 60) + $jamKembaliMinute;

            if ($jamKeluarMinutes < $currentMinutes) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_keluar' => 'Jam keluar tidak boleh menggunakan waktu yang sudah lewat.',
                ]);
            }

            if ($jamKembaliMinutes <= $jamKeluarMinutes) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_kembali' => 'Jam kembali harus lebih besar dari jam keluar.',
                ]);
            }

            $semuaSlotHariIni = TimeHelper::getSemuaSlotHari($dayOfWeek);
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
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_keluar' => 'Jam keluar harus berada dalam jadwal sekolah yang tersedia hari ini.',
                ]);
            }

            if (! $waktuKembaliValid) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jam_kembali' => 'Jam kembali harus berada dalam jadwal sekolah yang tersedia hari ini.',
                ]);
            }

            $storedJamKeluar = $rawJamKeluar;
            $storedJamKembali = $rawJamKembali;
            $batasWaktu = TimeHelper::getBatasWaktuKembali($storedJamKembali, $dayOfWeek);

        } else {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jam_keluar' => 'Format jam keluar tidak valid.',
                'jam_kembali' => 'Format jam kembali tidak valid.',
            ]);
        }

        $fotoPath = null;

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $request,
                $validated,
                &$fotoPath,
                $storedJamKeluar,
                $storedJamKembali,
                $batasWaktu
            ) {
                $siswa = auth()->user()->siswa;
                if (! $siswa) {
                    abort(403, 'Profil siswa tidak ditemukan.');
                }

                $pendingDispensasi = Dispensasi::where('siswa_id', $siswa->id)
                    ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
                    ->lockForUpdate()
                    ->first();

                if ($pendingDispensasi) {
                    $errorMessage = match ($pendingDispensasi->status) {
                        'menunggu' => 'Pengajuan dispensasi Anda sebelumnya masih menunggu persetujuan Guru Piket.',
                        'disetujui' => 'Pengajuan dispensasi Anda sudah disetujui. Tunjukkan QR Code ke Satpam atau batalkan permohonan jika tidak jadi keluar sebelum membuat pengajuan baru.',
                        'keluar' => 'Anda masih memiliki dispensasi yang sedang berlangsung. Selesaikan pengajuan terlebih dahulu sebelum membuat pengajuan baru.',
                        default => 'Anda masih memiliki pengajuan dispensasi yang aktif.',
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

                Dispensasi::create([
                    'siswa_id' => $siswa->id,
                    'guru_id' => null,
                    'nomor_surat' => Dispensasi::generateNomorSurat(),
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
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan.');
        }
    }

        public function show(Dispensasi $dispensasi)
{
    $siswa = auth()->user()->siswa;

    if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
        abort(403, 'Akses ditolak.');
    }

    $dispensasi->load([
        'guru',
        'siswa.kelas.jurusan',
        'siswa.user',
    ]);

    // ============================================================
    // FITUR: Hubungi Guru Piket
    // Popup hanya tersedia selama 3 menit sejak created_at
    // ============================================================
    $popupEligible = false;
    $popupSecondsLeft = 0;
    $hubungiGuru = null;
    $hubungiError = null;

    if ($dispensasi->status === 'menunggu') {
        $expiredAt = $dispensasi->created_at
        ->copy()
        ->addMinutes(6);

    $now = now('Asia/Jakarta');

   $popupSecondsLeft = 0;

        if ($now->lt($expiredAt)) {
            $popupEligible = true;
            $popupSecondsLeft = max(
                0,
                $expiredAt->timestamp - $now->timestamp
            );
        

            /*
             * Prioritas:
             *
             * 1. Guru yang tersimpan pada dispensasi
             * 2. Guru Piket aktual dari GuruPiketService
             * 3. Guru fallback dari Settings Admin
             *
             * URL WhatsApp TIDAK dibuat di sini.
             * URL akan dibuat oleh endpoint server-side setelah
             * ownership, status, dan batas waktu divalidasi ulang.
             */

            if ($dispensasi->guru && $dispensasi->guru->status_aktif) {
                $hubungiGuru = $dispensasi->guru;
            }

            // --------------------------------------------------------
            // Cari Guru Piket aktual dari sesi
            // --------------------------------------------------------
            if (! $hubungiGuru) {
                try {
                    $guruPiketService = app(
                        \App\Services\GuruPiketService::class
                    );

                    $infoSesi = $guruPiketService->getInformasiSesi();

                    /*
                     * Jika sesi conflict, jangan memilih guru
                     * secara sembarangan.
                     */
                    if (
                        ! ($infoSesi['conflict'] ?? false)
                        && ! empty($infoSesi['petugas'])
                    ) {
                        $petugas = collect($infoSesi['petugas']);

                        /*
                         * Prioritaskan guru yang benar-benar berstatus
                         * "Sedang Bertugas".
                         */
                        $sedangBertugas = $petugas->first(function ($p) {
                            return isset($p['guru'])
                                && $p['guru']
                                && $p['guru']->status_aktif
                                && ($p['status'] ?? null) === 'Sedang Bertugas';
                        });

                        if ($sedangBertugas) {
                            $hubungiGuru = $sedangBertugas['guru'];
                        }

                        /*
                         * Jika tidak ada yang berstatus "Sedang Bertugas",
                         * gunakan petugas aktual pertama dari service.
                         *
                         * Ini BUKAN Guru::first().
                         * Guru tersebut berasal dari resolver
                         * GuruPiketService, termasuk replacement jika ada.
                         */
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
                        'Gagal resolve Guru Piket untuk fitur Hubungi Guru.',
                        [
                            'dispensasi_id' => $dispensasi->id,
                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }

            // --------------------------------------------------------
            // Fallback Guru dari Settings Admin
            // --------------------------------------------------------
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
                $hubungiError = 'Guru Piket belum tersedia untuk pengajuan ini.';
            } elseif (! $hubungiGuru->no_telepon) {
                $hubungiError = 'Nomor WhatsApp Guru Piket belum tersedia.';
            }
        }
    }

    return view('siswa.pengajuan.show', compact(
        'dispensasi',
        'popupEligible',
        'popupSecondsLeft',
        'hubungiGuru',
        'hubungiError',
    ));
}

public function hubungiGuruPiket(Dispensasi $dispensasi)
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
        ->addMinutes(6);

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

    // 1. Guru yang tersimpan pada pengajuan
    if ($dispensasi->guru && $dispensasi->guru->status_aktif) {
        $hubungiGuru = $dispensasi->guru;
    }

    // 2. Guru Piket aktif dari sesi saat ini
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
            if (! $siswa || $dispensasi->siswa_id !== $siswa->id) {
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

