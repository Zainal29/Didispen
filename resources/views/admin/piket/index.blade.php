@extends('admin.layouts.app')

@section('title', 'Rekap Aktivitas Guru Piket')
@section('page-title', 'Rekap Aktivitas Guru Piket')

@section('content')
@include('components.alert')

<div class="space-y-6">
    {{-- Header Banner & Stats --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                    <i class="ti ti-user-check text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Rekap & Riwayat Guru Piket</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Monitoring aktivitas dan statistik pemrosesan dispensasi oleh guru piket.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                    <i class="ti ti-info-circle text-sm"></i> Konsep: Login = Piket
                </span>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="ti ti-users text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Guru</p>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $gurus->count() }} Guru</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="ti ti-file-text text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Terproses</p>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $gurus->sum('dispensasi_count') }} Surat</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/70 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="ti ti-award text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Guru Paling Aktif</p>
                    <p class="text-sm font-bold text-slate-900 mt-0.5 truncate">{{ $gurus->first()?->nama_lengkap ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel 1: Statistik Guru Piket --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ti ti-chart-bar text-blue-600"></i>
                    Statistik Guru Piket
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar peringkat guru berdasarkan akumulasi dispensasi yang telah diproses.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                {{ $gurus->count() }} Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Guru</th>
                        <th class="py-3 px-4">NIP</th>
                        <th class="py-3 px-4 text-center">Total Dispensasi Diproses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($gurus as $index => $g)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center">
                                @if($index === 0 && $g->dispensasi_count > 0)
                                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold text-xs inline-flex items-center justify-center">1</span>
                                @elseif($index === 1 && $g->dispensasi_count > 0)
                                    <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-bold text-xs inline-flex items-center justify-center">2</span>
                                @elseif($index === 2 && $g->dispensasi_count > 0)
                                    <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 font-bold text-xs inline-flex items-center justify-center">3</span>
                                @else
                                    <span class="text-xs text-slate-400 font-mono">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($g->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <span class="truncate">{{ $g->nama_lengkap }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $g->nip ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $g->dispensasi_count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-slate-100 text-slate-500' }}">
                                    <i class="ti ti-file-check text-xs"></i>
                                    {{ $g->dispensasi_count }} Surat
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400">
                                <i class="ti ti-folder-off text-3xl mb-1 text-slate-300"></i>
                                <p class="text-xs">Belum ada data guru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel 2: 100 Riwayat Persetujuan Terakhir --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ti ti-history text-blue-600"></i>
                    100 Riwayat Persetujuan Terakhir
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan audit riwayat pemrosesan surat dispensasi oleh guru piket.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                {{ $riwayat->count() }} Riwayat
            </span>
        </div>

        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">No. Surat</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Diproses Oleh</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-xs text-slate-500 font-mono whitespace-nowrap">
                                <i class="ti ti-clock text-slate-400 mr-1"></i>
                                {{ $r->created_at?->format('d/m/Y H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-blue-600 text-xs">
                                <a href="{{ route('admin.semua.pengajuan.show', $r->id) }}" class="hover:underline">
                                    {{ $r->nomor_surat }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                <div>{{ $r->siswa->nama_lengkap ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-normal">{{ $r->siswa?->kelas?->nama_kelas ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    <i class="ti ti-user-check text-emerald-600"></i>
                                    {{ $r->guru->nama_lengkap ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @php
                                    $statusPill = match($r->status) {
                                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'selesai' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'keluar' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusPill }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                <i class="ti ti-folder-off text-3xl mb-1 text-slate-300"></i>
                                <p class="text-xs">Belum ada riwayat persetujuan dispensasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection