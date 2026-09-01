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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
     * Endpoint pencarian siswa (AJAX untuk Select2)
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
                $nis = $s->user ? $s->user->nis_nip : 'Tanpa NIS';
                $kelas = $s->kelas ? $s->kelas->nama_kelas : 'Tanpa Kelas';

                return [
                    'id' => $s->id,
                    'text' => "{$nis} - {$s->nama_lengkap} ({$kelas})",
                ];
            });

        return response()->json(['results' => $siswas]);
    }

    /**
     * Simpan pengajuan manual — Langsung Disetujui + Generate QR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
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

        $waktuAktual = TimeHelper::getWaktuAktual('Jam Pelajaran ke-'.$validated['jam_kembali']);
        $parts = explode(' - ', $waktuAktual);
        $batasWaktu = Carbon::parse($parts[1] ?? '15:15')->addMinutes(15);

        $token = Str::random(64);
        $guruId = auth()->user()->guru->id ?? null;

        $fotoPath = null;
        if ($request->hasFile('foto_verifikasi')) {
            $fotoPath = $request->file('foto_verifikasi')->store('foto-verifikasi', ['disk' => 'public']);
        }

        $dispensasi = Dispensasi::create([
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

        // Generate QR Code
        $this->generateQRCode($dispensasi);

        return redirect()->route('guru.pengajuan.index')
            ->with('success', 'Dispensasi berhasil dibuat dan disetujui. QR Code telah di-generate.');
    }

    /**
     * Detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        // Load relasi agar data lengkap di view
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    /**
     * Setujui pengajuan dispensasi + Generate QR
     */
    public function approve(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $guruId = auth()->user()->guru->id ?? null;

        if (empty($dispensasi->qr_token)) {
            $dispensasi->qr_token = Str::random(64);
        }

        $dispensasi->update([
            'status' => 'disetujui',
            'guru_id' => $guruId,
        ]);

        // Generate QR Code
        $this->generateQRCode($dispensasi);

        return redirect()->route('guru.pengajuan.index')
            ->with('success', 'Dispensasi berhasil disetujui. QR Code telah di-generate.');
    }

    /**
     * Tolak pengajuan dispensasi
     */
    public function reject(Request $request, Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_admin' => 'required|string|min:5|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            'catatan_admin.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $dispensasi->update([
            'status' => 'ditolak',
            'catatan_admin' => $validated['catatan_admin'],
        ]);

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

         // ✅ Gunakan URL lengkap
         $qrContent = url('/verify-qr/' . $dispensasi->id . '?token=' . $dispensasi->qr_token);

         $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

         \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qr_codes');

         \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
             ->size(300)
             ->margin(0)
             ->generate($qrContent, storage_path('app/public/' . $qrCodePath));

         $dispensasi->update(['qr_code' => $qrCodePath]);
     }
}
