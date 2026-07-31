<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // 1. Statistik Kartu
        $stats = [
            'menunggu' => Dispensasi::where('status', 'menunggu')->count(),
            'disetujui' => Dispensasi::where('status', 'disetujui')->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->count(),
            'ditolak' => Dispensasi::where('status', 'ditolak')->count(),
            'total_siswa' => Siswa::count(),
            'total_guru' => Guru::count(),
        ];

        // 2. Data Grafik 7 Hari Terakhir
        $dates = [];
        $counts = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($date)->isoFormat('dddd, D MMM'); // Contoh: "Senin, 29 Jul"
            
            $counts[] = Dispensasi::whereDate('created_at', $date)->count();
        }

        // 3. Data Pengajuan Terbaru (5 terakhir)
        $recent = Dispensasi::with(['siswa.user', 'siswa.kelas', 'guruPiket.guru'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'dates', 'counts', 'recent'));
    }
}