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

        // 2. ✅ DATA GRAFIK 7 HARI TERAKHIR (1 QUERY SAJA)
         $chartData = Dispensasi::selectRaw('DATE(created_at) as date, count(*) as count')
             ->where('created_at', '>=', now()->subDays(6)->startOfDay())
             ->groupBy('date')
             ->pluck('count', 'date');

         $dates = [];
         $counts = [];
         for ($i = 6; $i >= 0; $i--) {
             $date = now()->subDays($i)->format('Y-m-d');
             $dates[] = \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMM');
             $counts[] = $chartData->get($date, 0); // Ambil dari collection, default 0
         }

         // 3. Data Terbaru (tetap sama)
         $recent = Dispensasi::with(['siswa.user', 'siswa.kelas', 'guru'])->latest()->take(5)->get();

         return view('admin.dashboard', compact('stats', 'dates', 'counts', 'recent'));
     }
}
