<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\DispensasiTimeHelper;
use App\Helpers\TimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
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

        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();

        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', $timeCheck['reason'].' Saat ini: '.($timeCheck['current_day'] ?? '').' '.($timeCheck['current_time'] ?? ''));
        }

        $pending = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->first();

        if ($pending) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('warning', 'Anda masih memiliki pengajuan yang belum diproses (No. '.$pending->nomor_surat.'). Tunggu persetujuan terlebih dahulu.');
        }

        return view('siswa.pengajuan.create', compact('siswa'));
    }

    public function store(Request $request)
    {
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();
        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Pengajuan ditolak: '.$timeCheck['reason']);
        }

        $validated = $request->validate([
            'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan' => 'required|string|min:10|max:500',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'no_telepon' => ['required', 'string', 'regex:/^(?:\+?62|0)?8[0-9]{7,12}$/'],
            'jam_keluar' => 'required|integer|between:1,10',
            'jam_kembali' => 'required|integer|between:1,10|gt:jam_keluar',
            'foto_verifikasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'alasan.min' => 'Alasan minimal 10 karakter agar lebih jelas.',
            'jam_kembali.gt' => 'Jam kembali harus lebih besar dari jam keluar.',
            'no_telepon.regex' => 'Format nomor tidak valid. Contoh: 081234567890 atau 6281234567890.',
        ]);

        $currentLessonHour = $this->getCurrentLessonHour();

        if ($validated['jam_keluar'] < $currentLessonHour) {
            return back()->withErrors([
                'jam_keluar' => 'Anda tidak dapat memilih jam yang sudah berlalu. Jam pelajaran saat ini adalah ke-'.$currentLessonHour.'. Silakan pilih mulai dari Jam Pelajaran ke-'.$currentLessonHour.'.',
            ])->withInput();
        }

        $siswa = auth()->user()->siswa;

        $pendingExists = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->exists();
        if ($pendingExists) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Anda masih memiliki pengajuan yang belum diproses.');
        }

        try {
            $normalizedPhone = $this->normalizePhoneNumber($validated['no_telepon']);
            $siswa->update(['no_telepon' => $normalizedPhone]);

            $fotoPath = null;
            if ($request->hasFile('foto_verifikasi')) {
                $fotoPath = $request->file('foto_verifikasi')->store('foto-verifikasi', ['disk' => 'public']);
            }

            $batasWaktu = $this->hitungBatasWaktuKembali($validated['jam_kembali']);

            Dispensasi::create([
                'siswa_id' => $siswa->id,
                'guru_id' => null,
                'nomor_surat' => $this->generateNomorSurat(),
                'status' => 'menunggu',
                'kategori' => $validated['kategori'],
                'alasan' => $validated['alasan'],
                'tujuan' => $validated['tujuan'],
                'lokasi' => $validated['lokasi'] ?? null,
                'jam_keluar' => 'Jam Pelajaran ke-'.$validated['jam_keluar'],
                'jam_kembali' => 'Jam Pelajaran ke-'.$validated['jam_kembali'],
                'batas_waktu_kembali' => $batasWaktu,
                'foto_verifikasi' => $fotoPath,
            ]);

            return redirect()->route('siswa.pengajuan.index')
                ->with('success', 'Pengajuan dispensasi berhasil dibuat. Menunggu persetujuan guru piket.');

        } catch (\Exception $e) {
            Log::error('Gagal membuat dispensasi: '.$e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi.');
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
     * ✅ PERBAIKAN: Generate QR Code hanya berisi Token JSON, dan kembalikan URL absolut
     */
    public function getQRCode(Dispensasi $dispensasi)
    {
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($dispensasi->status !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'QR Code hanya tersedia untuk pengajuan yang sudah DISETUJUI.',
            ], 400);
        }

        if (empty($dispensasi->qr_code)) {
            if (empty($dispensasi->qr_token)) {
                $dispensasi->qr_token = Str::random(64);
            }

            // ✅ HANYA TOKEN DALAM FORMAT JSON (Agnostik terhadap domain)
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
            // ✅ URL ABSOLUT agar frontend bisa menampilkannya di localhost MAUPUN production
            'qr_code_url' => asset('storage/' . $dispensasi->qr_code),
            'nomor_surat' => $dispensasi->nomor_surat,
            'jam_keluar' => $dispensasi->jam_keluar,
            'jam_kembali' => $dispensasi->jam_kembali,
        ]);
    }

    private function generateNomorSurat(): string
    {
        $tanggal = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "DISP/{$tanggal}/{$random}";
    }

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

    private function hitungBatasWaktuKembali(int $jamPelajaran): Carbon
    {
        $waktuAktual = TimeHelper::getWaktuAktual('Jam Pelajaran ke-'.$jamPelajaran);
        $parts = explode(' - ', $waktuAktual);
        $waktuSelesai = $parts[1] ?? '15:15';
        return Carbon::parse($waktuSelesai)->addMinutes(15);
    }
}
