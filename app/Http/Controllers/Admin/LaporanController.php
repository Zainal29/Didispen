<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Membangun query laporan berdasarkan parameter filter.
     */
    protected function buildQuery(Request $request)
    {
        $query = Dispensasi::with([
            'siswa.user',
            'siswa.kelas.jurusan',
            'guru',
            'petugasKeluar',
            'petugasKembali',
        ]);

        // 1. Filter Status Realisasi & Spesifik
        $status = $request->query('status');

        // Default jika kosong atau 'realisasi':
        // Menampilkan seluruh siswa yang benar-benar keluar gerbang sekolah:
        // - Sedang keluar
        // - Selesai (kembali lebih awal, tepat waktu, terlambat, sampai pulang)
        // - Kadaluarsa tetapi sempat keluar (tidak scan kembali)
        if (empty($status) || $status === 'realisasi') {
            $query->where(function ($q) {
                $q->whereIn('status', ['keluar', 'selesai'])
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'kadaluarsa')
                            ->whereNotNull('waktu_keluar_aktual')
                            ->whereNull('waktu_kembali_aktual');
                    });
            });
        } elseif ($status === 'keluar') {
            $query->where('status', 'keluar');
        } elseif ($status === 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($status === 'selesai_awal') {
            $query->where('status', 'selesai')
                ->where('sampai_pulang', false)
                ->whereNotNull('waktu_kembali_aktual')
                ->whereNotNull('batas_waktu_kembali')
                ->whereColumn('waktu_kembali_aktual', '<=', 'batas_waktu_kembali');
        } elseif ($status === 'terlambat') {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', 'selesai')
                        ->where('sampai_pulang', false)
                        ->where(function ($s) {
                            $s->where('is_warned', true)
                                ->orWhere(function ($s2) {
                                    $s2->whereNotNull('waktu_kembali_aktual')
                                        ->whereNotNull('batas_waktu_kembali')
                                        ->whereColumn('waktu_kembali_aktual', '>', 'batas_waktu_kembali');
                                });
                        });
                })->orWhere(function ($sub) {
                    $sub->where('status', 'keluar')
                        ->where('sampai_pulang', false)
                        ->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now());
                });
            });
        } elseif ($status === 'tidak_kembali') {
            $query->where('status', 'kadaluarsa')
                ->whereNotNull('waktu_keluar_aktual')
                ->whereNull('waktu_kembali_aktual');
        } elseif ($status === 'tidak_scan_keluar') {
            $query->where('status', 'kadaluarsa')
                ->whereNull('waktu_keluar_aktual');
        } elseif ($status === 'sampai_pulang') {
            $query->where('sampai_pulang', true)
                ->whereIn('status', ['keluar', 'selesai']);
        } elseif ($status === 'semua') {
            // Tampilkan seluruh riwayat tanpa filter status (Audit Lengkap)
        } else {
            // Status baku lainnya (menunggu, disetujui, ditolak, dibatalkan, kadaluarsa)
            $query->where('status', $status);
        }

        // 2. Filter Tanggal Dari
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        // 3. Filter Tanggal Sampai
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // 4. Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }

        // 5. Filter Jurusan
        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas.jurusan', function ($q) use ($request) {
                $q->where('id', $request->jurusan_id);
            });
        }

        return $query;
    }

    /**
     * Dapatkan label deskripsi dari status yang sedang difilter.
     */
    protected function getStatusLabel(?string $status): string
    {
        return match ($status) {
            'keluar' => 'Sedang Keluar (Belum Kembali)',
            'selesai' => 'Sudah Selesai (Semua)',
            'selesai_awal' => 'Selesai (Kembali Lebih Awal / Tepat Waktu)',
            'terlambat' => 'Selesai (Terlambat Kembali)',
            'tidak_kembali' => 'Kadaluarsa (Tidak Scan Kembali)',
            'tidak_scan_keluar' => 'Kadaluarsa (Tidak Scan Keluar / Indikasi Batal atau Terobos)',
            'sampai_pulang' => 'Selesai (Izin Sampai Pulang)',
            'semua' => 'Semua Riwayat (Lengkap Termasuk Batal & Ditolak)',
            'menunggu' => 'Menunggu Persetujuan Guru Piket',
            'disetujui' => 'Disetujui Guru Piket (Belum Keluar)',
            'ditolak' => 'Ditolak Guru Piket',
            'dibatalkan' => 'Dibatalkan',
            'kadaluarsa' => 'Semua Kadaluarsa',
            default => 'Semua Realisasi Siswa Keluar',
        };
    }

    public function index(Request $request)
    {
        $currentStatus = $request->query('status', 'realisasi');
        $statusLabel = $this->getStatusLabel($currentStatus);

        // Ambil data tabel dengan paginasi
        $query = $this->buildQuery($request);
        $dispensasi = $query->latest('created_at')->paginate(20)->withQueryString();

        // Hitung statistik untuk quick filter pills berdasarkan filter tanggal & kelas yang sedang aktif
        $baseQuery = Dispensasi::query();
        if ($request->filled('tanggal_dari')) {
            $baseQuery->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $baseQuery->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kelas_id')) {
            $baseQuery->whereHas('siswa.kelas', fn ($q) => $q->where('id', $request->kelas_id));
        }
        if ($request->filled('jurusan_id')) {
            $baseQuery->whereHas('siswa.kelas.jurusan', fn ($q) => $q->where('id', $request->jurusan_id));
        }

        $counts = [
            'realisasi' => (clone $baseQuery)->where(function ($q) {
                $q->whereIn('status', ['keluar', 'selesai'])
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'kadaluarsa')
                            ->whereNotNull('waktu_keluar_aktual')
                            ->whereNull('waktu_kembali_aktual');
                    });
            })->count(),
            'keluar' => (clone $baseQuery)->where('status', 'keluar')->count(),
            'selesai_awal' => (clone $baseQuery)->where('status', 'selesai')
                ->where('sampai_pulang', false)
                ->whereNotNull('waktu_kembali_aktual')
                ->whereNotNull('batas_waktu_kembali')
                ->whereColumn('waktu_kembali_aktual', '<=', 'batas_waktu_kembali')->count(),
            'terlambat' => (clone $baseQuery)->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', 'selesai')
                        ->where('sampai_pulang', false)
                        ->where(function ($s) {
                            $s->where('is_warned', true)
                                ->orWhere(function ($s2) {
                                    $s2->whereNotNull('waktu_kembali_aktual')
                                        ->whereNotNull('batas_waktu_kembali')
                                        ->whereColumn('waktu_kembali_aktual', '>', 'batas_waktu_kembali');
                                });
                        });
                })->orWhere(function ($sub) {
                    $sub->where('status', 'keluar')
                        ->where('sampai_pulang', false)
                        ->whereNotNull('batas_waktu_kembali')
                        ->where('batas_waktu_kembali', '<', now());
                });
            })->count(),
            'tidak_kembali' => (clone $baseQuery)->where('status', 'kadaluarsa')
                ->whereNotNull('waktu_keluar_aktual')
                ->whereNull('waktu_kembali_aktual')->count(),
            'tidak_scan_keluar' => (clone $baseQuery)->where('status', 'kadaluarsa')
                ->whereNull('waktu_keluar_aktual')->count(),
            'sampai_pulang' => (clone $baseQuery)->where('sampai_pulang', true)
                ->whereIn('status', ['keluar', 'selesai'])->count(),
            'semua' => (clone $baseQuery)->count(),
        ];

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.laporan.index', compact(
            'dispensasi',
            'currentStatus',
            'statusLabel',
            'counts',
            'kelasList',
            'jurusanList'
        ));
    }

    public function exportPdf(Request $request)
    {
        $currentStatus = $request->query('status', 'realisasi');
        $statusLabel = $this->getStatusLabel($currentStatus);

        // Batasi maksimal 1.000 record untuk menjaga kestabilan memori DomPDF
        $dispensasi = $this->buildQuery($request)->latest('created_at')->limit(1000)->get();

        $pdf = Pdf::loadView('pdf.laporan-dispensasi', compact('dispensasi', 'statusLabel', 'currentStatus'))
            ->setPaper('a4', 'landscape');

        $filename = 'Laporan_Dispensasi_' . str_replace(' ', '_', $currentStatus) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $currentStatus = $request->query('status', 'realisasi');
        $statusLabel = $this->getStatusLabel($currentStatus);

        $dispensasi = $this->buildQuery($request)->latest('created_at')->get();

        $filename = 'Laporan_Dispensasi_' . str_replace(' ', '_', $currentStatus) . '_' . now()->format('Ymd_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function () use ($dispensasi, $statusLabel) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '<style>
                table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11px; }
                th, td { border: 1px solid #000000; padding: 6px; text-align: left; }
                th { background-color: #2563eb; color: #ffffff; font-weight: bold; text-align: center; }
                .text-center { text-align: center; }
                .title { font-size: 14px; font-weight: bold; text-align: center; }
                .subtitle { font-size: 11px; text-align: center; color: #475569; margin-bottom: 12px; }
                .badge-late { color: #dc2626; font-weight: bold; }
                .badge-early { color: #059669; font-weight: bold; }
            </style>';
            echo '</head><body>';

            echo '<div class="title">LAPORAN REKAPITULASI DISPENSASI SISWA</div>';
            echo '<div class="subtitle">Kategori: ' . htmlspecialchars($statusLabel) . ' | Dicetak: ' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB</div>';

            echo '<table>';
            echo '<thead><tr>';
            echo '<th width="3%">No</th>';
            echo '<th width="10%">No. Surat</th>';
            echo '<th width="8%">Tanggal</th>';
            echo '<th width="8%">NIS</th>';
            echo '<th width="14%">Nama Siswa</th>';
            echo '<th width="8%">Kelas</th>';
            echo '<th width="9%">Jurusan</th>';
            echo '<th width="8%">Kategori</th>';
            echo '<th width="12%">Tujuan & Alasan</th>';
            echo '<th width="7%">Jam Rencana</th>';
            echo '<th width="8%">Scan Keluar</th>';
            echo '<th width="8%">Scan Kembali</th>';
            echo '<th width="12%">Status Realisasi</th>';
            echo '<th width="10%">Guru Piket</th>';
            echo '</tr></thead><tbody>';

            $no = 1;
            foreach ($dispensasi as $row) {
                $scanKeluarText = $row->waktu_keluar_aktual
                    ? \Carbon\Carbon::parse($row->waktu_keluar_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                    : '-';
                $scanKembaliText = $row->waktu_kembali_aktual
                    ? \Carbon\Carbon::parse($row->waktu_kembali_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                    : ($row->sampai_pulang ? 'Sampai Pulang' : '-');

                $jamRencana = $row->jam_keluar . ($row->sampai_pulang ? ' s/d Pulang' : ' - ' . $row->jam_kembali);

                echo '<tr>';
                echo '<td class="text-center">' . $no++ . '</td>';
                echo '<td>' . htmlspecialchars($row->nomor_surat) . '</td>';
                echo '<td class="text-center">' . ($row->created_at ? $row->created_at->format('d/m/Y') : '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->user->nis_nip ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row->siswa->nama_lengkap ?? '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->kelas->nama_kelas ?? '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->siswa->kelas->jurusan->nama_jurusan ?? '-') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $row->kategori))) . '</td>';
                echo '<td>' . htmlspecialchars($row->tujuan . ' - ' . $row->alasan) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($jamRencana) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($scanKeluarText) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($scanKembaliText) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($row->status_badge['text']) . '</td>';
                echo '<td>' . htmlspecialchars($row->guru->nama_lengkap ?? '-') . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
