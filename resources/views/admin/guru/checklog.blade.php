@extends('admin.layouts.app')
@section('title', 'Log Aktivitas Guru')
@section('page-title', 'Izin & Log Aktivitas Guru')

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Monitoring Tenaga Pengajar</span>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Daftar Aktivitas & Checklog Guru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pemantauan izin keluar dinas/pribadi guru secara real-time.</p>
            </div>
        </div>

        <div class="overflow-x-auto min-w-0 w-full touch-pan-x">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-3.5 pl-5 text-left">Nama Guru</th>
                        <th class="p-3.5 text-left">Tujuan & Keterangan</th>
                        <th class="p-3.5 text-left">Waktu Keluar</th>
                        <th class="p-3.5 text-left">Waktu Kembali</th>
                        <th class="p-3.5 pr-5 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $log->status === 'keluar' ? 'bg-rose-50/30' : '' }}">
                        <td class="p-3.5 pl-5">
                            <div class="font-bold text-slate-900">{{ $log->guru->nama_lengkap }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $log->guru->mata_pelajaran ?? 'Guru Pengajar' }}</div>
                        </td>
                        <td class="p-3.5 max-w-sm">
                            <div class="font-semibold text-slate-800">{{ $log->tujuan }}</div>
                            <div class="text-[11px] text-slate-500 italic mt-0.5">"{{ Str::limit($log->alasan, 65) }}"</div>
                            @if($log->lokasi)
                                <div class="text-[11px] text-blue-600 mt-1 flex items-center gap-1 font-medium">
                                    <i class="ti ti-map-pin text-xs"></i>
                                    <span>{{ $log->lokasi }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-3.5 font-mono text-slate-700 whitespace-nowrap">
                            {{ $log->jam_keluar->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                        </td>
                        <td class="p-3.5 font-mono text-slate-500 whitespace-nowrap">
                            {{ $log->jam_kembali ? $log->jam_kembali->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="p-3.5 pr-5 whitespace-nowrap">
                            @if($log->status === 'keluar')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-ping"></span>
                                    Sedang di Luar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="ti ti-circle-check text-xs"></i> Telah Kembali
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="ti ti-clipboard-list text-4xl mb-2 text-slate-300 block"></i>
                            <p class="text-xs font-semibold">Belum ada data log keluar/masuk guru.</p>
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