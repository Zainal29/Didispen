@extends('admin.layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log Aktivitas')

@section('content')
<div class="space-y-6">
    {{-- HEADER DENGAN FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Log Aktivitas Sistem</h3>
                <p class="text-sm text-gray-500 mt-1">Catatan semua aktivitas pengguna dan sinkronisasi.</p>
            </div>

            <form method="GET" class="flex flex-wrap gap-2">
                <select name="filter_type" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Aktivitas</option>
                    <option value="admin" {{ request('filter_type') == 'admin' ? 'selected' : '' }}>Admin Only</option>
                    <option value="satpam" {{ request('filter_type') == 'satpam' ? 'selected' : '' }}>Satpam Only</option>
                    <option value="sync" {{ request('filter_type') == 'sync' ? 'selected' : '' }}>Sinkronisasi</option>
                </select>

                <select name="action" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Aksi</option>
                    @foreach(['sync_sipintu_siswa', 'sync_sipintu_guru', 'approve', 'reject', 'konfirmasi_keluar', 'konfirmasi_kembali', 'create_siswa', 'create_guru', 'update_siswa', 'update_guru'] as $a)
                        <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ str_replace('_', ' ', $a) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $logs->total() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Sinkronisasi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $logs->where('action', 'like', '%sync%')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fas fa-sync-alt"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Aktivitas Admin</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $logs->where('user.role', 'admin')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Aktivitas Satpam</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $logs->where('user.role', 'satpam')->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-user-secret"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 font-semibold">
                    <tr>
                        <th class="p-4 text-left">Waktu</th>
                        <th class="p-4 text-left">User</th>
                        <th class="p-4 text-left">Role</th>
                        <th class="p-4 text-left">Aksi</th>
                        <th class="p-4 text-left">Tabel</th>
                        <th class="p-4 text-center">Record ID</th>
                        <th class="p-4 text-left">IP</th>
                        <th class="p-4 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm">
                            <div class="font-mono text-xs text-gray-600">{{ $log->created_at->timezone('Asia/Jakarta')->format('d-m-Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-sm text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                            @if($log->user)
                                <div class="text-xs text-gray-500">{{ $log->user->email ?? '-' }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($log->user)
                                @php
                                    $roleBadge = [
                                        'admin' => 'bg-indigo-100 text-indigo-700',
                                        'satpam' => 'bg-emerald-100 text-emerald-700',
                                        'guru' => 'bg-blue-100 text-blue-700',
                                    ][$log->user->role] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-2 py-1 rounded-md text-xs font-bold {{ $roleBadge }}">
                                    {{ ucfirst($log->user->role) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-500">System</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @php
                                $actionColors = [
                                    'sync_sipintu_siswa' => 'purple',
                                    'sync_sipintu_guru' => 'purple',
                                    'approve' => 'emerald',
                                    'reject' => 'red',
                                    'konfirmasi_keluar' => 'blue',
                                    'konfirmasi_kembali' => 'cyan',
                                    'create_siswa' => 'indigo',
                                    'create_guru' => 'orange',
                                    'update_siswa' => 'yellow',
                                    'update_guru' => 'yellow',
                                ];
                                $color = $actionColors[$log->action] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-100">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="p-4 text-sm font-mono text-gray-600">{{ $log->table_name }}</td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                                {{ $log->record_id }}
                            </span>
                        </td>
                        <td class="p-4 text-sm text-gray-600 font-mono">{{ $log->ip_address }}</td>
                        <td class="p-4">
                            @if(str_contains($log->action, 'sync'))
                                @php
                                    $isSuccess = ($log->new_value['success'] ?? false) ||
                                                 ($log->new_value['stats']['failed'] ?? 0) == 0 ||
                                                 !isset($log->new_value['success']);
                                    $statusColor = $isSuccess ? 'emerald' : 'red';
                                    $statusText = $isSuccess ? 'Berhasil' : 'Gagal';
                                    $statusIcon = $isSuccess ? 'fa-check-circle' : 'fa-times-circle';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-100">
                                    <i class="fas {{ $statusIcon }}"></i>
                                    {{ $statusText }}
                                </span>
                                @if(isset($log->new_value['stats']))
                                    <div class="mt-1 text-[10px] text-gray-500">
                                        +{{ $log->new_value['stats']['inserted'] ?? 0 }} / ~{{ $log->new_value['stats']['updated'] ?? 0 }}
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- DETAIL ROW UNTUK SYNC --}}
                    @if(str_contains($log->action, 'sync') && ($log->new_value || $log->old_value))
                    <tr class="bg-gray-50/50">
                        <td colspan="8" class="p-0">
                            <details class="group">
                                <summary class="cursor-pointer px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors flex items-center gap-2">
                                    <i class="fas fa-chevron-right group-open:rotate-90 transition-transform"></i>
                                    Lihat Detail Sinkronisasi
                                </summary>
                                <div class="p-4 border-t border-gray-200">
                                    @if(isset($log->new_value['message']))
                                        <div class="mb-3 p-3 bg-white rounded-lg border border-gray-200">
                                            <p class="text-xs font-semibold text-gray-700 mb-1">Pesan:</p>
                                            <p class="text-xs text-gray-600">{{ $log->new_value['message'] }}</p>
                                        </div>
                                    @endif

                                    @if(isset($log->new_value['stats']))
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                            <div class="bg-white p-3 rounded-lg border border-gray-200">
                                                <p class="text-[10px] text-gray-500 uppercase">Total Data</p>
                                                <p class="text-lg font-bold text-gray-800">{{ $log->new_value['stats']['total'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                                                <p class="text-[10px] text-emerald-600 uppercase">Inserted</p>
                                                <p class="text-lg font-bold text-emerald-700">{{ $log->new_value['stats']['inserted'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                                <p class="text-[10px] text-blue-600 uppercase">Updated</p>
                                                <p class="text-lg font-bold text-blue-700">{{ $log->new_value['stats']['updated'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                                <p class="text-[10px] text-red-600 uppercase">Failed</p>
                                                <p class="text-lg font-bold text-red-700">{{ $log->new_value['stats']['failed'] ?? 0 }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($log->new_value['stats']['errors']) && count($log->new_value['stats']['errors']) > 0)
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                            <p class="text-xs font-bold text-red-700 mb-2">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Error ({{ count($log->new_value['stats']['errors']) }}):
                                            </p>
                                            <ul class="text-[10px] text-red-600 space-y-1 max-h-32 overflow-y-auto">
                                                @foreach(array_slice($log->new_value['stats']['errors'], 0, 5) as $error)
                                                    <li>• {{ $error }}</li>
                                                @endforeach
                                                @if(count($log->new_value['stats']['errors']) > 5)
                                                    <li class="text-gray-500 italic">... dan {{ count($log->new_value['stats']['errors']) - 5 }} error lainnya</li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-clipboard-list text-4xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium text-gray-600">Belum ada log aktivitas.</p>
                                <p class="text-xs mt-1">Aktivitas sistem akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
