<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJadwalPiketRequest;
use App\Http\Requests\Admin\UpdateJadwalPiketRequest;
use App\Models\Guru;
use App\Models\JadwalPiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalPiketController extends Controller
{
    /**
     * Tampilkan daftar seluruh jadwal piket guru.
     */
    public function index(Request $request)
    {
        $query = JadwalPiket::with([
            'koordinator',
            'guru' => function ($q) {
                $q->orderBy('nama_lengkap');
            }
        ]);

        // Filter Hari
        if ($request->filled('hari')) {
            $query->where('hari', (int) $request->hari);
        }

        // Filter Status Aktif
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        // Filter Pencarian Nama Guru
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($w) use ($search) {
                $w->whereHas('guru', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('no_telepon', 'like', "%{$search}%");
                })->orWhereHas('koordinator', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('no_telepon', 'like', "%{$search}%");
                })->orWhere('nama_sesi', 'like', "%{$search}%");
            });
        }

        $viewMode = $request->get('view', 'list'); // 'list' atau 'matriks'
        $waService = app(\App\Services\WhatsappMessageService::class);

        if ($viewMode === 'matriks') {
            $allJadwals = (clone $query)->orderBy('hari')->orderBy('jam_mulai')->get();
            $matriksData = $allJadwals->groupBy('hari');
            $jadwals = $query->orderBy('hari')->orderBy('jam_mulai')->paginate(50)->withQueryString();
            return view('admin.jadwal-piket.index', compact('jadwals', 'matriksData', 'viewMode', 'waService'));
        }

        $jadwals = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jadwal-piket.index', compact('jadwals', 'viewMode', 'waService'));
    }

    /**
     * Tampilkan formulir pembuatan jadwal baru.
     */
    public function create()
    {
        $gurus = Guru::where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.jadwal-piket.create', compact('gurus'));
    }

    /**
     * Simpan jadwal piket baru ke database.
     */
    public function store(StoreJadwalPiketRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $jadwal = JadwalPiket::create([
                'hari' => (int) $validated['hari'],
                'nama_sesi' => $validated['nama_sesi'] ?? null,
                'jam_mulai' => strlen($validated['jam_mulai']) === 5 ? $validated['jam_mulai'] . ':00' : $validated['jam_mulai'],
                'jam_selesai' => strlen($validated['jam_selesai']) === 5 ? $validated['jam_selesai'] . ':00' : $validated['jam_selesai'],
                'koordinator_guru_id' => $validated['koordinator_guru_id'] ?? null,
                'tanggal_mulai_berlaku' => $validated['tanggal_mulai_berlaku'],
                'tanggal_selesai_berlaku' => $validated['tanggal_selesai_berlaku'] ?? null,
                'is_active' => true,
            ]);

            // Tugaskan guru piket resmi
            $jadwal->guru()->sync($validated['guru']);
        });

        return redirect()
            ->route('admin.jadwal-piket.index')
            ->with('success', 'Jadwal guru piket berhasil dibuat.');
    }

    /**
     * Tampilkan rincian jadwal piket.
     */
    public function show(JadwalPiket $jadwal)
    {
        $jadwal->load([
            'koordinator',
            'guru' => function ($q) {
                $q->orderBy('nama_lengkap');
            },
            'pertukaranJadwalPikets' => function ($q) {
                $q->with(['guruAsal', 'guruPengganti'])->latest();
            },
        ]);

        $waService = app(\App\Services\WhatsappMessageService::class);
        return view('admin.jadwal-piket.show', compact('jadwal', 'waService'));
    }

    /**
     * Tampilkan formulir edit jadwal piket.
     */
    public function edit(JadwalPiket $jadwal)
    {
        $jadwal->load(['koordinator', 'guru']);

        $gurus = Guru::where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        $selectedGuruIds = $jadwal->guru->pluck('id')->toArray();

        return view('admin.jadwal-piket.edit', compact('jadwal', 'gurus', 'selectedGuruIds'));
    }

    /**
     * Perbarui jadwal piket di database.
     */
    public function update(UpdateJadwalPiketRequest $request, JadwalPiket $jadwal)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($jadwal, $validated) {
            $jadwal->update([
                'hari' => (int) $validated['hari'],
                'nama_sesi' => $validated['nama_sesi'] ?? null,
                'jam_mulai' => strlen($validated['jam_mulai']) === 5 ? $validated['jam_mulai'] . ':00' : $validated['jam_mulai'],
                'jam_selesai' => strlen($validated['jam_selesai']) === 5 ? $validated['jam_selesai'] . ':00' : $validated['jam_selesai'],
                'koordinator_guru_id' => $validated['koordinator_guru_id'] ?? null,
                'tanggal_mulai_berlaku' => $validated['tanggal_mulai_berlaku'],
                'tanggal_selesai_berlaku' => $validated['tanggal_selesai_berlaku'] ?? null,
            ]);

            // Sinkronisasi daftar penugasan guru piket tanpa menghapus record guru
            $jadwal->guru()->sync($validated['guru']);
        });

        return redirect()
            ->route('admin.jadwal-piket.index')
            ->with('success', 'Jadwal guru piket berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / nonaktif jadwal piket.
     */
    public function toggle(JadwalPiket $jadwal)
    {
        $jadwal->update([
            'is_active' => ! $jadwal->is_active,
        ]);

        $statusText = $jadwal->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Jadwal piket berhasil {$statusText}.");
    }
}
