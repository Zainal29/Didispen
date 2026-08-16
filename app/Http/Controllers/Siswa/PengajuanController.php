<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\GuruPiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengajuanController extends Controller
{
    /**
     * Menampilkan daftar riwayat pengajuan siswa
     */
    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;

        $query = Dispensasi::with(['guruPiket.guru'])
            ->where('siswa_id', $siswa->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->paginate(15);

        return view('siswa.pengajuan.index', compact('pengajuan'));
    }

    /**
     * Menampilkan form buat pengajuan
     */
    public function create()
    {
        $siswa = auth()->user()->siswa;

        // ✅ REVISI: Gunakan scope hariIni() alih-alih where('tanggal', today())
        $guruPiketHariIni = GuruPiket::with('guru')->hariIni()->first();

        if (!$guruPiketHariIni) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Maaf, tidak ada guru piket yang dijadwalkan hari ini. Silakan hubungi Admin.');
        }

        // ✅ Tambahan: Cegah siswa membuat pengajuan baru jika yang lama masih "menunggu"
        $pendingDispensasi = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->first();

        if ($pendingDispensasi) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('warning', 'Anda masih memiliki pengajuan yang belum diproses (No. ' . $pendingDispensasi->nomor_surat . '). Tunggu persetujuan terlebih dahulu.');
        }

        return view('siswa.pengajuan.create', compact('siswa', 'guruPiketHariIni'));
    }

    /**
     * Menyimpan pengajuan baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan' => 'required|string|min:10|max:500',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'jam_keluar' => 'required|integer|between:1,10',
            'jam_kembali' => 'required|integer|between:1,10|gt:jam_keluar',
        ], [
            'alasan.min' => 'Alasan minimal 10 karakter agar lebih jelas.',
            'jam_kembali.gt' => 'Jam kembali harus lebih besar dari jam keluar.',
        ]);

        $siswa = auth()->user()->siswa;

        // ✅ REVISI: Gunakan scope hariIni() untuk mendapatkan ID guru piket hari ini
        $guruPiket = GuruPiket::hariIni()->first();

        if (!$guruPiket) {
            return redirect()->back()
                ->with('error', 'Maaf, tidak ada guru piket yang dijadwalkan hari ini. Hubungi Admin.');
        }

        // Cegah spam pengajuan (double check)
        $pendingExists = Dispensasi::where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($pendingExists) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', 'Anda masih memiliki pengajuan yang belum diproses.');
        }

        // Susun data untuk disimpan dengan rapi
        $dataToSave = [
            'siswa_id' => $siswa->id,
            'guru_piket_id' => $guruPiket->id, // ✅ Diisi otomatis dari jadwal hari ini
            'nomor_surat' => $this->generateNomorSurat(),
            'status' => 'menunggu',
            'kategori' => $validated['kategori'],
            'alasan' => $validated['alasan'],
            'tujuan' => $validated['tujuan'],
            'lokasi' => $validated['lokasi'] ?? null,
            'jam_keluar' => 'Jam Pelajaran ke-' . $validated['jam_keluar'],
            'jam_kembali' => 'Jam Pelajaran ke-' . $validated['jam_kembali'],
        ];

        try {
            Dispensasi::create($dataToSave);

            return redirect()->route('siswa.pengajuan.index')
                ->with('success', 'Pengajuan dispensasi berhasil dibuat. Menunggu persetujuan guru piket.');
        } catch (\Exception $e) {
            Log::error('Gagal membuat dispensasi: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengajuan. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403, 'Akses ditolak.');
        }

        $dispensasi->load(['guruPiket.guru', 'siswa.kelas.jurusan']);
        return view('siswa.pengajuan.show', compact('dispensasi'));
    }

    /**
     * Mengambil data QR Code untuk ditampilkan di modal
     */
    public function getQRCode(Dispensasi $dispensasi)
    {
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($dispensasi->status !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'QR Code ini sudah pernah di-scan atau tidak aktif lagi.'
            ], 400);
        }

        return response()->json([
            'qr_code' => $dispensasi->qr_code,
            'nomor_surat' => $dispensasi->nomor_surat,
            'jam_keluar' => $dispensasi->jam_keluar,
            'jam_kembali' => $dispensasi->jam_kembali
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
}