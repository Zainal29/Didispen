<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

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

        // Filter status jika ada
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

        // ✅ OTOMATIS: Ambil 1 guru piket yang bertugas hari ini
        $guruPiketHariIni = GuruPiket::with('guru')->where('tanggal', today())->first();

        return view('siswa.pengajuan.create', compact('siswa', 'guruPiketHariIni'));
    }

    /**
     * Menyimpan pengajuan baru ke database
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan' => 'required|string|min:10',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'jam_keluar' => 'required|integer|between:1,10',
            'jam_kembali' => 'required|integer|between:1,10|gt:jam_keluar',
        ]);

        $siswa = auth()->user()->siswa;

        // ✅ OTOMATIS: Ambil ID guru piket hari ini
        $guruPiket = GuruPiket::where('tanggal', today())->first();

        if (!$guruPiket) {
            return redirect()->back()->with('error', 'Maaf, tidak ada guru piket yang dijadwalkan hari ini. Hubungi Admin.');
        }

        $data['siswa_id'] = $siswa->id;
        $data['guru_piket_id'] = $guruPiket->id; // Langsung diisi otomatis oleh sistem
        $data['nomor_surat'] = $this->generateNomorSurat();
        $data['status'] = 'menunggu';

        // Format jam pelajaran menjadi teks
        $data['jam_keluar'] = 'Jam Pelajaran ke-' . $data['jam_keluar'];
        $data['jam_kembali'] = 'Jam Pelajaran ke-' . $data['jam_kembali'];

        Dispensasi::create($data);

        return redirect()->route('siswa.pengajuan.index')
            ->with('success', 'Pengajuan dispensasi berhasil dibuat. Menunggu persetujuan guru piket.');
    }

    /**
     * Menampilkan detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        // Pastikan hanya pemilik yang bisa melihat
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
        // Pastikan hanya pemilik yang bisa melihat
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403, 'Akses ditolak.');
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