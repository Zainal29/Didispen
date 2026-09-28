<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\JadwalPiket;
use App\Models\PertukaranJadwalPiket;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class GuruPiketService
{
    /**
     * Normalisasi datetime ke Carbon instance dengan timezone Asia/Jakarta
     */
    protected function toCarbon(CarbonInterface|string|null $datetime = null): Carbon
    {
        if ($datetime === null) {
            return now('Asia/Jakarta');
        }

        if ($datetime instanceof Carbon) {
            return $datetime->copy()->setTimezone('Asia/Jakarta');
        }

        if ($datetime instanceof CarbonInterface) {
            return Carbon::instance($datetime)->setTimezone('Asia/Jakarta');
        }

        return Carbon::parse($datetime, 'Asia/Jakarta');
    }

    /**
     * 1. Resolver Jadwal Aktif
     * Menemukan sesi jadwal yang sedang aktif pada datetime tertentu:
     * - hari sesuai (1=Senin s.d. 7=Minggu)
     * - jam_mulai <= waktu < jam_selesai
     * - tanggal >= tanggal_mulai_berlaku
     * - tanggal_selesai_berlaku IS NULL atau tanggal <= tanggal_selesai_berlaku
     * - is_active = true
     */
    public function getJadwalAktif(CarbonInterface|string|null $datetime = null): ?JadwalPiket
    {
        $dt = $this->toCarbon($datetime);
        $hariIso = $dt->dayOfWeekIso; // 1 (Senin) - 7 (Minggu)
        $waktu = $dt->format('H:i:s');
        $tanggal = $dt->toDateString();

        return JadwalPiket::where('is_active', true)
            ->where('hari', $hariIso)
            ->where('jam_mulai', '<=', $waktu)
            ->where('jam_selesai', '>', $waktu)
            ->whereDate('tanggal_mulai_berlaku', '<=', $tanggal)
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_selesai_berlaku')
                    ->orWhereDate('tanggal_selesai_berlaku', '>=', $tanggal);
            })
            ->first();
    }

    /**
     * 2. Resolver Jadwal Berikutnya
     * Menemukan jadwal pada hari yang sama yang belum mulai (jam_mulai > waktu)
     * diurutkan dari jam_mulai terkecil.
     */
    public function getJadwalBerikutnya(CarbonInterface|string|null $datetime = null): ?JadwalPiket
    {
        $dt = $this->toCarbon($datetime);
        $hariIso = $dt->dayOfWeekIso;
        $waktu = $dt->format('H:i:s');
        $tanggal = $dt->toDateString();

        return JadwalPiket::where('is_active', true)
            ->where('hari', $hariIso)
            ->where('jam_mulai', '>', $waktu)
            ->whereDate('tanggal_mulai_berlaku', '<=', $tanggal)
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_selesai_berlaku')
                    ->orWhereDate('tanggal_selesai_berlaku', '>=', $tanggal);
            })
            ->orderBy('jam_mulai', 'asc')
            ->first();
    }

    /**
     * Mengambil seluruh sesi jadwal yang berlaku pada suatu tanggal
     */
    public function getJadwalUntukTanggal(CarbonInterface|string|null $datetime = null): Collection
    {
        $dt = $this->toCarbon($datetime);
        $hariIso = $dt->dayOfWeekIso;
        $tanggal = $dt->toDateString();

        return JadwalPiket::where('is_active', true)
            ->where('hari', $hariIso)
            ->whereDate('tanggal_mulai_berlaku', '<=', $tanggal)
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_selesai_berlaku')
                    ->orWhereDate('tanggal_selesai_berlaku', '>=', $tanggal);
            })
            ->orderBy('jam_mulai', 'asc')
            ->get();
    }

    /**
     * 3. Resolver Guru Resmi
     * Mengambil semua guru resmi yang terdaftar pada jadwal_piket_guru
     */
    public function getGuruResmi(JadwalPiket $jadwal): Collection
    {
        return $jadwal->jadwalPiketGurus()
            ->with('guru.user')
            ->get()
            ->pluck('guru')
            ->filter();
    }

    /**
     * 4. Resolver Petugas Aktual & Conflict Handling
     * Menghitung petugas aktual pada tanggal tertentu berdasarkan guru resmi dan replacement aktif.
     * Jika ada conflict (>= 2 replacement aktif untuk satu guru asal), TIDAK BOLEH memilih otomatis.
     *
     * @return array{
     *     jadwal: JadwalPiket,
     *     tanggal: string,
     *     conflict: bool,
     *     conflict_message: ?string,
     *     petugas: array<int, array{
     *         guru: Guru,
     *         guru_resmi: Guru,
     *         is_pengganti: bool,
     *         replacement_id: ?int
     *     }>
     * }
     */
    public function getPetugasAktual(JadwalPiket $jadwal, CarbonInterface|string|null $datetime = null): array
    {
        $dt = $this->toCarbon($datetime);
        $tanggalStr = $dt->toDateString();

        // 1. Ambil guru resmi dari master jadwal_piket_guru
        $guruResmiList = $this->getGuruResmi($jadwal);

        // 2. Ambil replacement yang disetujui & aktif untuk jadwal dan tanggal ini
        $activeReplacements = PertukaranJadwalPiket::where('jadwal_piket_id', $jadwal->id)
            ->whereDate('tanggal', $tanggalStr)
            ->where('status', 'disetujui')
            ->where('is_active', true)
            ->with(['guruAsal.user', 'guruPengganti.user'])
            ->get();

        // 3. Deteksi Conflict: Jika ada guru_asal_id yang memiliki lebih dari satu replacement aktif
        $grouped = $activeReplacements->groupBy('guru_asal_id');
        foreach ($grouped as $guruAsalId => $replacements) {
            if ($replacements->count() > 1) {
                return [
                    'jadwal' => $jadwal,
                    'tanggal' => $tanggalStr,
                    'conflict' => true,
                    'conflict_message' => "Conflict: Terdapat {$replacements->count()} replacement aktif untuk guru asal ID {$guruAsalId} pada tanggal {$tanggalStr}.",
                    'petugas' => [],
                ];
            }
        }

        // 4. Hitung Petugas Aktual
        $petugasResult = [];

        foreach ($guruResmiList as $guruResmi) {
            $replacement = $activeReplacements->firstWhere('guru_asal_id', $guruResmi->id);

            // Validasi: Guru pengganti harus ada DAN berstatus aktif (status_aktif === true)
            if ($replacement && $replacement->guruPengganti && $replacement->guruPengganti->status_aktif) {
                // Guru asal digantikan oleh guru pengganti aktif
                $petugasResult[] = [
                    'guru' => $replacement->guruPengganti,
                    'guru_resmi' => $guruResmi,
                    'is_pengganti' => true,
                    'replacement_id' => $replacement->id,
                ];
            } else {
                // Guru resmi tetap bertugas (jika tidak ada replacement ATAU guru pengganti nonaktif)
                $petugasResult[] = [
                    'guru' => $guruResmi,
                    'guru_resmi' => $guruResmi,
                    'is_pengganti' => false,
                    'replacement_id' => null,
                ];
            }
        }

        return [
            'jadwal' => $jadwal,
            'tanggal' => $tanggalStr,
            'conflict' => false,
            'conflict_message' => null,
            'petugas' => $petugasResult,
        ];
    }

    /**
     * 5. Resolver Status per Petugas
     * Menghitung status spesifik petugas aktual:
     * - Sebelum jam mulai sesi -> 'Akan Bertugas'
     * - Setelah jam selesai sesi -> 'Sesi Selesai'
     * - Saat sesi berlangsung:
     *     - Cek GuruChecklog yang relevan dengan periode sesi:
     *         - Terdapat status = 'keluar' aktif -> 'Sedang Keluar'
     *         - Terdapat status = 'selesai' yang relevan dengan sesi ini -> 'Sudah Kembali'
     *         - Tanpa checklog relevan pada sesi ini -> 'Sedang Bertugas'
     */
    public function resolveStatusPetugas(Guru $guru, JadwalPiket $jadwal, CarbonInterface|string|null $datetime = null): string
    {
        $dt = $this->toCarbon($datetime);
        $waktu = $dt->format('H:i:s');
        $tanggal = $dt->toDateString();

        $jamMulai = substr((string) $jadwal->jam_mulai, 0, 8);
        $jamSelesai = substr((string) $jadwal->jam_selesai, 0, 8);

        // Sebelum jam mulai sesi
        if ($waktu < $jamMulai) {
            return 'Akan Bertugas';
        }

        // Setelah jam selesai sesi
        if ($waktu >= $jamSelesai) {
            return 'Sesi Selesai';
        }

        // Rentang waktu sesi piket yang sedang dihitung
        $sesiMulai = Carbon::parse("{$tanggal} {$jamMulai}", 'Asia/Jakarta');
        $sesiSelesai = Carbon::parse("{$tanggal} {$jamSelesai}", 'Asia/Jakarta');

        // 1. Cek apakah ada checklog dengan status 'keluar' aktif yang relevan dengan sesi ini
        $sedangKeluar = GuruChecklog::where('guru_id', $guru->id)
            ->where('status', 'keluar')
            ->whereDate('jam_keluar', $tanggal)
            ->where('jam_keluar', '<', $sesiSelesai)
            ->exists();

        if ($sedangKeluar) {
            return 'Sedang Keluar';
        }

        // 2. Cek apakah ada checklog yang relevan dengan periode sesi ini yang sudah selesai (status 'selesai')
        // Relevan jika jam_keluar terjadi di dalam sesi piket ATAU jam_kembali terjadi di dalam sesi piket
        $sudahKembali = GuruChecklog::where('guru_id', $guru->id)
            ->where('status', 'selesai')
            ->where(function ($q) use ($sesiMulai, $sesiSelesai) {
                $q->where(function ($sub) use ($sesiMulai, $sesiSelesai) {
                    $sub->where('jam_keluar', '>=', $sesiMulai)
                        ->where('jam_keluar', '<', $sesiSelesai);
                })->orWhere(function ($sub) use ($sesiMulai, $sesiSelesai) {
                    $sub->where('jam_kembali', '>=', $sesiMulai)
                        ->where('jam_kembali', '<=', $sesiSelesai);
                });
            })
            ->exists();

        if ($sudahKembali) {
            return 'Sudah Kembali';
        }

        // 3. Tidak ada checklog relevan pada sesi piket ini
        return 'Sedang Bertugas';
    }

    /**
     * 6. Resolver Lengkap Sesi Saat Ini / Datetime Tertentu
     * Menggabungkan jadwal, petugas aktual, dan status per petugas.
     */
    public function getInformasiSesi(CarbonInterface|string|null $datetime = null): array
    {
        $dt = $this->toCarbon($datetime);

        // 1. Cari jadwal yang sedang aktif
        $jadwal = $this->getJadwalAktif($dt);
        $isJadwalBerikutnya = false;

        // 2. Jika tidak ada jadwal aktif saat ini, cari jadwal berikutnya
        if (! $jadwal) {
            $jadwal = $this->getJadwalBerikutnya($dt);
            $isJadwalBerikutnya = (bool) $jadwal;
        }

        if (! $jadwal) {
            return [
                'jadwal' => null,
                'status_sesi' => 'Tidak Ada Sesi',
                'conflict' => false,
                'conflict_message' => null,
                'petugas' => [],
            ];
        }

        // 3. Resolve petugas aktual
        $petugasData = $this->getPetugasAktual($jadwal, $dt);

        if ($petugasData['conflict']) {
            return [
                'jadwal' => $jadwal,
                'status_sesi' => 'Conflict',
                'conflict' => true,
                'conflict_message' => $petugasData['conflict_message'],
                'petugas' => [],
            ];
        }

        // 4. Hitung status masing-masing petugas
        $petugasDenganStatus = [];
        foreach ($petugasData['petugas'] as $p) {
            $status = $this->resolveStatusPetugas($p['guru'], $jadwal, $dt);

            $petugasDenganStatus[] = [
                'guru' => $p['guru'],
                'guru_resmi' => $p['guru_resmi'],
                'is_pengganti' => $p['is_pengganti'],
                'replacement_id' => $p['replacement_id'],
                'status' => $status,
            ];
        }

        return [
            'jadwal' => $jadwal,
            'status_sesi' => $isJadwalBerikutnya ? 'Akan Datang' : 'Berlangsung',
            'conflict' => false,
            'conflict_message' => null,
            'petugas' => $petugasDenganStatus,
        ];
    }
}
