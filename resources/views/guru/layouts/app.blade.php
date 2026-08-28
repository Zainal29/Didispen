<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN Guru">
    <title>@yield('title', 'Guru') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100">
@php
    $user    = auth()->user();
    $pending = $stats['pending'] ?? 0;
    $keluar  = isset($siswaKeluar) ? $siswaKeluar->count() : 0;
    $navOn   = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg shadow-blue-500/30';
    $navOff  = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors';
    $mobOn   = 'text-blue-600';
    $mobOff  = 'text-gray-400';
@endphp

<div class="min-h-screen" x-data="{ sheet: false }">

    {{-- ================================================== --}}
    {{-- SIDEBAR — DESKTOP SAJA                              --}}
    {{-- ================================================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-72 bg-white border-r border-gray-200 flex-col z-30">

        <div class="flex items-center space-x-3 px-5 py-5 border-b border-gray-100">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-sky-500 rounded-xl blur-md opacity-40"></div>
                <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-500/40">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-900 tracking-tight">DIDISPEN</h1>
                <p class="text-[11px] text-gray-500 font-medium">Panel Guru</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
            <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? $navOn : $navOff }}">
                <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i> Dashboard
            </a>
            <a href="{{ route('guru.checklog.index') }}" class="{{ request()->routeIs('guru.checklog.*') ? $navOn : $navOff }}">
                <i class="fas fa-door-open w-5 mr-3 text-center"></i> Keluar/Masuk
            </a>

            <p class="px-4 mb-2 mt-5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Dispensasi</p>
            <div class="space-y-1">
                <a href="{{ route('guru.pengajuan.index') }}" class="{{ request()->routeIs('guru.pengajuan.*') ? $navOn : $navOff }}">
                    <i class="fas fa-file-signature w-5 mr-3 text-center"></i> Verifikasi
                    @if($pending > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
                    @endif
                </a>

                <a href="{{ route('guru.scan') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-colors">
                    <i class="fas fa-qrcode w-6 text-center"></i>
                    <span class="font-medium">Scan QR (Backup)</span>
                </a>

                <a href="{{ route('guru.pengajuan.create') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-colors">
                    <i class="fas fa-plus-circle w-6 text-center"></i>
                    <span class="font-medium">Buat Dispensasi</span>
                </a>
                <a href="{{ route('guru.laporan.index') }}" class="{{ request()->routeIs('guru.laporan.*') ? $navOn : $navOff }}">
                    <i class="fas fa-chart-bar w-5 mr-3 text-center"></i> Laporan
                </a>
                <a href="{{ route('panduan') }}" class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                    <i class="fas fa-book-open w-5 mr-3 text-center"></i> Panduan
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-red-600 border border-red-200 hover:bg-red-50 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ================================================== --}}
    {{-- MAIN AREA                                          --}}
    {{-- ================================================== --}}
    <div class="flex flex-col min-h-screen lg:pl-72">

        <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-200">
            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-sky-500 rounded-lg blur-sm opacity-40"></div>
                        <div class="relative w-9 h-9 rounded-lg bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center text-sm shadow-md shadow-blue-500/30">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-black text-gray-900 tracking-tight leading-none">DIDISPEN</h1>
                        <p class="text-[11px] text-gray-500 font-medium mt-0.5 truncate">@yield('page-title', 'Dashboard')</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>Online
                </span>
            </div>
            {{-- Desktop Header --}}
            <div class="hidden lg:flex items-center justify-between px-6 py-3">
                <h2 class="text-base font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-3">
                    <span class="flex items-center text-xs font-semibold text-gray-500">
                        <i class="far fa-calendar mr-1.5 text-blue-600"></i> {{ now()->format('d M Y') }}
                    </span>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white text-sm font-bold flex items-center justify-center shadow-md shadow-blue-500/30">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                            <p class="text-[11px] text-blue-600 font-semibold">Guru Piket</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 pb-28 lg:pb-6 bg-gradient-to-br from-gray-50 via-white to-blue-50/40">
            @yield('content')
        </main>
    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAV — MOBILE                                --}}
    {{-- ================================================== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(37,99,235,0.08)]"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-4 h-16">

            {{-- Beranda --}}
            <a href="{{ route('guru.dashboard') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('guru.dashboard') ? $mobOn : $mobOff }}">
                <i class="fas fa-house text-lg"></i>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>

            {{-- Keluar/Masuk (Checklog) --}}
            <a href="{{ route('guru.checklog.index') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('guru.checklog.*') ? $mobOn : $mobOff }}">
                <i class="fas fa-door-open text-lg"></i>
                <span class="text-[10px] font-bold">Keluar/Masuk</span>
            </a>

            {{-- FAB Verifikasi --}}
            <div class="relative flex flex-col items-center justify-end pb-1.5">
                <a href="{{ route('guru.pengajuan.index') }}"
                   class="absolute -top-6 w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white text-xl flex items-center justify-center shadow-lg shadow-blue-500/40 border-4 border-gray-100 active:scale-95 transition-transform {{ request()->routeIs('guru.pengajuan.*') ? 'ring-2 ring-blue-300' : '' }}">
                    <i class="fas fa-clipboard-check"></i>
                    @if($pending > 0)
                        <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border-2 border-white">{{ $pending }}</span>
                    @endif
                </a>
                <span class="text-[10px] font-bold {{ request()->routeIs('guru.pengajuan.*') ? $mobOn : $mobOff }}">Verifikasi</span>
            </div>



            {{-- Akun (bottom sheet) --}}
            <button @click="sheet = true" class="flex flex-col items-center justify-center gap-1 text-gray-400">
                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white text-[10px] font-bold flex items-center justify-center">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <span class="text-[10px] font-bold">Akun</span>
            </button>
        </div>
    </nav>

        {{-- BOTTOM SHEET AKUN --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="absolute inset-0 bg-black/40" @click="sheet = false"></div>
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl p-6"
             style="padding-bottom: calc(env(safe-area-inset-bottom) + 24px);"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
            <div class="w-10 h-1 rounded-full bg-gray-200 mx-auto mb-5"></div>

            {{-- Header akun --}}
            <div class="flex items-center space-x-3 mb-5">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white font-bold flex items-center justify-center shadow-md shadow-blue-500/30">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">Guru Piket</p>
                </div>
            </div>

            {{-- ✅ GRID 3 TILE: Laporan, Verifikasi, Panduan (rapi satu baris) --}}
            <div class="grid grid-cols-3 gap-2 mb-2">
                <a href="{{ route('guru.laporan.index') }}" @click="sheet = false"
                   class="px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 text-center">
                    <i class="fas fa-chart-bar mr-1"></i> Laporan
                </a>
                <a href="{{ route('guru.pengajuan.index') }}" @click="sheet = false"
                   class="px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 text-center">
                    <i class="fas fa-file-signature mr-1"></i> Verifikasi
                </a>
                <a href="{{ route('panduan') }}" @click="sheet = false"
                   class="px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 text-center">
                    <i class="fas fa-book-open mr-1"></i> Panduan
                </a>
            </div>



            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar dari Akun
                </button>
            </form>
            <button @click="sheet = false" class="w-full mt-2 px-4 py-3 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors">Tutup</button>
        </div>
    </div>
</div>

@stack('scripts')

{{-- ✅ GLOBAL SWEETALERT NOTIFICATION --}}
{{-- @if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
        background: '#d1fae5',
        color: '#065f46'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        timer: 4000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
        background: '#fee2e2',
        color: '#991b1b'
    });
</script>
@endif --}}
</body>
</html>
