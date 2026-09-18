<?php

namespace App\Http\Controllers\Siswa;

    use App\Helpers\DispensasiTimeHelper;
    use App\Helpers\TimeHelper;
    use App\Http\Controllers\Controller;
    use App\Models\Dispensasi;
    use App\Models\Setting;
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

            $maxJam = \App\Helpers\DispensasiTimeHelper::getMaxJamPelajaran(now()->dayOfWeek);

            // ✅ AMBIL JADWAL DINAMIS DARI DATABASE
            $defaultJadwal = json_encode([
                'regular' => array_fill(1, 10, ['start' => '00:00', 'end' => '00:00']),
                'friday'  => array_fill(1, 8, ['start' => '00:00', 'end' => '00:00'])
            ]);
            $jadwalPelajaran = json_decode(Setting::get('jam_pelajaran', $defaultJadwal), true);

            return view('siswa.pengajuan.create', compact('siswa', 'settings', 'maxJam', 'jadwalPelajaran'));
        }

        // ... (method lainnya tetap sama) ...

        private function hitungBatasWaktuKembali(int $jamPelajaran): Carbon
        {
            // ✅ TAMBAHKAN $dayOfWeek AGAR JADWAL JUMAT TERBACA
            $dayOfWeek = now()->dayOfWeek;
            $waktuAktual = TimeHelper::getWaktuAktual('Jam Pelajaran ke-' . $jamPelajaran, $dayOfWeek);

            $parts = explode(' - ', $waktuAktual);
            $waktuSelesai = $parts[1] ?? '15:15';

            return Carbon::parse($waktuSelesai);
        }

        public function store(Request $request)
            {
                // ✅ DOUBLE CHECK: Validasi waktu di server
                $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();
                if (!$timeCheck['allowed']) {
                    return redirect()->route('siswa.pengajuan.index')
                        ->with('error', 'Pengajuan ditolak: ' . $timeCheck['reason']);
                }

                // ✅ TAMBAHKAN: Validasi jam keluar/kembali tidak melebihi batas
                $dayOfWeek = now()->dayOfWeek;
                $maxJam = DispensasiTimeHelper::getMaxJamPelajaran($dayOfWeek);

                $validated = $request->validate([
                    'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
                    'alasan' => 'required|string|min:10|max:500',
                    'tujuan' => 'required|string|max:255',
                    'lokasi' => 'nullable|string|max:255',
                    'no_telepon' => ['required', 'string', 'regex:/^(?:\+?62|0)?8[0-9]{7,12}$/'],
                    'jam_keluar' => "required|integer|between:1,{$maxJam}", // ✅ Dinamis
                    'jam_kembali' => "required|integer|between:1,{$maxJam}|gt:jam_keluar", // ✅ Dinamis
                    'foto_verifikasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                ], [
                    'alasan.min' => 'Alasan minimal 10 karakter agar lebih jelas.',
                    'jam_kembali.gt' => 'Jam kembali harus lebih besar dari jam keluar.',
                    'no_telepon.regex' => 'Format nomor tidak valid.',
                    'jam_keluar.between' => "Jam keluar harus antara 1 dan {$maxJam}.",
                    'jam_kembali.between' => "Jam kembali harus antara 1 dan {$maxJam}.",
                ]);

            $fotoPath = null; // ✅ Definisikan di luar agar bisa diakses catch block

            try {
                    // ✅ PERBAIKAN TYPO: Hapus "throw" di depan DB::transaction
                    \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, &$fotoPath) {
                        $siswa = auth()->user()->siswa;

                    $pendingDispensasi = Dispensasi::where('siswa_id', $siswa->id)
                        ->where('status', ['menunggu', 'disetujui', 'keluar'])
                        ->lockForUpdate()
                        ->first();

                    if ($pendingDispensasi) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'menunggu' => 'Pengajuan dispensasi Anda masih menunggu diproses.',
                            'disetujui' => 'Pengajuan dispensasi Anda sudah disetujui dan belum selesai.', 'keluar' => 'Anda masih memiliki dispensasi yang sedang berlangsung. Selesaikan pengajuan terlebih dahulu sebelum membuat pengajuan baru.',
                            'kategori' => 'Anda masih memiliki pengajuan yang belum diproses.'
                        ]);
                    }

                    $normalizedPhone = $this->normalizePhoneNumber($validated['no_telepon']);
                    $siswa->update(['no_telepon' => $normalizedPhone]);

                    if ($request->hasFile('foto_verifikasi')) {
                        $fotoPath = $request->file('foto_verifikasi')->store('foto-verifikasi', ['disk' => 'public']);
                    }

                    $batasWaktu = $this->hitungBatasWaktuKembali($validated['jam_kembali']);

                    Dispensasi::create([
                        'siswa_id' => $siswa->id,
                        'guru_id' => null,
                        'nomor_surat' => \App\Models\Dispensasi::generateNomorSurat(),             'status' => 'menunggu',
                        'kategori' => $validated['kategori'],
                        'alasan' => $validated['alasan'],
                        'tujuan' => $validated['tujuan'],
                        'lokasi' => $validated['lokasi'] ?? null,
                        'jam_keluar' => 'Jam Pelajaran ke-' . $validated['jam_keluar'],
                        'jam_kembali' => 'Jam Pelajaran ke-' . $validated['jam_kembali'],
                        'batas_waktu_kembali' => $batasWaktu,
                        'foto_verifikasi' => $fotoPath,
                    ]);
                });

                return redirect()->route('siswa.pengajuan.index')
                    ->with('success', 'Pengajuan dispensasi berhasil dibuat.');

            } catch (\Illuminate\Validation\ValidationException $e) {
                // ✅ Hapus foto jika validasi gagal di dalam transaction
                if ($fotoPath) \Illuminate\Support\Facades\Storage::disk('public')->delete($fotoPath);
                throw $e;
            } catch (\Exception $e) {
                // ✅ Hapus foto jika database error/rollback
                if ($fotoPath) \Illuminate\Support\Facades\Storage::disk('public')->delete($fotoPath);
                Log::error('Gagal membuat dispensasi: ' . $e->getMessage());
                return redirect()->back()->withInput()
                    ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan.');
            }
        }

        public function show(Dispensasi $dispensasi)
        {
            if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
                abort(403, 'Akses ditolak.');
            }

            $dispensasi->load(['guru', 'siswa.kelas.jurusan', 'siswa.user']);
            return view('siswa.pengajuan.show', compact('dispensasi'));
        }

        /**
        * <i class="fas fa-check-circle"></i> PERBAIKAN: Generate QR Code hanya berisi Token JSON, dan kembalikan URL absolut
        */
        public function getQRCode(Dispensasi $dispensasi)
        {
            if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
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
            if (str_starts_with($digits, '62')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            return '+62'.$digits;
        }

        private function getCurrentLessonHour(): int
        {
            // Mode demo: anggap selalu jam pelajaran ke-1 agar form bisa diisi kapan saja
            // return 1;

            // Kode asli (aktifkan jika sudah production):
            $now = now();
            $currentHour = (int) $now->format('H');
            $currentMinute = (int) $now->format('i');
            $jadwal = [
                1 => ['start' => '07:00', 'end' => '07:45'],
                2 => ['start' => '07:45', 'end' => '08:30'],
                3 => ['start' => '08:30', 'end' => '09:15'],
                4 => ['start' => '09:30', 'end' => '10:15'],
                5 => ['start' => '10:15', 'end' => '11:00'],
                6 => ['start' => '11:00', 'end' => '11:45'],
                7 => ['start' => '12:15', 'end' => '13:00'],
                8 => ['start' => '13:00', 'end' => '13:45'],
                9 => ['start' => '13:45', 'end' => '14:30'],
                10 => ['start' => '14:30', 'end' => '15:15'],
            ];

            $currentTime = $currentHour * 60 + $currentMinute;
            foreach ($jadwal as $jam => $waktu) {
                $startMinute = (int) explode(':', $waktu['start'])[0] * 60 + (int) explode(':', $waktu['start'])[1];
                $endMinute = (int) explode(':', $waktu['end'])[0] * 60 + (int) explode(':', $waktu['end'])[1];
                if ($currentTime >= $startMinute) {
                    if ($currentTime <= $endMinute) return $jam;
                } else {
                    return $jam;
                }
            }
            return 10;
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

            if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
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
            if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
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
    }
