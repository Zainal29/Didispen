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

        if ($request->filled('filter_type')) {
            if ($request->filter_type === 'admin') {
                $query->whereHas('user', fn($q) => $q->where('role', 'admin'));
            } elseif ($request->filter_type === 'satpam') {
                $query->whereHas('user', fn($q) => $q->where('role', 'satpam'));
            } elseif ($request->filter_type === 'sync') {
                $query->where('action', 'like', '%sync%');
            }
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.audit.index', compact('logs'));
    }
}
