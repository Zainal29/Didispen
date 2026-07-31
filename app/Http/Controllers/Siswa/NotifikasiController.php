<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('siswa.notifikasi.index', compact('notifikasi'));
    }

    public function markRead(Notifikasi $notifikasi)
    {
        // Pastikan notifikasi milik user yang login
        if ($notifikasi->user_id !== auth()->id()) {
            abort(403);
        }

        $notifikasi->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead()
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}