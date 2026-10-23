@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    {{-- =========================================================
        1. EXECUTIVE HERO BANNER (Modern, Elegant, Dynamic)
    ========================================================== --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 border border-slate-800/80">
        
        {{-- Decorative Ambient Glows --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-16 w-48 h-48 rounded-full bg-blue-400/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            
            {{-- Left: Identity & Greeting --}}
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/15 border border-blue-400/25 text-blue-300 mb-3 backdrop-blur-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>DIDISPEN Central • Panel Administrator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                    Pusat kendali dan pemantauan perizinan dispensasi siswa secara real-time. Kelola validasi, pantau siswa di luar sekolah, dan rekapitulasi data secara terpadu.
                </p>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <a href="{{ route('admin.semua.pengajuan') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-0.5">
                        <i class="ti ti-file-text text-base"></i>
                        <span>Buka Semua Pengajuan</span>
                    </a>
                    <a href="{{ route('admin.laporan.index') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-slate-200 text-xs font-semibold backdrop-blur-xs transition-all hover:-translate-y-0.5">
                        <i class="ti ti-chart-bar text-base"></i>
                        <span>Laporan Rekap</span>
                    </a>
                </div>
            </div>

            {{-- Right: Live Operational Pulse (3 Key Metrics Inside Hero) --}}
            <div class="grid grid-cols-3 gap-2.5 sm:gap-3 shrink-0 lg:max-w-md w-full">
                
                {{-- Menunggu --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['menunggu'] > 0 ? 'bg-amber-400 animate-ping' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $stats['menunggu'] }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Perlu Aksi</p>
                    </div>
                </div>

                {{-- Sedang di Luar --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Di Luar</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['keluar'] > 0 ? 'bg-blue-400 animate-pulse' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $stats['keluar'] }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Siswa Aktif</p>
                    </div>
                </div>

                {{-- Terlambat --}}
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
                        <span class="w-2 h-2 rounded-full {{ $stats['terlambat'] > 0 ? 'bg-rose-500 animate-ping' : 'bg-slate-600' }}"></span>
                    </div>
                    <div class="mt-2">
                        <p class="text-xl sm:text-2xl font-black {{ $stats['terlambat'] > 0 ? 'text-rose-400' : 'text-white' }} tracking-tight">
                            {{ $stats['terlambat'] }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Overdue</p>
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- =========================================================
        2. STATUS OPERASIONAL UTAMA (4 Unified Executive Cards)
    ========================================================== --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                <span>Status Siklus Dispensasi</span>
            </h2>
            <span class="text-[11px] text-slate-400 font-medium">Diperbarui real-time</span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            
            {{-- 1. MENUNGGU --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'menunggu']) }}" 
               class="group p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-clock-pause text-lg"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $stats['menunggu'] }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Verifikasi guru / piket</span>
                        <i class="ti ti-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
            </a>

            {{-- 2. DISETUJUI --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'disetujui']) }}" 
               class="group p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Disetujui</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-circle-check text-lg"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $stats['disetujui'] }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Siap berangkat keluar</span>
                        <i class="ti ti-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
            </a>

            {{-- 3. SEDANG DI LUAR --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'keluar']) }}" 
               class="group p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sedang di Luar</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-door-exit text-lg"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $stats['keluar'] }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Izin aktif di luar kampus</span>
                        <i class="ti ti-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
            </a>

            {{-- 4. SELESAI --}}
            <a href="{{ route('admin.semua.pengajuan', ['status' => 'selesai']) }}" 
               class="group p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-flag text-lg"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $stats['selesai'] }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Telah kembali & diverifikasi</span>
                        <i class="ti ti-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
            </a>

        </div>
    </div>


    {{-- =========================================================
        3. SECONDARY STRIP: Pengecualian & Master Data (Clean, Unified)
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
        
        {{-- Exceptions Bar (Terlambat, Ditolak, Dibatalkan) --}}
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i class="ti ti-alert-circle text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Catatan & Pengecualian</h3>
                    <p class="text-[10px] text-slate-400">Dispensasi tidak normal</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $stats['terlambat'] > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 font-bold' : 'bg-slate-50 text-slate-600 border border-slate-200 font-medium' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $stats['terlambat'] > 0 ? 'bg-rose-500' : 'bg-slate-400' }}"></span>
                    <span>Terlambat: <strong>{{ $stats['terlambat'] }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 border border-slate-200 font-medium">
                    <span>Ditolak: <strong>{{ $stats['ditolak'] }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 border border-slate-200 font-medium hidden sm:inline-flex">
                    <span>Batal: <strong>{{ $stats['dibatalkan'] }}</strong></span>
                </span>
            </div>
        </div>

        {{-- Master Data Quick Stats (Siswa, Guru, Satpam) --}}
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="ti ti-database text-base"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Master Data Terdaftar</h3>
                    <p class="text-[10px] text-slate-400">Total entitas aktif di sistem</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 text-xs">
                <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 transition-colors">
                    <i class="ti ti-school text-xs text-blue-600"></i>
                    <span>Siswa: <strong>{{ number_format($stats['total_siswa'], 0, ',', '.') }}</strong></span>
                </a>
                <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 transition-colors">
                    <i class="ti ti-user-check text-xs text-blue-600"></i>
                    <span>Guru: <strong>{{ number_format($stats['total_guru'], 0, ',', '.') }}</strong></span>
                </a>
                <a href="{{ route('admin.satpam.index') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 transition-colors hidden sm:inline-flex">
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
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
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
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 text-[11px] font-semibold border border-slate-200/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
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
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
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
                   class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1">
                    <span>Semua</span>
                    <i class="ti ti-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="px-5 divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[340px]">
                @forelse($recent as $item)
                    <div class="py-3.5 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
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
                                'menunggu'   => ['class' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu'],
                                'disetujui'  => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Disetujui'],
                                'keluar'     => ['class' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Di Luar'],
                                'selesai'    => ['class' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Selesai'],
                                'ditolak'    => ['class' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Ditolak'],
                                'dibatalkan' => ['class' => 'bg-slate-50 text-slate-500 border-slate-200', 'label' => 'Batal'],
                                'kadaluarsa' => ['class' => 'bg-zinc-100 text-zinc-600 border-zinc-200', 'label' => 'Kadaluarsa'],
                            ];
                            $curr = $statusConfig[$item->status] ?? ['class' => 'bg-slate-50 text-slate-600 border-slate-200', 'label' => ucfirst($item->status)];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold border shrink-0 {{ $curr['class'] }}">
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
                       class="w-full flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors">
                        <span>Lihat Seluruh Data Pengajuan</span>
                        <i class="ti ti-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </div>

    </div>


    {{-- =========================================================
        5. QUICK SHORTCUTS (4 Unified Royal Blue Cards)
    ========================================================== --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                <span>Pusat Kendali & Menu Cepat</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
            {{-- 1. Semua Pengajuan --}}
            <a href="{{ route('admin.semua.pengajuan') }}"
               class="group p-4 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="ti ti-file-text text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Semua Pengajuan</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pantau, validasi, dan kelola seluruh berkas izin dispensasi siswa.</p>
                </div>
            </a>

            {{-- 2. Data Siswa --}}
            <a href="{{ route('admin.siswa.index') }}"
               class="group p-4 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="ti ti-school text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Data Siswa</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Kelola biodata siswa, rombel kelas, NIS, dan status akun aktif.</p>
                </div>
            </a>

            {{-- 3. Data Guru --}}
            <a href="{{ route('admin.guru.index') }}"
               class="group p-4 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="ti ti-user-check text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Data Guru</h4>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Manajemen data pengajar, NIP, kontak dinas, serta hak akses piket.</p>
                </div>
            </a>

            {{-- 4. Laporan Rekap --}}
            <a href="{{ route('admin.laporan.index') }}"
               class="group p-4 bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="ti ti-chart-bar text-xl"></i>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="ti ti-arrow-right text-xs"></i>
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