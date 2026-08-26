<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\DispensasiTimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengajuanController extends Controller
{
    /**
     * Daftar riwayat pengajuan siswa
     */
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

    /**
     * Form buat pengajuan — dengan validasi waktu
     */
    public function create()
    {
        $siswa = auth()->user()->siswa;

        // ✅ CEK WAKTU: Apakah sekarang dalam jam pengajuan?
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();

        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', $timeCheck['reason'].' Saat ini: '.($timeCheck['current_day'] ?? '').' '.($timeCheck['current_time'] ?? ''));
        }

        // Cegah double pengajuan menunggu
        $pending = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->first();

        if ($pending) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('warning', 'Anda masih memiliki pengajuan yang belum diproses (No. '.$pending->nomor_surat.'). Tunggu persetujuan terlebih dahulu.');
        }

        return view('siswa.pengajuan.create', compact('siswa'));
    }

    /**
     * Simpan pengajuan — dengan validasi waktu & hitung batas kembali
     */
  public function store(Request $request)
    {
        // ✅ CEK WAKTU LAGI di server (untuk keamanan)
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();
        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Pengajuan ditolak: '.$timeCheck['reason']);
        }

        $validated = $request->validate([
            'kategori'    => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan'      => 'required|string|min:10|max:500',
            'tujuan'      => 'required|string|max:255',
            'lokasi'      => 'nullable|string|max:255',
            'jam_keluar'  => 'required|integer|between:1,10',
            'jam_kembali' => 'required|integer|between:1,10|gt:jam_keluar',
        ], [
            'alasan.min'     => 'Alasan minimal 10 karakter agar lebih jelas.',
            'jam_kembali.gt' => 'Jam kembali harus lebih besar dari jam keluar.',
        ]);

        // ✅ VALIDASI JAM REALISTIS (SERVER SIDE)
        // Asumsi: Jam Pelajaran ke-1 dimulai pukul 07:00 WIB
        // Rumus: Jam Pelajaran Saat Ini = Jam Sistem - 6
        // Contoh: Jam 09:30 WIB = Jam Pelajaran ke-3 (09 - 6 = 3)
        $currentLessonHour = now()->format('H') - 6; 
        
        // Pastikan tidak kurang dari 1 (jika sebelum jam 7 pagi)
        $currentLessonHour = max(1, $currentLessonHour); 

        if ($validated['jam_keluar'] <= $currentLessonHour) {
            return back()->withErrors([
                'jam_keluar' => 'Anda tidak dapat memilih jam yang sudah berlalu. Silakan pilih mulai dari Jam Pelajaran ke-' . ($currentLessonHour + 1) . '.'
            ])->withInput();
        }

        $siswa = auth()->user()->siswa;

        // Double-check cegah spam
        $pendingExists = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->exists();
        if ($pendingExists) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Anda masih memiliki pengajuan yang belum diproses.');
        }

        try {
            // ✅ BARU: Hitung batas waktu kembali berdasarkan jam pelajaran yang dipilih
            $batasWaktu = $this->hitungBatasWaktuKembali($validated['jam_kembali']);

            Dispensasi::create([
                'siswa_id'              => $siswa->id,
                'guru_id'               => null,
                'nomor_surat'           => $this->generateNomorSurat(),
                'status'                => 'menunggu',
                'kategori'              => $validated['kategori'],
                'alasan'                => $validated['alasan'],
                'tujuan'                => $validated['tujuan'],
                'lokasi'                => $validated['lokasi'] ?? null,
                'jam_keluar'            => 'Jam Pelajaran ke-'.$validated['jam_keluar'],
                'jam_kembali'           => 'Jam Pelajaran ke-'.$validated['jam_kembali'],
                'batas_waktu_kembali'   => $batasWaktu, // ✅ DISIMPAN KE DATABASE
            ]);

            return redirect()->route('siswa.pengajuan.index')
                ->with('success', 'Pengajuan dispensasi berhasil dibuat. Menunggu persetujuan guru piket.');
        } catch (\Exception $e) {
            Log::error('Gagal membuat dispensasi: '.$e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi.');
        }
    }

    /**
     * Detail pengajuan
     */public function show(Dispensasi $dispensasi)
{
    // Pastikan hanya siswa yang bersangkutan yang bisa lihat
    if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
        abort(403, 'Akses ditolak.');
    }

    $dispensasi->load(['guru', 'siswa.kelas.jurusan', 'siswa.user']);

    return view('siswa.pengajuan.show', compact('dispensasi'));
}

    /**
     * Ambil QR Code untuk modal
     */
    public function getQRCode(Dispensasi $dispensasi)
    {
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($dispensasi->status !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'QR Code ini sudah pernah di-scan atau tidak aktif lagi.',
            ], 400);
        }

        return response()->json([
            'qr_code' => $dispensasi->qr_code,
            'nomor_surat' => $dispensasi->nomor_surat,
            'jam_keluar' => $dispensasi->jam_keluar,
            'jam_kembali' => $dispensasi->jam_kembali,
        ]);
    }

    /**
     * Generate nomor surat unik
     */
    private function generateNomorSurat(): string
    {
        $tanggal = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "DISP/{$tanggal}/{$random}";
    }

    /**
     * ✅ BARU: Helper untuk menghitung batas waktu kembali + toleransi 15 menit
     */
    private function hitungBatasWaktuKembali(int $jamPelajaran): Carbon
    {
        // Mapping jam pelajaran ke waktu selesai (Sesuaikan dengan jadwal sekolah Anda)
        $jamMap = [
            1 => '08:00',
            2 => '09:00',
            3 => '10:00',
            4 => '11:00',
            5 => '12:00',
            6 => '13:00',
            7 => '14:00',
            8 => '15:00',
            9 => '16:00',
            10 => '17:00',
        ];

        $waktuSelesai = $jamMap[$jamPelajaran] ?? '15:00';

        // Tambahkan toleransi 15 menit setelah jam berakhir
        return Carbon::parse($waktuSelesai)->addMinutes(15);
    }
}
