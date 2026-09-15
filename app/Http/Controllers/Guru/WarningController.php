<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    /**
     * Kirim peringatan ke siswa yang overdue
     */
    public function sendWarning(Request $request, Dispensasi $dispensasi)
    {
        // Validasi: hanya bisa warning jika status 'keluar' dan overdue
        if ($dispensasi->status !== 'keluar') {
            return back()->with('error', 'Dispensasi ini belum dikonfirmasi keluar.');
        }

        if (! $dispensasi->isOverdue()) {
            return back()->with('error', 'Siswa ini belum terlambat.');
        }

        // Tandai sebagai sudah diberi peringatan
        $dispensasi->markAsWarned();

        // TODO: Di sini nanti tambahkan kode untuk kirim WhatsApp
        // Contoh: WhatsAppService::sendWarning($dispensasi->siswa->no_telepon, $dispensasi);

        return back()->with('success', 'Peringatan berhasil dikirim ke siswa '.$dispensasi->siswa->nama_lengkap);
    }
}
