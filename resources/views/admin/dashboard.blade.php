@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    {{-- =========================================================
        1. EXECUTIVE HERO BANNER (Preline Glassmorphism + Uiverse Tactile CTAs)
    ========================================================== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 border border-slate-800/80">
        
        {{-- Decorative Ambient Glows --}}
        <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-16 w-56 h-56 rounded-full bg-cyan-400/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            
            {{-- Left: Identity & Greeting --}}
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/15 border border-blue-400/25 text-blue-300 mb-3 backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>DIDISPEN Central • Panel Administrator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                    Pusat kendali dan pemantauan perizinan dispensasi siswa secara real-time. Kelola validasi, pantau siswa di luar sekolah, dan rekapitulasi data secara terpadu.
                </p>

                {{-- Action Shortcuts (Uiverse Tactile Buttons) --}}
                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <a href="{{ route('admin.semua.pengajuan') }}" 
                       class="uiverse-btn-tactile inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white text-xs font-bold shadow-lg shadow-blue-600/30">
                        <i class="ti ti-file-text text-base"></i>
                        <span>Buka Semua Pengajuan</span>
                    </a>
                    <a href="{{ route('admin.laporan.index') }}" 
                       class="uiverse-btn-tactile inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-slate-200 text-xs font-semibold backdrop-blur-xs">
                        <i class="ti ti-chart-bar text-base"></i>
                        <span>Laporan Rekap</span>
                    </a>
                </div>
            </div>

            {{-- Right: Live Operational Pulse (Preline Metric Widgets Inside Hero) --}}
            <div class="grid grid-cols-3 gap-2.5 sm:gap-3 shrink-0 lg:max-w-md w-full">
                
                {{-- Menunggu --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between transition-all hover:bg-white/10 {{ $stats['menunggu'] > 0 ? 'uiverse-glow-amber' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['menunggu'] > 0 ? 'bg-amber-400 animate-ping' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $stats['menunggu'] }}</p>
                        <p class="text-[10px] text-amber-300/80 font-medium mt-0.5">Perlu Aksi</p>
                    </div>
                </div>

                {{-- Sedang di Luar --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between transition-all hover:bg-white/10 {{ $stats['keluar'] > 0 ? 'uiverse-glow-blue' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Di Luar</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['keluar'] > 0 ? 'bg-blue-400 animate-pulse' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $stats['keluar'] }}</p>
                        <p class="text-[10px] text-blue-300/80 font-medium mt-0.5">Siswa Aktif</p>
                    </div>
                </div>

                {{-- Terlambat --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between transition-all hover:bg-white/10 {{ $stats['terlambat'] > 0 ? 'uiverse-glow-rose' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['terlambat'] > 0 ? 'bg-rose-500 animate-ping' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black {{ $stats['terlambat'] > 0 ? 'text-rose-400' : 'text-white' }} tracking-tight">
                            {{ $stats['terlambat'] }}
                        </p>
                        <p class="text-[10px] {{ $stats['terlambat'] > 0 ? 'text-rose-300 font-bold' : 'text-slate-400' }} mt-0.5">Overdue</p>
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- =========================================================
        2. STATUS OPERASIONAL UTAMA (4 Preline UI KPI Metric Cards)
    ========================================================== --}}
    <div>
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                <span>Status Siklus Dispensasi</span>
            </h2>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Live Sinkronisasi
            </span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- 1. MENUNGGU --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'menunggu']) }}" 
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-amber-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                            {{ $stats['menunggu'] }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform">
                        <i class="ti ti-clock-pause text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1 text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 {{ $stats['menunggu'] > 0 ? 'animate-pulse' : '' }}"></span>
                        Perlu Verifikasi
                    </span>
                    <i class="ti ti-arrow-right text-xs text-slate-400 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all"></i>
                </div>
            </a>

            {{-- 2. DISETUJUI --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'disetujui']) }}" 
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Disetujui</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                            {{ $stats['disetujui'] }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        <i class="ti ti-circle-check text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Siap Berangkat
                    </span>
                    <i class="ti ti-arrow-right text-xs text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all"></i>
                </div>
            </a>

            {{-- 3. SEDANG DI LUAR --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'keluar']) }}" 
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sedang di Luar</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                            {{ $stats['keluar'] }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                        <i class="ti ti-door-exit text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1 text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Izin Aktif
                    </span>
                    <i class="ti ti-arrow-right text-xs text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all"></i>
                </div>
            </a>

            {{-- 4. SELESAI --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'selesai']) }}" 
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                            {{ $stats['selesai'] }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-600 to-slate-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <i class="ti ti-flag text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center gap-1 text-slate-700 font-semibold bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                        Kembali Tertib
                    </span>
                    <i class="ti ti-arrow-right text-xs text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all"></i>
                </div>
            </a>

        </div>
    </div>


    {{-- =========================================================
        3. SECONDARY STRIP: Pengecualian & Master Data (Preline Metric Bars)
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        {{-- Exceptions Bar (Terlambat, Ditolak, Dibatalkan) --}}
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i class="ti ti-alert-circle text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Catatan & Pengecualian</h3>
                    <p class="text-[10px] text-slate-400">Status anomali dispensasi</p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl {{ $stats['terlambat'] > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 font-bold' : 'bg-slate-50 text-slate-600 border border-slate-200 font-medium' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $stats['terlambat'] > 0 ? 'bg-rose-500' : 'bg-slate-400' }}"></span>
                    <span>Terlambat: <strong>{{ $stats['terlambat'] }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 font-medium">
                    <span>Ditolak: <strong>{{ $stats['ditolak'] }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 font-medium hidden sm:inline-flex">
                    <span>Batal: <strong>{{ $stats['dibatalkan'] }}</strong></span>
                </span>
            </div>
        </div>

        {{-- Master Data Quick Stats (Siswa, Guru, Satpam) --}}
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                    <i class="ti ti-database text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Master Data Terdaftar</h3>
                    <p class="text-[10px] text-slate-400">Total entitas aktif di sistem</p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('admin.siswa.index') }}" class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200">
                    <i class="ti ti-school text-xs text-blue-600"></i>
                    <span>Siswa: <strong>{{ number_format($stats['total_siswa'], 0, ',', '.') }}</strong></span>
                </a>
                <a href="{{ route('admin.guru.index') }}" class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200">
                    <i class="ti ti-user-check text-xs text-blue-600"></i>
                    <span>Guru: <strong>{{ number_format($stats['total_guru'], 0, ',', '.') }}</strong></span>
                </a>
                <a href="{{ route('admin.satpam.index') }}" class="uiverse-btn-tactile inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 hidden sm:inline-flex">
                    <i class="ti ti-shield-check text-xs text-blue-600"></i>
                    <span>Satpam: <strong>{{ number_format($stats['total_satpam'], 0, ',', '.') }}</strong></span>
                </a>
            </div>
        </div>

    </div>


    {{-- =========================================================
        4. MAIN SECTION: ANALYTICS (8 cols) & RECENT ACTIVITY (4 cols)
    ========================================================== --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">

        {{-- CHART AKTIVITAS (8 COLS) --}}
        <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="ti ti-chart-bar text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Tren Pengajuan Dispensasi</h3>
                        <p class="text-[11px] text-slate-400">Volume pengajuan 7 hari terakhir</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white text-slate-700 text-[11px] font-bold border border-slate-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        7 Hari Terakhir
                    </span>
                </div>
            </div>

            <div class="p-5 flex-1 flex flex-col justify-center">
                <div class="relative h-[270px] w-full">
                    <canvas id="chartPengajuan"></canvas>
                </div>
            </div>
        </div>

        {{-- PENGAJUAN TERBARU (4 COLS) --}}
        <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="ti ti-list text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Pengajuan Terbaru</h3>
                        <p class="text-[11px] text-slate-400">Aktivitas dispensasi hari ini</p>
                    </div>
                </div>
                <a href="{{ route('admin.semua.pengajuan') }}" 
                   class="uiverse-btn-tactile text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <span>Semua</span>
                    <i class="ti ti-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="px-5 divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[340px]">
                @forelse($recent as $item)
                    <div class="py-3.5 flex items-center gap-3 hover:bg-slate-50/60 -mx-2 px-2 rounded-xl transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0 border border-blue-100/70">
                            {{ strtoupper(substr($item->siswa?->nama_lengkap ?? '?', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $item->siswa?->nama_lengkap ?? '-' }}</p>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5 truncate">
                                <span>{{ $item->siswa?->kelas?->nama_kelas ?? '-' }}</span>
                                <span>•</span>
                                <span class="capitalize">{{ $item->kategori }}</span>
                                <span>•</span>
                                <span>{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @php
                            $statusConfig = [
                                'menunggu'   => ['class' => 'bg-amber-50 text-amber-700 border-amber-200/80', 'dot' => 'bg-amber-500', 'label' => 'Menunggu'],
                                'disetujui'  => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80', 'dot' => 'bg-emerald-500', 'label' => 'Disetujui'],
                                'keluar'     => ['class' => 'bg-blue-50 text-blue-700 border-blue-200/80', 'dot' => 'bg-blue-500', 'label' => 'Di Luar'],
                                'selesai'    => ['class' => 'bg-slate-100 text-slate-700 border-slate-200/80', 'dot' => 'bg-slate-500', 'label' => 'Selesai'],
                                'ditolak'    => ['class' => 'bg-rose-50 text-rose-700 border-rose-200/80', 'dot' => 'bg-rose-500', 'label' => 'Ditolak'],
                                'dibatalkan' => ['class' => 'bg-slate-50 text-slate-500 border-slate-200/80', 'dot' => 'bg-slate-400', 'label' => 'Batal'],
                                'kadaluarsa' => ['class' => 'bg-zinc-100 text-zinc-600 border-zinc-200/80', 'dot' => 'bg-zinc-400', 'label' => 'Kadaluarsa'],
                            ];
                            $curr = $statusConfig[$item->status] ?? ['class' => 'bg-slate-50 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400', 'label' => ucfirst($item->status)];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold border shrink-0 {{ $curr['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $curr['dot'] }}"></span>
                            {{ $curr['label'] }}
                        </span>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <i class="ti ti-inbox text-3xl mb-1 block"></i>
                        <p class="text-xs font-semibold">Belum ada pengajuan hari ini</p>
                    </div>
                @endforelse
            </div>

            @if($recent->count())
                <div class="p-3 bg-slate-50/80 border-t border-slate-100">
                    <a href="{{ route('admin.semua.pengajuan') }}" 
                       class="uiverse-btn-tactile w-full flex items-center justify-center gap-1.5 py-2 text-xs font-bold text-slate-600 hover:text-blue-600">
                        <span>Lihat Seluruh Data Pengajuan</span>
                        <i class="ti ti-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </div>

    </div>


    {{-- =========================================================
        5. QUICK SHORTCUTS (4 Preline / Uiverse Tactile Cards)
    ========================================================== --}}
    <div>
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                <span>Pusat Kendali & Menu Cepat</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- 1. Semua Pengajuan --}}
            <a href="{{ route('admin.semua.pengajuan') }}"
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-blue-500/25 transition-all">
                        <i class="ti ti-file-text text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Semua Pengajuan</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pantau, validasi, dan kelola seluruh berkas izin dispensasi siswa.</p>
                </div>
            </a>

            {{-- 2. Data Siswa --}}
            <a href="{{ route('admin.siswa.index') }}"
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-blue-500/25 transition-all">
                        <i class="ti ti-school text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Data Siswa</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Kelola biodata siswa, rombel kelas, NIS, dan status akun aktif.</p>
                </div>
            </a>

            {{-- 3. Data Guru --}}
            <a href="{{ route('admin.guru.index') }}"
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-blue-500/25 transition-all">
                        <i class="ti ti-user-check text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Data Guru</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Manajemen data pengajar, NIP, kontak dinas, serta hak akses piket.</p>
                </div>
            </a>

            {{-- 4. Laporan Rekap --}}
            <a href="{{ route('admin.laporan.index') }}"
               class="group p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 uiverse-card-lift flex flex-col justify-between shadow-2xs">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-blue-500/25 transition-all">
                        <i class="ti ti-chart-bar text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Laporan & Rekap</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Ekspor laporan rekapitulasi dispensasi berkala dalam format PDF/Excel.</p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('chartPengajuan');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 270);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.08)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'Pengajuan',
                data: @json($counts),
                backgroundColor: gradient,
                borderColor: '#2563eb',
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.55,
                categoryPercentage: 0.7,
                maxBarThickness: 38
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 12, weight: '500' },
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y + ' pengajuan';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.max(...@json($counts), 5) + 3,
                    border: { display: false },
                    ticks: {
                        stepSize: 1,
                        color: '#94a3b8',
                        font: { size: 10, weight: '500' },
                        padding: 6
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)',
                        drawTicks: false
                    }
                },
                x: {
                    border: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10, weight: '600' },
                        padding: 6
                    },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush