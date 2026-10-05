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

        // Informasi Guru Piket
        $infoPiket = $guruPiketService->getInformasiSesi();
        $adaJadwalHariIni = false;
        if (empty($infoPiket['jadwal'])) {
            $adaJadwalHariIni = $guruPiketService->getJadwalUntukTanggal()->isNotEmpty();
        }

        // Ambil dispensasi aktif (disetujui atau keluar)
        $dispensasiAktif = Dispensasi::with(['guru', 'siswa.kelas.jurusan'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['disetujui', 'keluar'])
            ->latest()
            ->first();

        $isTerlambat = false;
        $terlambatJam = 0;
        $terlambatMenit = 0;

        if ($dispensasiAktif && $dispensasiAktif->status === 'keluar' && $dispensasiAktif->batas_waktu_kembali) {
            $isTerlambat = $dispensasiAktif->isOverdue();
            if ($isTerlambat) {
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
        $stats = [
            'total' => Dispensasi::where('siswa_id', $siswa->id)->count(),
            'menunggu' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'menunggu')->count(),
            'disetujui' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'disetujui')->count(),
            'ditolak' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'ditolak')->count(),
            'selesai' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'selesai')->count(),
        ];

        // Pengajuan terbaru (5 terakhir)
        $pengajuanTerbaru = Dispensasi::with(['guru'])
            ->where('siswa_id', $siswa->id)
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
        $hubungiError = null;

        if ($dispensasiMenunggu) {
            $expiredAt = $dispensasiMenunggu->created_at->copy()->addMinutes(6);
            $now = now('Asia/Jakarta');

            if ($now->lt($expiredAt)) {
                $piketEligible = true;
                $popupSecondsLeft = max(0, $expiredAt->timestamp - $now->timestamp);

                // 1. Guru yang tersimpan pada dispensasi
                if ($dispensasiMenunggu->guru && $dispensasiMenunggu->guru->status_aktif && !empty($dispensasiMenunggu->guru->no_telepon)) {
                    $hubungiGuru = $dispensasiMenunggu->guru;
                }

                // 2. Info sesi piket saat ini (otomatis menghitung pertukaran shift)
                if (! $hubungiGuru) {
                    try {
                        if (! ($infoPiket['conflict'] ?? false) && ! empty($infoPiket['petugas'])) {
                            $petugas = collect($infoPiket['petugas']);
                            $guruAktif = $petugas->first(function ($p) {
                                return isset($p['guru'])
                                    && $p['guru']
                                    && $p['guru']->status_aktif
                                    && !empty($p['guru']->no_telepon)
                                    && ($p['status'] ?? null) === 'Sedang Bertugas';
                            }) ?? $petugas->first(function ($p) {
                                return isset($p['guru'])
                                    && $p['guru']
                                    && $p['guru']->status_aktif
                                    && !empty($p['guru']->no_telepon);
                            });

                            if ($guruAktif) {
                                $hubungiGuru = $guruAktif['guru'];
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning('Dashboard Siswa GuruPiketService error: ' . $e->getMessage());
                    }
                }

                // 3. Fallback Guru Piket dari Setting Admin
                if (! $hubungiGuru) {
                    $guruIdFallback = \App\Models\Setting::get('fallback_guru_piket_id')
                        ?? \App\Models\Setting::get('guru_piket_fallback_id');
                    if ($guruIdFallback) {
                        $hubungiGuru = \App\Models\Guru::where('id', $guruIdFallback)
                            ->where('status_aktif', true)
                            ->first();
                    }
                }

                // 4. Fallback Guru Piket yang memiliki jadwal hari ini
                if (! $hubungiGuru) {
                    try {
                        $jadwalHariIni = $guruPiketService->getJadwalUntukTanggal();
                        $petugasHariIni = $jadwalHariIni->first(fn($j) => $j->guru && $j->guru->status_aktif && !empty($j->guru->no_telepon));
                        if ($petugasHariIni) {
                            $hubungiGuru = $petugasHariIni->guru;
                        }
                    } catch (\Throwable $e) {}
                }

                // 5. Fallback Guru aktif manapun yang memiliki nomor telepon
                if (! $hubungiGuru) {
                    $hubungiGuru = \App\Models\Guru::where('status_aktif', true)
                        ->whereNotNull('no_telepon')
                        ->where('no_telepon', '!=', '')
                        ->first();
                }

                if (! $hubungiGuru) {
                    $hubungiError = 'Belum ada kontak Guru Piket yang dapat dihubungi saat ini.';
                } elseif (! $hubungiGuru->no_telepon) {
                    $hubungiError = 'Nomor WhatsApp Guru Piket belum tersedia di sistem.';
                }
            }
        }

        return view('siswa.dashboard', compact(
            'stats',
            'pengajuanTerbaru',
            'notifikasiBelumDibaca',
            'dispensasiAktif',
            'isTerlambat',
            'terlambatJam',
            'terlambatMenit',
            'infoPiket',
            'adaJadwalHariIni',
            'dispensasiMenunggu',
            'piketEligible',
            'popupSecondsLeft',
            'hubungiGuru',
            'hubungiError'
        ));
    }
}
