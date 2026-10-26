<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Notifikasi;
use App\Helpers\DispensasiTimeHelper;
use App\Services\GuruPiketService;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(GuruPiketService $guruPiketService)
    {
        $siswa = auth()->user()->siswa;
        if (! $siswa) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        // Jalankan auto-expire otomatis
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        // Informasi Guru Piket
        $infoPiket = $guruPiketService->getInformasiSesi();
        $adaJadwalHariIni = false;
        if (empty($infoPiket['jadwal'])) {
            $adaJadwalHariIni = $guruPiketService->getJadwalUntukTanggal()->isNotEmpty();
        }

        // Ambil dispensasi aktif (disetujui atau keluar) baik diajukan sendiri maupun diikuti sebagai anggota
        $dispensasiAktif = Dispensasi::with(['guru', 'siswa.kelas.jurusan', 'anggotaSiswa.kelas.jurusan'])
            ->where(function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id)
                  ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
                      $a->where('siswa.id', $siswa->id);
                  });
            })
            ->whereIn('status', ['disetujui', 'keluar'])
            ->latest()
            ->first();

        $isTerlambat = false;
        $inGracePeriod = false;
        $sisaMenitToleransi = 0;
        $terlambatJam = 0;
        $terlambatMenit = 0;

        if ($dispensasiAktif && $dispensasiAktif->status === 'keluar' && $dispensasiAktif->batas_waktu_kembali) {
            if ($dispensasiAktif->isCurrentlyInGracePeriod()) {
                $inGracePeriod = true;
                $sisaMenitToleransi = $dispensasiAktif->getSisaMenitToleransi();
            } elseif ($dispensasiAktif->isOfficiallyLate()) {
                $isTerlambat = true;
                $totalMenit = DispensasiTimeHelper::hitungMenitTerlambat($dispensasiAktif->batas_waktu_kembali);
                $terlambatJam = floor($totalMenit / 60);
                $terlambatMenit = $totalMenit % 60;
            }
        }

        // Auto-generate QR Code jika status disetujui tapi qr_code masih kosong
        if ($dispensasiAktif && $dispensasiAktif->status === 'disetujui' && empty($dispensasiAktif->qr_code)) {
            if (empty($dispensasiAktif->qr_token)) {
                $dispensasiAktif->qr_token = Str::random(64);
            }

            $qrContent = $dispensasiAktif->qr_token; // Hanya token murni
            $qrCodePath = 'qr_codes/dispensasi_' . $dispensasiAktif->id . '.svg';

            // Buat direktori jika belum ada
            Storage::disk('public')->makeDirectory('qr_codes');

            QrCode::format('svg')
                ->size(300)
                ->margin(0)
                ->generate($qrContent, storage_path('app/public/' . $qrCodePath));

            $dispensasiAktif->qr_code = $qrCodePath;
            $dispensasiAktif->save();
        }

        // Statistik pengajuan
        $statsBase = Dispensasi::where(function ($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id)
              ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
                  $a->where('siswa.id', $siswa->id);
              });
        });

        $stats = [
            'total' => (clone $statsBase)->count(),
            'menunggu' => (clone $statsBase)->where('status', 'menunggu')->count(),
            'disetujui' => (clone $statsBase)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $statsBase)->where('status', 'ditolak')->count(),
            'selesai' => (clone $statsBase)->where('status', 'selesai')->count(),
        ];

        // Total pengajuan yang memiliki catatan keterlambatan
        $totalTerlambat = (clone $statsBase)->terlambat()->count();

        // Pengajuan terbaru (5 terakhir)
        $pengajuanTerbaru = Dispensasi::with(['guru', 'siswa.kelas.jurusan', 'anggotaSiswa'])
            ->where(function ($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id)
                  ->orWhereHas('anggotaSiswa', function ($a) use ($siswa) {
                      $a->where('siswa.id', $siswa->id);
                  });
            })
            ->latest()
            ->take(5)
            ->get();

        // Notifikasi belum dibaca
        $notifikasiBelumDibaca = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        // Fitur: Hubungi Guru Piket jika ada pengajuan berstatus 'menunggu' (Tersedia selama 6 menit)
        $dispensasiMenunggu = Dispensasi::with(['guru', 'siswa.kelas.jurusan'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'menunggu')
            ->latest()
            ->first();

        $piketEligible = false;
        $popupSecondsLeft = 0;
        $hubungiGuru = null;
        $daftarGuruPiket = collect();
        $hubungiError = null;

        if ($dispensasiMenunggu) {
            $expiredAt = $dispensasiMenunggu->getBatasWaktuMenunggu()
                ?? $dispensasiMenunggu->created_at->copy()->addMinutes(Dispensasi::DURASI_KADALUARSA_MENUNGGU_MENIT);
            $now = now('Asia/Jakarta');

            if ($now->lt($expiredAt)) {
                $piketEligible = true;
                $popupSecondsLeft = max(0, $expiredAt->timestamp - $now->timestamp);

                // 1. Ambil seluruh petugas dari sesi piket yang sedang berlangsung saat ini
                try {
                    if (! ($infoPiket['conflict'] ?? false) && ! empty($infoPiket['petugas'])) {
                        foreach ($infoPiket['petugas'] as $p) {
                            $guru = $p['guru'] ?? null;
                            if ($guru && $guru->status_aktif) {
                                $daftarGuruPiket->push([
                                    'guru' => $guru,
                                    'status' => $p['status'] ?? 'Sedang Bertugas',
                                    'is_pengganti' => $p['is_pengganti'] ?? false,
                                ]);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Dashboard Siswa GuruPiketService error: ' . $e->getMessage());
                }

                // 2. Jika tidak ada jadwal/petugas di sesi saat ini, fallback ke guru yang tersimpan di dispensasi
                if ($daftarGuruPiket->isEmpty() && $dispensasiMenunggu->guru && $dispensasiMenunggu->guru->status_aktif) {
                    $daftarGuruPiket->push([
                        'guru' => $dispensasiMenunggu->guru,
                        'status' => 'Guru Terjadwal',
                        'is_pengganti' => false,
                    ]);
                }

                // 3. Fallback Guru Piket dari Setting Admin
                if ($daftarGuruPiket->isEmpty()) {
                    $guruIdFallback = \App\Models\Setting::get('fallback_guru_piket_id')
                        ?? \App\Models\Setting::get('guru_piket_fallback_id');
                    if ($guruIdFallback) {
                        $fallbackGuru = \App\Models\Guru::where('id', $guruIdFallback)
                            ->where('status_aktif', true)
                            ->first();
                        if ($fallbackGuru) {
                            $daftarGuruPiket->push([
                                'guru' => $fallbackGuru,
                                'status' => 'Petugas Piket',
                                'is_pengganti' => false,
                            ]);
                        }
                    }
                }

                // 4. Fallback Guru Piket yang memiliki jadwal hari ini
                if ($daftarGuruPiket->isEmpty()) {
                    try {
                        $jadwalHariIni = $guruPiketService->getJadwalUntukTanggal();
                        foreach ($jadwalHariIni as $j) {
                            if ($j->guru && $j->guru->status_aktif) {
                                $daftarGuruPiket->push([
                                    'guru' => $j->guru,
                                    'status' => 'Guru Piket Hari Ini',
                                    'is_pengganti' => false,
                                ]);
                            }
                        }
                    } catch (\Throwable $e) {}
                }

                // Pastikan unik per ID guru
                $daftarGuruPiket = $daftarGuruPiket->unique(fn($item) => $item['guru']->id)->values();

                if ($daftarGuruPiket->isEmpty()) {
                    $hubungiError = 'Belum ada kontak Guru Piket yang dapat dihubungi saat ini.';
                } else {
                    $hubungiGuru = $daftarGuruPiket->first()['guru'] ?? null;
                }
            }
        }

        return view('siswa.dashboard', compact(
            'stats',
            'pengajuanTerbaru',
            'notifikasiBelumDibaca',
            'dispensasiAktif',
            'isTerlambat',
            'inGracePeriod',
            'sisaMenitToleransi',
            'terlambatJam',
            'terlambatMenit',
            'infoPiket',
            'adaJadwalHariIni',
            'dispensasiMenunggu',
            'piketEligible',
            'popupSecondsLeft',
            'hubungiGuru',
            'daftarGuruPiket',
            'hubungiError',
            'totalTerlambat'
        ));
    }
}
