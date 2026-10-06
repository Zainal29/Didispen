@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Overview')

@section('content')

{{-- =========================================================
    DASHBOARD ADMIN - DIDISPEN
========================================================= --}}

<div class="min-h-screen bg-[#f6f8fc] -m-4 sm:-m-6 p-4 sm:p-6">

    {{-- =====================================================
        HEADER
    ====================================================== --}}
    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5 mb-7">

        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span>

                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-indigo-600">
                    Dashboard Overview
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>

            <p class="text-sm text-slate-500 mt-1.5">
                Ringkasan aktivitas dispensasi dan kondisi sistem hari ini.
            </p>
        </div>

        <div class="flex items-center gap-3">

            {{-- Date --}}
            <div class="hidden sm:flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-calendar-day"></i>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Hari ini
                    </p>

                    <p class="text-sm font-semibold text-slate-700">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Live indicator --}}
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-50"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>

                <span class="text-xs font-semibold text-slate-600">
                    Sistem Aktif
                </span>
            </div>

        </div>
    </div>


    {{-- =====================================================
        STATISTIC CARDS - STATUS DISPENSASI
    ====================================================== --}}
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                Aktivitas Dispensasi
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-7 gap-3 sm:gap-4 mb-6">
        {{-- MENUNGGU --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Menunggu
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['menunggu'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-amber-50 border border-amber-100 text-amber-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-hourglass-half text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Perlu ditindak</span>
                </div>
            </div>
        </div>

        {{-- DISETUJUI --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Disetujui
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['disetujui'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-circle-check text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Siap / diizinkan</span>
                </div>
            </div>
        </div>

        {{-- SEDANG KELUAR --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-sky-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Sedang Keluar
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['keluar'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-person-walking-arrow-right text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    <span>Di luar sekolah</span>
                </div>
            </div>
        </div>

        {{-- SELESAI --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-blue-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Selesai
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['selesai'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-blue-50 border border-blue-100 text-blue-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-flag-checkered text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Sudah kembali</span>
                </div>
            </div>
        </div>

        {{-- TERLAMBAT --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Terlambat
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-red-600 mt-2 tracking-tight">
                            {{ $stats['terlambat'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-red-50 border border-red-100 text-red-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-triangle-exclamation text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-red-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    <span>Melewati batas</span>
                </div>
            </div>
        </div>

        {{-- DITOLAK --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-rose-500"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Ditolak
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['ditolak'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-circle-xmark text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    <span>Tidak disetujui</span>
                </div>
            </div>
        </div>

        {{-- DIBATALKAN --}}
        <div class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-slate-400"></div>
            <div class="p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                            Dibatalkan
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                            {{ $stats['dibatalkan'] }}
                        </h2>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fas fa-ban text-base sm:text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>Batal diajukan</span>
                </div>
            </div>
        </div>
    </div>


    {{-- =====================================================
        DATA MASTER & PENGGUNA
    ====================================================== --}}
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                Data Master & Pengguna
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        {{-- TOTAL SISWA --}}
        <!-- <a
            href="{{ route('admin.siswa.index') }}"
            class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >
            <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                        Total Siswa
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                        {{ number_format($stats['total_siswa'], 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="fas fa-user-graduate text-base sm:text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>Siswa terdaftar aktif</span>
                </div>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                    Kelola <i class="fas fa-chevron-right text-[9px]"></i>
                </span>
            </div>
        </a> -->

        {{-- TOTAL GURU --}}
        <!-- <a
            href="{{ route('admin.guru.index') }}"
            class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >
            <div class="absolute top-0 left-0 right-0 h-1 bg-violet-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                        Total Guru
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                        {{ number_format($stats['total_guru'], 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="fas fa-chalkboard-user text-base sm:text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                    <span>Tenaga pengajar & piket</span>
                </div>
                <span class="text-violet-600 font-semibold group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                    Kelola <i class="fas fa-chevron-right text-[9px]"></i>
                </span>
            </div>
        </a> -->

        {{-- TOTAL SATPAM --}}
        <!-- <a
            href="{{ route('admin.satpam.index') }}"
            class="group relative overflow-hidden bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >
            <div class="absolute top-0 left-0 right-0 h-1 bg-teal-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">
                        Total Satpam
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">
                        {{ number_format($stats['total_satpam'], 0, ',', '.') }}
                    </h2>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="fas fa-shield-halved text-base sm:text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    <span>Petugas pos keamanan</span>
                </div>
                <span class="text-teal-600 font-semibold group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                    Kelola <i class="fas fa-chevron-right text-[9px]"></i>
                </span>
            </div>
        </a> -->
    </div>


    {{-- =====================================================
        MAIN GRID
    ====================================================== --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 mb-6">


        {{-- =================================================
            CHART
        ================================================== --}}
        <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            {{-- Chart Header --}}
            <div class="px-5 sm:px-6 py-5 border-b border-slate-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-chart-column"></i>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                Aktivitas Pengajuan
                            </h3>

                            <p class="text-xs text-slate-400 mt-0.5">
                                Jumlah pengajuan dalam 7 hari terakhir
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Real-time
                        </span>

                    </div>

                </div>

            </div>


            {{-- Chart --}}
            <div class="p-5 sm:p-6">

                <div class="relative h-[280px] sm:h-[320px]">
                    <canvas id="chartPengajuan"></canvas>
                </div>

            </div>

        </div>


        {{-- =================================================
            RECENT ACTIVITY
        ================================================== --}}
        <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-5 py-5 border-b border-slate-100 flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fas fa-list-ul"></i>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Pengajuan Terbaru
                        </h3>

                        <p class="text-xs text-slate-400 mt-0.5">
                            Aktivitas terakhir
                        </p>
                    </div>

                </div>

                <a
                    href="{{ route('admin.semua.pengajuan') }}"
                    class="w-8 h-8 rounded-lg hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 flex items-center justify-center transition-colors"
                    title="Lihat semua"
                >
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                </a>

            </div>


            {{-- Activity --}}
            <div class="px-5">

                @forelse($recent as $item)

                    <div class="group flex items-center gap-3 py-4 border-b border-slate-100 last:border-0">

                        {{-- Avatar --}}
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-indigo-50 to-slate-100 border border-slate-200 flex items-center justify-center text-indigo-600 font-bold text-xs">
                            {{ strtoupper(substr($item->siswa?->nama_lengkap ?? '?', 0, 2)) }}
                        </div>


                        {{-- Info --}}
                        <div class="flex-1 min-w-0">

                            <p class="text-sm font-semibold text-slate-800 truncate">
                                {{ $item->siswa?->nama_lengkap ?? '-' }}
                            </p>

                            <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-400 truncate">
                                <span>
                                    {{ $item->siswa?->kelas?->nama_kelas ?? '-' }}
                                </span>

                                <span class="text-slate-300">•</span>

                                <span class="truncate">
                                    {{ ucfirst($item->kategori) }}
                                </span>
                            </div>

                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ $item->created_at->diffForHumans() }}
                            </p>

                        </div>


                        {{-- Status --}}
                        @php
                            $statusConfig = [
                                'menunggu' => [
                                    'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'dot' => 'bg-amber-500',
                                    'label' => 'Menunggu',
                                ],
                                'disetujui' => [
                                    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'dot' => 'bg-emerald-500',
                                    'label' => 'Disetujui',
                                ],
                                'keluar' => [
                                    'class' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'dot' => 'bg-sky-500',
                                    'label' => 'Sedang Keluar',
                                ],
                                'selesai' => [
                                    'class' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'dot' => 'bg-blue-500',
                                    'label' => 'Selesai',
                                ],
                                'ditolak' => [
                                    'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'dot' => 'bg-rose-500',
                                    'label' => 'Ditolak',
                                ],
                                'dibatalkan' => [
                                    'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    'dot' => 'bg-slate-400',
                                    'label' => 'Dibatalkan',
                                ],
                                'kadaluarsa' => [
                                    'class' => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                                    'dot' => 'bg-zinc-400',
                                    'label' => 'Kadaluarsa',
                                ],
                            ];

                            if ($item->status === 'keluar' && method_exists($item, 'isOverdue') && $item->isOverdue()) {
                                $status = [
                                    'class' => 'bg-red-50 text-red-700 border-red-200',
                                    'dot' => 'bg-red-500',
                                    'label' => 'Terlambat',
                                ];
                            } else {
                                $status = $statusConfig[$item->status] ?? [
                                    'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                    'dot' => 'bg-slate-400',
                                    'label' => ucfirst($item->status),
                                ];
                            }
                        @endphp

                        <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] font-bold {{ $status['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                            {{ $status['label'] }}
                        </span>

                    </div>

                @empty

                    <div class="py-12 text-center">

                        <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <i class="fas fa-inbox"></i>
                        </div>

                        <p class="text-sm font-medium text-slate-500">
                            Belum ada pengajuan
                        </p>

                        <p class="text-xs text-slate-400 mt-1">
                            Aktivitas baru akan muncul di sini.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- Footer --}}
            @if($recent->count())

                <div class="px-5 py-4 border-t border-slate-100">

                    <a
                        href="{{ route('admin.semua.pengajuan') }}"
                        class="group flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors"
                    >
                        Lihat Semua Pengajuan

                        <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
        QUICK ACCESS
    ====================================================== --}}
    <div class="mb-3">

        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
            Akses Cepat
        </p>

    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Semua Pengajuan --}}
        <a
            href="{{ route('admin.semua.pengajuan') }}"
            class="group relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >

            <div class="flex items-start justify-between">

                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-file-lines"></i>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-indigo-600 group-hover:text-white text-slate-400 flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </div>

            </div>

            <h4 class="text-sm font-bold text-slate-900 mt-4">
                Semua Pengajuan
            </h4>

            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                Kelola dan pantau seluruh pengajuan dispensasi.
            </p>

        </a>


        {{-- Data Siswa --}}
        <a
            href="{{ route('admin.siswa.index') }}"
            class="group relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >

            <div class="flex items-start justify-between">

                <div class="w-11 h-11 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-cyan-600 group-hover:text-white text-slate-400 flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </div>

            </div>

            <h4 class="text-sm font-bold text-slate-900 mt-4">
                Data Siswa
            </h4>

            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                Kelola data siswa, kelas, dan informasi akademik.
            </p>

        </a>


        {{-- Data Guru --}}
        <a
            href="{{ route('admin.guru.index') }}"
            class="group relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >

            <div class="flex items-start justify-between">

                <div class="w-11 h-11 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <i class="fas fa-chalkboard-user"></i>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-violet-600 group-hover:text-white text-slate-400 flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </div>

            </div>

            <h4 class="text-sm font-bold text-slate-900 mt-4">
                Data Guru
            </h4>

            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                Kelola data guru dan kebutuhan administrasi.
            </p>

        </a>


        {{-- Laporan --}}
        <a
            href="{{ route('admin.laporan.index') }}"
            class="group relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
        >

            <div class="flex items-start justify-between">

                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-chart-line"></i>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-50 group-hover:bg-blue-600 group-hover:text-white text-slate-400 flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-right text-xs"></i>
                </div>

            </div>

            <h4 class="text-sm font-bold text-slate-900 mt-4">
                Laporan
            </h4>

            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                Lihat laporan dan rekapitulasi dispensasi.
            </p>

        </a>

    </div>

</div>

@endsection


{{-- =========================================================
    CHART SCRIPT
========================================================= --}}
@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const canvas = document.getElementById('chartPengajuan');

    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 330);

    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.90)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.30)');


    new Chart(ctx, {

        type: 'bar',

        data: {
            labels: @json($dates),

            datasets: [
                {
                    label: 'Pengajuan',

                    data: @json($counts),

                    backgroundColor: gradient,

                    borderColor: '#4f46e5',

                    borderWidth: 0,

                    borderRadius: 7,

                    borderSkipped: false,

                    barPercentage: 0.62,

                    categoryPercentage: 0.72,

                    maxBarThickness: 44
                }
            ]
        },


        options: {

            responsive: true,

            maintainAspectRatio: false,

            interaction: {
                intersect: false,
                mode: 'index'
            },

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    backgroundColor: '#0f172a',

                    titleColor: '#ffffff',

                    bodyColor: '#cbd5e1',

                    padding: 12,

                    cornerRadius: 10,

                    displayColors: false,

                    titleFont: {
                        size: 12,
                        weight: '600'
                    },

                    bodyFont: {
                        size: 12,
                        weight: '500'
                    },

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

                    suggestedMax: Math.max(...@json($counts), 5) + 5,

                    border: {
                        display: false
                    },

                    ticks: {

                        stepSize: 1,

                        color: '#94a3b8',

                        font: {
                            size: 10,
                            weight: '500'
                        },

                        padding: 8
                    },

                    grid: {

                        color: 'rgba(148, 163, 184, 0.12)',

                        drawTicks: false
                    }
                },


                x: {

                    border: {
                        display: false
                    },

                    ticks: {

                        color: '#64748b',

                        font: {
                            size: 10,
                            weight: '600'
                        },

                        padding: 8
                    },

                    grid: {
                        display: false
                    }
                }
            }
        }

    });

});
</script>

@endpush