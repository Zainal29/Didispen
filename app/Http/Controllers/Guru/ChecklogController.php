<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruChecklog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ChecklogController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index()
    {
        $guruId = auth()->user()->guru->id;
        
        // Cek apakah sedang ada log yang statusnya 'keluar' (belum kembali)
        $sedangKeluar = GuruChecklog::where('guru_id', $guruId)
            ->where('status', 'keluar')
            ->latest()
            ->first();

        // Riwayat 10 terakhir
        $riwayat = GuruChecklog::where('guru_id', $guruId)
            ->latest()
            ->take(10)
            ->get();

        return view('guru.checklog.index', compact('sedangKeluar', 'riwayat'));
    }

    /**
     * Guru mencatat diri keluar
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'alasan' => 'required|string|min:5',
            'tujuan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
        ]);

        $log = GuruChecklog::create([
            'guru_id' => auth()->user()->guru->id,
            'alasan' => $data['alasan'],
            'tujuan' => $data['tujuan'],
            'lokasi' => $data['lokasi'],
            'jam_keluar' => now(), // Waktu otomatis saat tombol ditekan
            'status' => 'keluar',
        ]);

        $this->auditLog->log(auth()->id(), 'guru_check_out', 'guru_checklog', $log->id);

        return redirect()->route('guru.checklog.index')->with('success', 'Anda berhasil dicatat keluar. Hati-hati di jalan!');
    }

    /**
     * Guru mencatat diri kembali
     */
    public function checkIn(GuruChecklog $log)
    {
        // Pastikan yang di-checkin adalah milik guru yang login dan masih status 'keluar'
        if ($log->guru_id !== auth()->user()->guru->id || $log->status !== 'keluar') {
            abort(403, 'Aksi tidak valid.');
        }

        $log->update([
            'jam_kembali' => now(), // Waktu otomatis saat tombol ditekan
            'status' => 'selesai',
        ]);

        $this->auditLog->log(auth()->id(), 'guru_check_in', 'guru_checklog', $log->id);

        return redirect()->route('guru.checklog.index')->with('success', 'Anda berhasil dicatat kembali. Terima kasih.');
    }
}