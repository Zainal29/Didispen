<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Siswa;
use App\Helpers\TimeHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PengajuanController extends Controller
{
    /**
     * Daftar riwayat pengajuan yang dibuat guru
     */
    public function index(Request $request)
    {
        $guruId = auth()->user()->guru->id ?? null;

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('guru_id', $guruId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->paginate(15);

        return view('guru.pengajuan.index', compact('pengajuan'));
    }

    /**
     * Form buat pengajuan manual
     */
    public function create()
    {
        return view('guru.pengajuan.create');
    }

    /**
     * ✅ ENDPOINT PENCARIAN SISWA (AJAX untuk Select2)
     * Mencari berdasarkan NIS atau Nama, tapi mengembalikan ID untuk disimpan
     */
    public function searchSiswa(Request $request)
    {
        $query = $request->get('q', '');

        $siswas = Siswa::with(['user', 'kelas.jurusan'])
            ->where('status_aktif', 1)
            ->where(function($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                  ->orWhereHas('user', function($q2) use ($query) {
                      $q2->where('nis_nip', 'like', "%{$query}%");
                  });
            })
            ->limit(15)
            ->get()
            ->map(function($s) {
                // Ekstrak data dengan aman untuk menghindari error null
                $nis = $s->user ? $s->user->nis_nip : 'Tanpa NIS';
                $kelas = $s->kelas ? $s->kelas->nama_kelas : 'Tanpa Kelas';

                return [
                    'id' => $s->id, // ✅ Ini yang akan disimpan ke DB (Aman & Benar)
                    'text' => "{$nis} - {$s->nama_lengkap} ({$kelas})", // ✅ Ini yang DITAMPILKAN di layar
                ];
            });

        return response()->json(['results' => $siswas]);
    }

    /**
     * Simpan pengajuan manual — Langsung Disetujui
     */
    public function store(Request $request)
    {
        // SESUDAH (Benar)
        $validated = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id', // <-- Perbaiki menjadi 'siswa'
            'kategori'        => 'required|in:sakit,izin,keperluan_sekolah,lainnya',
            'alasan'          => 'required|string|min:10',
            'tujuan'          => 'required|string|max:255',
            'jam_keluar'      => 'required|integer|between:1,10',
            'jam_kembali'     => 'required|integer|between:1,10|gt:jam_keluar',
            'foto_verifikasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'jam_kembali.gt' => 'Jam kembali harus lebih besar dari jam keluar.',
            'foto_verifikasi.image' => 'File harus berupa gambar (JPG/PNG).',
            'foto_verifikasi.max'   => 'Ukuran foto maksimal 2MB.',
        ]);

        // Hitung batas waktu kembali
        $waktuAktual = TimeHelper::getWaktuAktual('Jam Pelajaran ke-'.$validated['jam_kembali']);
        $parts = explode(' - ', $waktuAktual);
        $batasWaktu = Carbon::parse($parts[1] ?? '15:15')->addMinutes(15);

        $token = Str::random(64);
        $guruId = auth()->user()->guru->id ?? null;

        // Handle upload foto
        $fotoPath = null;
        if ($request->hasFile('foto_verifikasi')) {
            $fotoPath = $request->file('foto_verifikasi')->store('foto-verifikasi', ['disk' => 'public']);
        }

        // Buat Dispensasi (LANGSUNG DISETUJUI karena dibuat oleh Guru)
        Dispensasi::create([
            'siswa_id'            => $validated['siswa_id'],
            'guru_id'             => $guruId,
            'nomor_surat'         => 'DISP/' . now()->format('Ymd') . '/' . strtoupper(substr(md5($token), 0, 6)),
            'status'              => 'disetujui',
            'kategori'            => $validated['kategori'],
            'alasan'              => $validated['alasan'],
            'tujuan'              => $validated['tujuan'],
            'jam_keluar'          => 'Jam Pelajaran ke-'.$validated['jam_keluar'],
            'jam_kembali'         => 'Jam Pelajaran ke-'.$validated['jam_kembali'],
            'batas_waktu_kembali' => $batasWaktu,
            'qr_token'            => $token,
            'foto_verifikasi'     => $fotoPath,
            'catatan_admin'       => 'Dibuatkan manual oleh Guru Piket: ' . auth()->user()->name,
        ]);

        return redirect()->route('guru.pengajuan.index')
            ->with('success', 'Dispensasi berhasil dibuat dan disetujui. Siswa dapat langsung menunjukkan QR Code.');
    }

    /**
     * Detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        $guruId = auth()->user()->guru->id ?? null;

        if ($dispensasi->guru_id !== $guruId) {
            abort(403, 'Akses ditolak.');
        }

        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        return view('guru.pengajuan.show', compact('dispensasi'));
    }
}
