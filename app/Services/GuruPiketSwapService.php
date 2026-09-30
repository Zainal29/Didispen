<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\PertukaranJadwalPiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class GuruPiketSwapService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Membuat pengajuan tukar jadwal / penggantian piket oleh Guru Asal.
     *
     * @throws ValidationException|InvalidArgumentException
     */
    public function createSwapRequest(Guru $guruAsal, array $data): PertukaranJadwalPiket
    {
        $jadwalId = (int) ($data['jadwal_piket_id'] ?? 0);
        $guruPenggantiId = (int) ($data['guru_pengganti_id'] ?? 0);
        $tanggalRaw = $data['tanggal'] ?? null;
        $alasan = $data['alasan'] ?? null;

        // 1. Cek Jadwal Piket
        $jadwal = JadwalPiket::find($jadwalId);
        if (! $jadwal || ! $jadwal->is_active) {
            throw ValidationException::withMessages([
                'jadwal_piket_id' => 'Jadwal piket tidak ditemukan atau tidak aktif.',
            ]);
        }

        // 2. Guru Asal harus anggota resmi jadwal piket
        if (! $jadwal->isAnggotaJadwal($guruAsal->id)) {
            throw ValidationException::withMessages([
                'jadwal_piket_id' => 'Guru asal bukan merupakan anggota resmi pada jadwal piket yang dipilih.',
            ]);
        }

        // 3. Guru Pengganti tidak boleh sama dengan Guru Asal
        if ($guruPenggantiId === $guruAsal->id) {
            throw ValidationException::withMessages([
                'guru_pengganti_id' => 'Guru pengganti tidak boleh sama dengan guru asal.',
            ]);
        }

        // 4. Guru Pengganti harus ada di data guru.
// Tidak harus memiliki jadwal piket dan tidak harus berstatus aktif.
$guruPengganti = Guru::find($guruPenggantiId);

if (! $guruPengganti) {
    throw ValidationException::withMessages([
        'guru_pengganti_id' => 'Guru pengganti tidak ditemukan.',
    ]);
}

        // 5. Validasi Tanggal
        if (! $tanggalRaw) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal tugas piket wajib diisi.',
            ]);
        }

        try {
            $tanggal = Carbon::parse($tanggalRaw)->startOfDay();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'tanggal' => 'Format tanggal tidak valid.',
            ]);
        }

        // Hari tanggal harus cocok dengan hari jadwal (ISO: 1 = Senin, ..., 7 = Minggu)
        if ($tanggal->dayOfWeekIso !== (int) $jadwal->hari) {
            throw ValidationException::withMessages([
                'tanggal' => 'Hari pada tanggal yang dipilih tidak sesuai dengan hari jadwal piket ini.',
            ]);
        }

        // Tanggal harus berada dalam rentang masa berlaku jadwal
        $mulaiBerlaku = Carbon::parse($jadwal->tanggal_mulai_berlaku)->startOfDay();
        $selesaiBerlaku = Carbon::parse($jadwal->tanggal_selesai_berlaku)->endOfDay();

        if ($tanggal->lt($mulaiBerlaku) || $tanggal->gt($selesaiBerlaku)) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal berada di luar masa berlaku jadwal piket.',
            ]);
        }

        $tanggalStr = $tanggal->toDateString();

        // 6. Cek Concurrency / Duplicate Request: Tidak boleh ada request yang sedang 'menunggu' atau 'disetujui' & active
        $existing = PertukaranJadwalPiket::where('jadwal_piket_id', $jadwal->id)
            ->where('guru_asal_id', $guruAsal->id)
            ->whereDate('tanggal', $tanggalStr)
            ->where(function ($q) {
                $q->where('status', 'menunggu')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'disetujui')
                          ->where('is_active', true);
                  });
            })
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'jadwal_piket_id' => 'Sudah terdapat pengajuan pertukaran aktif atau sedang menunggu persetujuan untuk sesi jadwal ini.',
            ]);
        }

        // 7. Simpan Pengajuan
        return DB::transaction(function () use ($jadwal, $guruAsal, $guruPengganti, $tanggalStr, $alasan) {
            $swap = PertukaranJadwalPiket::create([
                'jadwal_piket_id' => $jadwal->id,
                'guru_asal_id' => $guruAsal->id,
                'guru_pengganti_id' => $guruPengganti->id,
                'tanggal' => $tanggalStr,
                'alasan' => $alasan,
                'status' => 'menunggu',
                'is_active' => false,
                'diminta_at' => now(),
                'diproses_at' => null,
                'diproses_oleh' => null,
                'catatan' => null,
            ]);

            // Audit log
            $userId = auth()->id() ?? $guruAsal->user_id;
            $this->auditLogService->log(
                $userId,
                'guru_piket_swap_requested',
                'pertukaran_jadwal_piket',
                $swap->id,
                null,
                $swap->toArray()
            );

            return $swap;
        });
    }

    /**
     * Guru Pengganti menyetujui pengajuan pertukaran jadwal.
     *
     * @throws ValidationException|RuntimeException
     */
    public function acceptSwapRequest(PertukaranJadwalPiket $swap, User $user): PertukaranJadwalPiket
    {
        // 1. Authorization check: User harus Guru Pengganti atau Admin
        $isPengganti = $user->guru && $user->guru->id === $swap->guru_pengganti_id;
        $isAdmin = $user->isAdmin();

        if (! $isPengganti && ! $isAdmin) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk menyetujui pengajuan ini.',
            ]);
        }

        // 2. Status harus menunggu
        if ($swap->status !== 'menunggu') {
            throw ValidationException::withMessages([
                'status' => 'Hanya pengajuan dengan status menunggu yang dapat disetujui.',
            ]);
        }

        // 3. Guru Pengganti harus masih aktif
        $guruPengganti = $swap->guruPengganti;
        if (! $guruPengganti || ! $guruPengganti->status_aktif) {
            throw ValidationException::withMessages([
                'guru_pengganti' => 'Guru pengganti tidak aktif sehingga pengajuan tidak dapat disetujui.',
            ]);
        }

        return DB::transaction(function () use ($swap, $user) {
            // Lock and check conflict: Pastikan belum ada replacement aktif lain untuk slot yang sama
            $activeCount = PertukaranJadwalPiket::where('jadwal_piket_id', $swap->jadwal_piket_id)
                ->where('guru_asal_id', $swap->guru_asal_id)
                ->whereDate('tanggal', $swap->tanggal->toDateString())
                ->where('id', '!=', $swap->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->count();

            if ($activeCount > 0) {
                throw new RuntimeException("Conflict: Sudah terdapat replacement aktif lain untuk jadwal ini.");
            }

            $old = $swap->toArray();

            $swap->update([
                'status' => 'disetujui',
                'is_active' => true,
                'diproses_oleh' => $user->id,
                'diproses_at' => now(),
            ]);

            // Audit log
            $this->auditLogService->log(
                $user->id,
                'guru_piket_swap_approved',
                'pertukaran_jadwal_piket',
                $swap->id,
                $old,
                $swap->fresh()->toArray()
            );

            return $swap;
        });
    }

    /**
     * Guru Pengganti menolak pengajuan pertukaran jadwal.
     *
     * @throws ValidationException
     */
    public function rejectSwapRequest(PertukaranJadwalPiket $swap, User $user, ?string $catatan = null): PertukaranJadwalPiket
    {
        // 1. Authorization check: User harus Guru Pengganti atau Admin
        $isPengganti = $user->guru && $user->guru->id === $swap->guru_pengganti_id;
        $isAdmin = $user->isAdmin();

        if (! $isPengganti && ! $isAdmin) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk menolak pengajuan ini.',
            ]);
        }

        // 2. Status harus menunggu
        if ($swap->status !== 'menunggu') {
            throw ValidationException::withMessages([
                'status' => 'Hanya pengajuan dengan status menunggu yang dapat ditolak.',
            ]);
        }

        return DB::transaction(function () use ($swap, $user, $catatan) {
            $old = $swap->toArray();

            $swap->update([
                'status' => 'ditolak',
                'is_active' => false,
                'diproses_oleh' => $user->id,
                'diproses_at' => now(),
                'catatan' => $catatan,
            ]);

            // Audit log
            $this->auditLogService->log(
                $user->id,
                'guru_piket_swap_rejected',
                'pertukaran_jadwal_piket',
                $swap->id,
                $old,
                $swap->fresh()->toArray()
            );

            return $swap;
        });
    }

    /**
     * Guru Asal membatalkan pengajuan pertukaran jadwal yang masih berstatus menunggu.
     *
     * @throws ValidationException
     */
    public function cancelSwapRequest(PertukaranJadwalPiket $swap, User $user): PertukaranJadwalPiket
    {
        // 1. Authorization check: User harus Guru Asal atau Admin
        $isAsal = $user->guru && $user->guru->id === $swap->guru_asal_id;
        $isAdmin = $user->isAdmin();

        if (! $isAsal && ! $isAdmin) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki hak akses untuk membatalkan pengajuan ini.',
            ]);
        }

        // 2. Status harus menunggu
        if ($swap->status !== 'menunggu') {
            throw ValidationException::withMessages([
                'status' => 'Hanya pengajuan dengan status menunggu yang dapat dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($swap, $user) {
            $old = $swap->toArray();

            $swap->update([
                'status' => 'dibatalkan',
                'is_active' => false,
                'diproses_oleh' => $user->id,
                'diproses_at' => now(),
            ]);

            // Audit log
            $this->auditLogService->log(
                $user->id,
                'guru_piket_swap_cancelled',
                'pertukaran_jadwal_piket',
                $swap->id,
                $old,
                $swap->fresh()->toArray()
            );

            return $swap;
        });
    }
}
