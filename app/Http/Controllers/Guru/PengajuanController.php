<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\DispensasiService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PengajuanController extends Controller
{
    public function __construct(private DispensasiService $dispensasiService) {}

    /**
     * Daftar pengajuan untuk diverifikasi guru
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;

        // ✅ Tampilkan:
        //   1. Semua dispensasi "menunggu" (siapa saja guru boleh proses)
        //   2. Dispensasi yang sudah ditangani oleh guru ini (guru_id = $guru->id)
        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where(function ($q) use ($guru) {
                $q->where('status', 'menunggu')
                    ->orWhere('guru_id', $guru->id);
            });

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ Filter Pencarian (Nama Siswa atau NIS)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($q2) use ($search) {
                    $q2->where('nama_lengkap', 'like', "%{$search}%");
                })->orWhereHas('siswa.user', function ($q2) use ($search) {
                    $q2->where('nis_nip', 'like', "%{$search}%");
                });
            });
        }

        $dispensasi = $query->latest()->paginate(15)->withQueryString();

        // Pass sebagai object semu agar view hero piket tetap kompatibel
        $piketHariIni = (object) ['guru' => $guru];

        return view('guru.pengajuan.index', compact('dispensasi', 'piketHariIni'));
    }

    /**
     * Detail pengajuan
     */
    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);
        $guru = auth()->user()->guru;

        // Keamanan:
        // - "menunggu": semua guru boleh lihat
        // - Sudah diproses: hanya guru yang tercatat di guru_id yang boleh lihat
        if ($dispensasi->status !== 'menunggu' && $dispensasi->guru_id !== $guru->id) {
            abort(403, 'Akses ditolak. Anda bukan guru piket yang menangani dispensasi ini.');
        }

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    /**
     * Approve / Setujui Pengajuan
     */
    public function approve(Request $request, Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.kelas', 'siswa.user']);
        $guru = auth()->user()->guru;

        if ($dispensasi->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Dispensasi ini sudah diproses sebelumnya.');
        }

        try {
            $dispensasi->qr_token = $dispensasi->qr_token ?? Str::random(64);
            $qrContent = json_encode(['token' => $dispensasi->qr_token]);
            $qrCodePath = 'qr_codes/dispensasi_'.$dispensasi->id.'.svg';

            if (! Storage::disk('public')->exists('qr_codes')) {
                Storage::disk('public')->makeDirectory('qr_codes');
            }

            Storage::disk('public')->put(
                $qrCodePath,
                QrCode::format('svg')->size(300)->generate($qrContent)
            );

            // ✅ Set guru_id LANGSUNG ke tabel dispensasi
            $dispensasi->update([
                'status' => 'disetujui',
                'qr_code' => $qrCodePath,
                'qr_token' => $dispensasi->qr_token,
                'guru_id' => $guru->id,
            ]);

            // Kirim notifikasi ke siswa
            app(NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "✅ Pengajuan dispensasi ({$dispensasi->nomor_surat}) telah DISETUJUI oleh Guru Piket.",
                route('siswa.pengajuan.show', $dispensasi, false)
            );

            return redirect()->route('guru.pengajuan.show', $dispensasi)
                ->with('success', "Dispensasi {$dispensasi->nomor_surat} berhasil disetujui. QR Code telah dibuat.");

        } catch (\Exception $e) {
            Log::error('Gagal generate QR Code Dispensasi: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuat QR Code. Silakan coba lagi.');
        }
    }

    /**
     * Tolak Pengajuan
     */
    public function reject(Request $request, Dispensasi $dispensasi)
    {
        $dispensasi->load('siswa.user');
        $guru = auth()->user()->guru;

        if ($dispensasi->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Dispensasi ini sudah diproses sebelumnya.');
        }

        // Validasi alasan penolakan dari SweetAlert
        $request->validate([
            'catatan_admin' => 'required|string|min:5|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            'catatan_admin.min' => 'Alasan penolakan minimal 5 karakter agar jelas.',
        ]);

        try {
            // ✅ Set guru_id LANGSUNG ke tabel dispensasi
            $dispensasi->update([
                'status' => 'ditolak',
                'catatan_admin' => $request->catatan_admin,
                'guru_id' => $guru->id, // Catat guru yang menolak
            ]);

            // Kirim notifikasi ke siswa
            app(NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "❌ Pengajuan dispensasi ({$dispensasi->nomor_surat}) DITOLAK. Alasan: {$request->catatan_admin}",
                route('siswa.pengajuan.show', $dispensasi, false)
            );

            return redirect()->route('guru.pengajuan.index')
                ->with('success', "Dispensasi {$dispensasi->nomor_surat} berhasil ditolak.");

        } catch (\Exception $e) {
            Log::error('Gagal menolak dispensasi: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menolak pengajuan.');
        }
    }
}
