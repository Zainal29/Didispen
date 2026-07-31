<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->action))
            ->latest()->paginate(25);
        return view('admin.audit.index', compact('logs'));
    }
}
