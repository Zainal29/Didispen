<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StorePiketSwapRequest;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\PertukaranJadwalPiket;
use App\Services\GuruPiketSwapService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PiketSwapController extends Controller
{
    public function __construct(
        protected GuruPiketSwapService $swapService
    ) {}

    /**
     * Tampilkan formulir pengajuan tukar jadwal / penggantian piket.
     */
    public function create(Request $request)
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Akses khusus guru.');
        }

        // Ambil jadwal resmi milik guru ini yang aktif
        $jadwalList = $guru->jadwalPikets()
            ->where('jadwal_piket.is_active', true)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Daftar guru pengganti yang berstatus aktif (kecuali guru yang login)
        $guruPenggantiList = Guru::where('status_aktif', true)
            ->where('id', '!=', $guru->id)
            ->orderBy('nama_lengkap')
            ->get();

        $selectedJadwalId = (int) $request->get('jadwal_id', 0);
        $selectedTanggal = $request->get('tanggal', now()->toDateString());

        return view('guru.piket.swap-create', compact(
            'guru',
            'jadwalList',
            'guruPenggantiList',
            'selectedJadwalId',
            'selectedTanggal'
        ));
    }

    /**
     * Simpan pengajuan tukar jadwal baru.
     */
    public function store(StorePiketSwapRequest $request)
    {
        $guruAsal = auth()->user()->guru;
        if (! $guruAsal) {
            abort(403, 'Akses khusus guru.');
        }

        try {
            $this->swapService->createSwapRequest($guruAsal, $request->validated());

            return redirect()
                ->route('guru.piket.swap.incoming')
                ->with('success', 'Permintaan penggantian guru piket berhasil diajukan.');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Tampilkan daftar permintaan pertukaran masuk & riwayat pengajuan.
     */
    public function incoming(Request $request)
    {
        $guru = auth()->user()->guru;
        if (! $guru) {
            abort(403, 'Akses khusus guru.');
        }

        // Permintaan masuk untuk guru login sebagai guru pengganti
        $incomingRequests = PertukaranJadwalPiket::with(['jadwalPiket', 'guruAsal'])
            ->where('guru_pengganti_id', $guru->id)
            ->latest()
            ->get();

        // Riwayat pengajuan yang dibuat oleh guru login
        $outgoingRequests = PertukaranJadwalPiket::with(['jadwalPiket', 'guruPengganti'])
            ->where('guru_asal_id', $guru->id)
            ->latest()
            ->get();

        return view('guru.piket.swap-incoming', compact(
            'guru',
            'incomingRequests',
            'outgoingRequests'
        ));
    }

    /**
     * Menyetujui permintaan penggantian oleh guru pengganti.
     */
    public function accept(PertukaranJadwalPiket $pertukaran)
    {
        try {
            $this->swapService->acceptSwapRequest($pertukaran, auth()->user());

            return redirect()
                ->back()
                ->with('success', 'Permintaan penggantian guru piket berhasil disetujui.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menolak permintaan penggantian oleh guru pengganti.
     */
    public function reject(Request $request, PertukaranJadwalPiket $pertukaran)
    {
        $catatan = $request->input('catatan');

        try {
            $this->swapService->rejectSwapRequest($pertukaran, auth()->user(), $catatan);

            return redirect()
                ->back()
                ->with('success', 'Permintaan penggantian guru piket berhasil ditolak.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Membatalkan permintaan penggantian oleh guru asal.
     */
    public function cancel(PertukaranJadwalPiket $pertukaran)
    {
        try {
            $this->swapService->cancelSwapRequest($pertukaran, auth()->user());

            return redirect()
                ->back()
                ->with('success', 'Permintaan penggantian guru piket berhasil dibatalkan.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
