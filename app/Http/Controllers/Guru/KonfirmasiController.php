<?php
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\DispensasiService;

class KonfirmasiController extends Controller
{
    public function __construct(private DispensasiService $service) {}

    public function keluar(Dispensasi $dispensasi)
    {
        $guru = auth()->user()->guru;
        $this->service->konfirmasiKeluar($dispensasi, $guru);
        return redirect()->route('guru.dashboard')->with('success', 'Siswa dikonfirmasi keluar.');
    }

    public function kembali(Dispensasi $dispensasi)
    {
        $guru = auth()->user()->guru;
        $this->service->konfirmasiKembali($dispensasi, $guru);
        return redirect()->route('guru.dashboard')->with('success', 'Siswa dikonfirmasi kembali.');
    }
}
