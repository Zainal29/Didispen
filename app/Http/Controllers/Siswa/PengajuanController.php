<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;

        $query = Dispensasi::with(['guruPiket.guru'])
            ->where('siswa_id', $siswa->id);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->paginate(15);

        return view('siswa.pengajuan.index', compact('pengajuan'));
    }

    public function create()
    {
        // Ambil guru piket hari ini
        $guruPiket = GuruPiket::with('guru')
            ->where('tanggal', today())
            ->get();

        return view('siswa.pengajuan.create', compact('guruPiket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guru_piket_id' => 'required|exists:guru_piket,id',
            'kategori' => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan' => 'required|string|min:10',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'jam_keluar' => 'required|integer|between:1,10',
            'jam_kembali' => 'required|integer|between:1,10|gt:jam_keluar',
            // ✅ 'bukti_file' SUDAH DIHAPUS DARI VALIDASI
        ]);

        $siswa = auth()->user()->siswa;

        // ✅ LOGIKA UPLOAD FILE SUDAH DIHAPUS

        $data['siswa_id'] = $siswa->id;
        $data['nomor_surat'] = $this->generateNomorSurat();
        $data['status'] = 'menunggu';

        // SIMPAN SEBAGAI TEKS BIASA
        $data['jam_keluar'] = 'Jam Pelajaran ke-' . $data['jam_keluar'];
        $data['jam_kembali'] = 'Jam Pelajaran ke-' . $data['jam_kembali'];

        $dispensasi = Dispensasi::create($data);

        return redirect()->route('siswa.pengajuan.index')
            ->with('success', 'Pengajuan dispensasi berhasil dibuat. Menunggu persetujuan guru piket.');
    }

    public function show(Dispensasi $dispensasi)
    {
        // Pastikan hanya siswa pemilik yang bisa lihat
        if ($dispensasi->siswa_id !== auth()->user()->siswa->id) {
            abort(403);
        }

        $dispensasi->load(['guruPiket.guru', 'siswa.kelas.jurusan']);

        return view('siswa.pengajuan.show', compact('dispensasi'));
    }

    private function generateNomorSurat(): string
    {
        $tanggal = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "DISP/{$tanggal}/{$random}";
    }
}