<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // 1. Filter Tipe User / Aksi
        if ($request->filled('filter_type')) {
            if ($request->filter_type === 'admin') {
                $query->whereHas('user', fn($q) => $q->where('role', 'admin'));
            } elseif ($request->filter_type === 'satpam') {
                $query->whereHas('user', fn($q) => $q->where('role', 'satpam'));
            } elseif ($request->filter_type === 'sync') {
                $query->where('action', 'like', '%sync%');
            }
        }

        // 2. Filter Aksi Spesifik
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 3. ✅ BARU: Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. ✅ BARU: Pencarian Keyword (Nama User atau IP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Ambil data untuk tabel (dengan paginasi)
        $logs = $query->latest()->paginate(20);

        // ✅ PERBAIKAN KRITIS: Hitung statistik dari SELURUH database, bukan dari $logs yang sudah dipaginasi
        $statsQuery = AuditLog::query();

        // Terapkan filter yang sama ke stats query agar statistik relevan dengan filter yang dipilih
        if ($request->filled('filter_type')) {
            if ($request->filter_type === 'admin') $statsQuery->whereHas('user', fn($q) => $q->where('role', 'admin'));
            elseif ($request->filter_type === 'satpam') $statsQuery->whereHas('user', fn($q) => $q->where('role', 'satpam'));
            elseif ($request->filter_type === 'sync') $statsQuery->where('action', 'like', '%sync%');
        }
        if ($request->filled('action')) $statsQuery->where('action', $request->action);
        if ($request->filled('date_from')) $statsQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $statsQuery->whereDate('created_at', '<=', $request->date_to);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'sync' => (clone $statsQuery)->where('action', 'like', '%sync%')->count(),
            'admin' => (clone $statsQuery)->whereHas('user', fn($q) => $q->where('role', 'admin'))->count(),
            'satpam' => (clone $statsQuery)->whereHas('user', fn($q) => $q->where('role', 'satpam'))->count(),
        ];

        return view('admin.audit.index', compact('logs', 'stats'));
    }

    /**
     * Hapus satu data catatan audit log
     */
    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete();
        return redirect()->back()->with('success', 'Catatan log aktivitas berhasil dihapus.');
    }

    /**
     * Bersihkan audit log berdasarkan tipe / umur data agar database tidak membengkak
     */
    public function clear(Request $request)
    {
        $type = $request->input('type', 'login_only');

        if ($type === 'login_only') {
            $count = AuditLog::whereIn('action', ['login', 'logout', 'login_sso_sipintu', 'sso_login', 'logout_other_devices', 'view_profile'])->delete();
            $msg = "Berhasil menghapus {$count} catatan riwayat login & logout.";
        } elseif ($type === 'older_30_days') {
            $count = AuditLog::where('created_at', '<', now()->subDays(30))->delete();
            $msg = "Berhasil menghapus {$count} catatan log yang berumur lebih dari 30 hari.";
        } elseif ($type === 'older_7_days') {
            $count = AuditLog::where('created_at', '<', now()->subDays(7))->delete();
            $msg = "Berhasil menghapus {$count} catatan log yang berumur lebih dari 7 hari.";
        } else {
            $count = AuditLog::query()->delete();
            $msg = "Berhasil membersihkan seluruh log aktivitas sistem ({$count} data dihapus).";
        }

        return redirect()->route('admin.audit.index')->with('success', $msg);
    }
}
