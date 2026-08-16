@extends('guru.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Guru Piket')

@section('content')
@include('components.alert')

@php
    $firstName = explode(' ', auth()->user()->name)[0];
    $fmtTime = function ($v) {
        if ($v instanceof \Carbon\Carbon) return $v->format('H:i');
        return preg_match('/\d{2}:\d{2}/', (string) $v, $m) ? $m[0] : $v;
    };
    $pendingCount = $stats['pending'] ?? 0;
@endphp

{{-- ============ HERO / PIKET HARI INI ============ --}}
@if($stats['piket_hari_ini'] ?? null)
<div class="relative overflow-hidden rounded-2xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500"></div>
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/10 rounded-full"></div>
    <div class="relative z-10 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-blue-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight mt-0.5">Halo, {{ $firstName }}! 👋</h2>
            <div class="mt-2.5 flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/15 border border-white/20 text-white text-[11px] font-bold">
                    <i class="fas fa-clock mr-1.5 text-amber-300"></i>Shift {{ ucfirst($stats['piket_hari_ini']->shift) }}
                </span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/15 border border-white/20 text-white text-[11px] font-semibold">
                    <i class="fas fa-user-shield mr-1.5 text-sky-200"></i>Guru Piket
                </span>
            </div>
        </div>
        <div class="hidden sm:block text-white/20 text-6xl flex-shrink-0">
            <i class="fas fa-calendar-check"></i>
        </div>
    </div>
</div>
@else
<div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 sm:p-5 mb-4 flex items-start gap-3">
    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/30">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="min-w-0">
        <h3 class="text-sm font-bold text-amber-800">Tidak Ada Jadwal Piket</h3>
        <p class="text-xs text-amber-700 mt-0.5">Anda tidak memiliki jadwal piket hari ini.</p>
    </div>
</div>
@endif



{{-- ============ STATISTIK CARDS ============ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 mb-4 md:grid-cols-3">
    @php
        $cards = [
            ['Menunggu',       $pendingCount,          'fa-clock',         'bg-amber-100 text-amber-600',    'border-amber-400'],
            ['Selesai',        $stats['selesai'] ?? 0, 'fa-check-circle',  'bg-emerald-100 text-emerald-600','border-emerald-400'],
            ['Total Hari Ini', $stats['total'] ?? 0,   'fa-tasks',         'bg-blue-100 text-blue-600',      'border-blue-400'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $color, $border])
        <div class="bg-white rounded-2xl border border-gray-100 border-l-4 {{ $border }} shadow-sm p-3.5 sm:p-4 flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-gray-500 uppercase font-bold truncate">{{ $label }}</p>
                <h3 class="text-2xl sm:text-3xl font-black text-gray-800 mt-1">{{ $value }}</h3>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $color }} flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fas {{ $icon }} text-sm sm:text-base"></i>
            </div>
        </div>
    @endforeach
</div>

{{-- ============ MENUNGGU PERSETUJUAN ============ --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-4 py-3.5 sm:p-5 border-b border-gray-100 flex items-center justify-between gap-2">
        <h3 class="text-sm font-bold text-gray-900 flex items-center">
            <i class="fas fa-list mr-2 text-blue-600"></i>Pengajuan Menunggu Persetujuan
            @if($pendingCount > 0)
                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold shadow-sm">
                    {{ $pendingCount }}
                </span>
            @endif
        </h3>
        @if(isset($pendingDispensasi) && $pendingDispensasi->count() > 0)
            <a href="{{ route('guru.pengajuan.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors flex-shrink-0">
                Lihat Semua →
            </a>
        @endif
    </div>

    {{-- MOBILE: Kartu --}}
    <div class="md:hidden divide-y divide-gray-100">
        @if(isset($pendingDispensasi) && $pendingDispensasi->count() > 0)
            @foreach($pendingDispensasi->take(5) as $item)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $item->siswa->nama_lengkap }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ $item->siswa->kelas->nama_kelas ?? '-' }} • {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 flex-shrink-0">Menunggu</span>
                    </div>

                    <div class="text-[11px] text-gray-600 bg-gray-50 rounded-xl p-2.5 space-y-1 border border-gray-100">
                        <p class="truncate"><strong>Tujuan:</strong> {{ $item->tujuan }}</p>
                        <p><strong>Waktu:</strong> {{ $fmtTime($item->jam_keluar) }} – {{ $fmtTime($item->jam_kembali) }}</p>
                    </div>

                    <a href="{{ route('guru.pengajuan.show', $item) }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all">
                        <i class="fas fa-eye mr-1.5"></i>Proses Pengajuan
                    </a>
                </div>
            @endforeach
        @else
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-300 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p class="text-gray-500 font-semibold text-sm">Semua pengajuan sudah diproses.</p>
                <p class="text-gray-400 text-xs mt-0.5">Kerja bagus!</p>
            </div>
        @endif
    </div>

    {{-- DESKTOP: Tabel / List Modern --}}
    <div class="hidden md:block p-5">
        @if(isset($pendingDispensasi) && $pendingDispensasi->count() > 0)
            <div class="space-y-3">
                @foreach($pendingDispensasi->take(5) as $item)
                    <div class="border border-gray-100 rounded-xl p-4 hover:border-blue-200 hover:bg-blue-50/30 transition-all flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center space-x-2">
                                <h4 class="font-bold text-gray-900 text-sm truncate">{{ $item->siswa->nama_lengkap }}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">Menunggu</span>
                            </div>
                            <p class="text-xs text-gray-500 truncate mt-1">
                                <span class="font-semibold text-gray-700">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span> • 
                                <span class="capitalize">{{ str_replace('_', ' ', $item->kategori) }}</span> • 
                                <span class="font-mono font-medium text-blue-600">{{ $fmtTime($item->jam_keluar) }} – {{ $fmtTime($item->jam_kembali) }}</span> • 
                                <span>Tujuan: {{ $item->tujuan }}</span>
                            </p>
                        </div>
                        <a href="{{ route('guru.pengajuan.show', $item) }}"
                           class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-md shadow-blue-500/20 transition-all flex-shrink-0 active:scale-95">
                            <i class="fas fa-eye mr-1.5"></i>Proses
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-300 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p class="text-gray-500 font-semibold text-sm">Semua pengajuan sudah diproses</p>
                <p class="text-gray-400 text-xs mt-1">Tidak ada dispensasi yang menunggu verifikasi saat ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection