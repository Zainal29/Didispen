<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Statistik Dashboard
        |--------------------------------------------------------------------------
        */

        $now = now();

        $stats = [
            'menunggu' => Dispensasi::where('status', 'menunggu')->count(),

            'disetujui' => Dispensasi::where('status', 'disetujui')->count(),

            // Siswa yang sedang berada di luar
            'keluar' => Dispensasi::where('status', 'keluar')->count(),

            'selesai' => Dispensasi::where('status', 'selesai')->count(),

            // Terlambat: status masih 'keluar' tetapi melewati batas_waktu_kembali
            'terlambat' => Dispensasi::where(function ($query) {
                $query->where('status', 'keluar')
                    ->whereNotNull('batas_waktu_kembali')
                    ->where('batas_waktu_kembali', '<', now());
            })->orWhere('status', 'terlambat')->count(),

            'ditolak' => Dispensasi::where('status', 'ditolak')->count(),

            'dibatalkan' => Dispensasi::where('status', 'dibatalkan')->count(),

            // Statistik master data
            'total_siswa' => Siswa::count(),

            'total_guru' => Guru::count(),

            'total_satpam' => User::where('role', 'satpam')->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | 2. Data Grafik 7 Hari Terakhir
        |--------------------------------------------------------------------------
        */

        $chartData = Dispensasi::selectRaw(
            'DATE(created_at) as date, COUNT(*) as count'
        )
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            )
            ->groupBy('date')
            ->pluck('count', 'date');


        $dates = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = now()
                ->subDays($i)
                ->format('Y-m-d');

            $dates[] = Carbon::parse($date)
                ->locale('id')
                ->isoFormat('dddd, D MMM');

            $counts[] = (int) $chartData->get($date, 0);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Pengajuan Terbaru
        |--------------------------------------------------------------------------
        */

        $recent = Dispensasi::with([
            'siswa.user',
            'siswa.kelas',
            'guru',
        ])
            ->latest()
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 4. Kirim ke Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.dashboard',
            compact(
                'stats',
                'dates',
                'counts',
                'recent'
            )
        );
    }
}