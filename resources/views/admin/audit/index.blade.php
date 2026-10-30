@extends('admin.layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log Aktivitas')

@section('content')
@include('components.alert')

<div class="space-y-6">
    {{-- HEADER DENGAN FILTER LENGKAP --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                    <i class="ti ti-history text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Log Aktivitas Sistem</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan seluruh rekam jejak aktivitas pengguna, akses, dan sinkronisasi.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                {{-- Tombol Test Koneksi --}}
                <form method="POST" action="{{ route('admin.sipintu.test-sync') }}">
                    @csrf
                    <button type="submit" class="uiverse-btn-tactile min-h-[40px] px-3.5 py-2 bg-slate-50 border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                        <i class="ti ti-plug text-sm text-blue-600"></i> Test Koneksi SiPintu
                    </button>
                </form>

                {{-- Tombol Bersihkan Log --}}
                <button type="button" onclick="openClearLogsModal()" class="uiverse-btn-tactile min-h-[40px] px-3.5 py-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="ti ti-trash text-sm"></i> Bersihkan Log
                </button>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <form method="GET" class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="ti ti-search text-sm"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari User / IP..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-800 transition">
            </div>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none w-full sm:w-auto text-slate-700">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none w-full sm:w-auto text-slate-700">

            <select name="filter_type" onchange="this.form.submit()" class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none w-full sm:w-auto text-slate-700">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('filter_type') == 'admin' ? 'selected' : '' }}>Admin Only</option>
                <option value="satpam" {{ request('filter_type') == 'satpam' ? 'selected' : '' }}>Satpam Only</option>
                <option value="sync" {{ request('filter_type') == 'sync' ? 'selected' : '' }}>Sinkronisasi</option>
            </select>

            <select name="action" onchange="this.form.submit()" class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none w-full sm:w-auto text-slate-700">
                <option value="">Semua Aksi</option>
                @foreach(['test_connection_sipintu','sync_sipintu_siswa', 'sync_sipintu_guru', 'approve', 'reject', 'konfirmasi_keluar', 'konfirmasi_kembali', 'create_siswa', 'create_guru', 'update_siswa', 'update_guru'] as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ str_replace('_', ' ', $a) }}</option>
                @endforeach
            </select>

            <button type="submit" class="uiverse-btn-tactile px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm shadow-blue-500/25">
                <i class="ti ti-filter text-sm"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'date_from', 'date_to', 'filter_type', 'action']))
                <a href="{{ route('admin.audit.index') }}" class="uiverse-btn-tactile px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="uiverse-card-lift bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Total Aktivitas</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center">
                <i class="ti ti-activity text-xl"></i>
            </div>
        </div>

        <div class="uiverse-card-lift bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Sinkronisasi</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['sync']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center">
                <i class="ti ti-refresh text-xl"></i>
            </div>
        </div>

        <div class="uiverse-card-lift bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Aktivitas Admin</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['admin']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center">
                <i class="ti ti-user-check text-xl"></i>
            </div>
        </div>

        <div class="uiverse-card-lift bg-white rounded-2xl shadow-2xs border border-slate-200/80 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Aktivitas Satpam</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['satpam']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center">
                <i class="ti ti-shield-check text-xl"></i>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        {{-- HINT SCROLL MOBILE --}}
        <div class="px-4 py-2 bg-blue-50/60 border-b border-blue-100/70 text-blue-700 text-xs sm:hidden flex items-center gap-1.5 font-medium">
            <i class="ti ti-arrows-horizontal text-blue-600 animate-pulse text-base"></i>
            <span>Geser tabel ke samping untuk melihat seluruh kolom audit aktivitas</span>
        </div>

        <div class="table-scroll-container">
            <table class="w-full min-w-[1040px] text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 text-left whitespace-nowrap min-w-[130px]">Waktu</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[190px]">User</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[90px]">Role</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[120px]">Aksi</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[120px]">Tabel</th>
                        <th class="p-4 text-center whitespace-nowrap min-w-[100px]">Record ID</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[130px]">IP</th>
                        <th class="p-4 text-left whitespace-nowrap min-w-[120px]">Status</th>
                        <th class="p-4 text-center whitespace-nowrap min-w-[80px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 whitespace-nowrap">
                            <div class="font-mono text-xs font-semibold text-slate-800">{{ $log->created_at->timezone('Asia/Jakarta')->format('d-m-Y') }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                        </td>
                        <td class="p-4 min-w-[190px]">
                            <div class="font-bold text-xs sm:text-sm text-slate-900">{{ $log->user->name ?? 'System' }}</div>
                            @if($log->user)
                                <div class="text-[11px] text-slate-400">{{ $log->user->email ?? '-' }}</div>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            @if($log->user)
                                @php
                                    $roleBadge = [
                                        'admin' => 'bg-blue-50 text-blue-700 border-blue-200/60',
                                        'satpam' => 'bg-blue-50 text-blue-700 border-blue-200/60',
                                        'guru' => 'bg-blue-50 text-blue-700 border-blue-200/60',
                                    ][$log->user->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $roleBadge }}">
                                    {{ ucfirst($log->user->role) }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    System
                                </span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="font-semibold text-xs text-slate-800 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->table_name ?? '-' }}
                        </td>
                        <td class="p-4 text-center font-mono text-xs text-slate-600 whitespace-nowrap">
                            {{ $log->record_id ?? '-' }}
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            @if(isset($log->new_value['status']))
                                @php
                                    $rawStatus = (string) $log->new_value['status'];
                                    $statusConfig = [
                                        'success'    => ['text' => 'Berhasil',   'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'icon' => 'ti-check'],
                                        'failed'     => ['text' => 'Gagal',      'class' => 'bg-rose-50 text-rose-700 border-rose-200/60',       'icon' => 'ti-x'],
                                        'disetujui'  => ['text' => 'Disetujui',  'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'icon' => 'ti-circle-check'],
                                        'keluar'     => ['text' => 'Keluar',     'class' => 'bg-blue-50 text-blue-700 border-blue-200/60',       'icon' => 'ti-door-exit'],
                                        'selesai'    => ['text' => 'Selesai',    'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60', 'icon' => 'ti-circle-check'],
                                        'menunggu'   => ['text' => 'Menunggu',   'class' => 'bg-amber-50 text-amber-700 border-amber-200/60',   'icon' => 'ti-clock'],
                                        'ditolak'    => ['text' => 'Ditolak',    'class' => 'bg-rose-50 text-rose-700 border-rose-200/60',       'icon' => 'ti-x'],
                                        'dibatalkan' => ['text' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-700 border-slate-200',      'icon' => 'ti-ban'],
                                        'kadaluarsa' => ['text' => 'Kadaluarsa', 'class' => 'bg-red-50 text-red-700 border-red-200/60',       'icon' => 'ti-clock-x'],
                                    ];
                                    $cfg = $statusConfig[$rawStatus] ?? [
                                        'text'  => ucfirst(str_replace('_', ' ', $rawStatus)),
                                        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                                        'icon'  => 'ti-check'
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $cfg['class'] }}">
                                    <i class="ti {{ $cfg['icon'] }} text-xs"></i>
                                    {{ $cfg['text'] }}
                                </span>
                                @if(isset($log->new_value['stats']))
                                    <div class="mt-1 text-[10px] font-mono text-slate-400">
                                        +{{ $log->new_value['stats']['inserted'] ?? 0 }} / ~{{ $log->new_value['stats']['updated'] ?? 0 }}
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            <button type="button"
                                    onclick="confirmDeleteSingleLog({{ $log->id }})"
                                    class="uiverse-btn-tactile inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors shadow-2xs"
                                    title="Hapus log ini">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- DETAIL ROW UNTUK SYNC --}}
                    @if(str_contains($log->action, 'sync') && ($log->new_value || $log->old_value))
                    <tr class="bg-slate-50/50">
                        <td colspan="9" class="p-0">
                            <details class="group">
                                <summary class="cursor-pointer px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors flex items-center gap-2">
                                    <i class="ti ti-chevron-right group-open:rotate-90 transition-transform"></i>
                                    Lihat Detail Sinkronisasi
                                </summary>
                                <div class="p-4 border-t border-slate-200 space-y-3">
                                    @if(isset($log->new_value['message']))
                                        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
                                            <p class="text-xs font-bold text-slate-700 mb-0.5">Pesan:</p>
                                            <p class="text-xs text-slate-600">{{ $log->new_value['message'] }}</p>
                                        </div>
                                    @endif

                                    @if(isset($log->new_value['stats']))
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <div class="bg-white p-3 rounded-xl border border-slate-200">
                                                <p class="text-[10px] text-slate-400 uppercase font-bold">Total Data</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $log->new_value['stats']['total'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-white p-3 rounded-xl border border-slate-200">
                                                <p class="text-[10px] text-emerald-600 uppercase font-bold">Inserted</p>
                                                <p class="text-lg font-bold text-emerald-700">{{ $log->new_value['stats']['inserted'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-white p-3 rounded-xl border border-slate-200">
                                                <p class="text-[10px] text-blue-600 uppercase font-bold">Updated</p>
                                                <p class="text-lg font-bold text-blue-700">{{ $log->new_value['stats']['updated'] ?? 0 }}</p>
                                            </div>
                                            <div class="bg-white p-3 rounded-xl border border-slate-200">
                                                <p class="text-[10px] text-rose-600 uppercase font-bold">Failed</p>
                                                <p class="text-lg font-bold text-rose-700">{{ $log->new_value['stats']['failed'] ?? 0 }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($log->new_value['stats']['errors']) && count($log->new_value['stats']['errors']) > 0)
                                        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3">
                                            <p class="text-xs font-bold text-rose-700 mb-2 flex items-center gap-1.5">
                                                <i class="ti ti-alert-triangle"></i>
                                                Error ({{ count($log->new_value['stats']['errors']) }}):
                                            </p>
                                            <ul class="text-[10px] text-rose-600 space-y-1 max-h-32 overflow-y-auto sidebar-scroll">
                                                @foreach(array_slice($log->new_value['stats']['errors'], 0, 5) as $error)
                                                    <li>• {{ $error }}</li>
                                                @endforeach
                                                @if(count($log->new_value['stats']['errors']) > 5)
                                                    <li class="text-slate-500 italic">... dan {{ count($log->new_value['stats']['errors']) - 5 }} error lainnya</li>
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
                        <td colspan="9" class="p-10 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="ti ti-history text-4xl mb-3 opacity-40"></i>
                                <p class="text-sm font-semibold text-slate-600">Belum ada log aktivitas.</p>
                                <p class="text-xs mt-1 text-slate-400">Aktivitas sistem akan tercatat otomatis di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDeleteSingleLog(id) {
    Swal.fire({
        title: 'Hapus Log Ini?',
        text: 'Catatan log aktivitas ini akan dihapus permanen dari sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFormAction(`/admin/audit-log/${id}`, 'DELETE', 'Menghapus log...');
        }
    });
}

function openClearLogsModal() {
    Swal.fire({
        title: 'Bersihkan Log Aktivitas',
        html: `
            <div class="text-left text-sm space-y-3">
                <p class="text-slate-600">Pilih opsi pembersihan log untuk mengoptimalkan penyimpanan & performa database:</p>
                <div class="space-y-2 mt-2">
                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="swal_clear_type" value="login_only" checked class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-bold text-slate-800 text-xs sm:text-sm">Hapus Riwayat Login & Logout Saja</p>
                            <p class="text-[11px] text-slate-500">Rekomendasi utama agar sistem ringan. Hanya menghapus log sesi masuk/keluar.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="swal_clear_type" value="older_30_days" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-bold text-slate-800 text-xs sm:text-sm">Hapus Log Lebih dari 30 Hari</p>
                            <p class="text-[11px] text-slate-500">Menghapus seluruh log aktivitas yang berumur lebih dari 1 bulan.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="swal_clear_type" value="older_7_days" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="font-bold text-slate-800 text-xs sm:text-sm">Hapus Log Lebih dari 7 Hari</p>
                            <p class="text-[11px] text-slate-500">Menghapus seluruh log aktivitas yang berumur lebih dari 1 minggu.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer">
                        <input type="radio" name="swal_clear_type" value="all" class="mt-1 text-rose-600 focus:ring-rose-500">
                        <div>
                            <p class="font-bold text-rose-800 text-xs sm:text-sm">Kosongkan Seluruh Log Sistem</p>
                            <p class="text-[11px] text-rose-600 font-medium">Hapus total semua catatan riwayat sistem (Reset bersih).</p>
                        </div>
                    </label>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Bersihkan Sekarang',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        },
        preConfirm: () => {
            const selected = document.querySelector('input[name="swal_clear_type"]:checked');
            return selected ? selected.value : 'login_only';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const selectedType = result.value;
            submitClearLogs(selectedType);
        }
    });
}

function submitClearLogs(type) {
    Swal.fire({
        title: 'Membersihkan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.audit.clear') }}';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    form.appendChild(typeInput);

    document.body.appendChild(form);
    form.submit();
}

function submitFormAction(url, method, loadingText) {
    Swal.fire({
        title: loadingText || 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = method;

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
