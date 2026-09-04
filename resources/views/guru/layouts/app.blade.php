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
               <div class="relative flex items-center justify-center w-14 h-14 rounded-xl bg-[#fbfcf6] shadow-lg overflow-hidden flex-shrink-0">
                    @if(file_exists(public_path('images/logo-didispen.jpeg')))
                        <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                    @else
                        <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                    @endif
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
                {{-- PROFIL --}}
                <a href="{{ route('profil.show') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                          {{ request()->routeIs('profil.show') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fas fa-user-circle w-5 text-center"></i>
                    <span class="font-medium">Profil Saya</span>
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
                        <div class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-[#fbfcf6] shadow-md overflow-hidden flex-shrink-0">
                            @if(file_exists(public_path('images/logo-didispen.jpeg')))
                                <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                            @else
                                <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                            @endif
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
    {{-- BOTTOM NAV — MOBILE (5 GRID SIMETRIS)             --}}
    {{-- ================================================== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(37,99,235,0.08)]"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-5 h-16">

            {{-- 1. Beranda --}}
            <a href="{{ route('guru.dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('guru.dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                <i class="fas fa-house text-lg"></i>
                <span class="text-[9px] font-bold">Beranda</span>
            </a>

            {{-- 2. Keluar/Masuk --}}
            <a href="{{ route('guru.checklog.index') }}"
               class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('guru.checklog.*') ? 'text-blue-600' : 'text-gray-400' }}">
                <i class="fas fa-door-open text-lg"></i>
                <span class="text-[9px] font-bold">Keluar/Masuk</span>
            </a>

            {{-- 3. FAB Verifikasi (Center - Lebih Besar) --}}
            <div class="relative flex flex-col items-center justify-end pb-1">
                <a href="{{ route('guru.pengajuan.index') }}"
                   class="absolute -top-5 w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white text-lg flex items-center justify-center shadow-lg shadow-blue-500/40 border-4 border-gray-100 active:scale-95 transition-transform {{ request()->routeIs('guru.pengajuan.*') ? 'ring-2 ring-blue-300' : '' }}">
                    <i class="fas fa-clipboard-check"></i>
                    @if($pending > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-4 px-1 rounded-full bg-red-500 text-white text-[8px] font-bold flex items-center justify-center border-2 border-white">{{ $pending }}</span>
                    @endif
                </a>
                <span class="text-[9px] font-bold {{ request()->routeIs('guru.pengajuan.*') ? 'text-blue-600' : 'text-gray-400' }}">Verifikasi</span>
            </div>

            {{-- 4. Scan QR --}}
            <a href="{{ route('guru.scan') }}"
               class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('guru.scan') ? 'text-blue-600' : 'text-gray-400' }}">
                <i class="fas fa-qrcode text-lg"></i>
                <span class="text-[9px] font-bold">Scan QR</span>
            </a>

            {{-- 5. Akun --}}
            <button @click="sheet = true"
                    class="flex flex-col items-center justify-center gap-0.5 text-gray-400">
                <span class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white text-[10px] font-bold flex items-center justify-center shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <span class="text-[9px] font-bold">Akun</span>
            </button>
        </div>
    </nav>

    {{-- BOTTOM SHEET AKUN --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="sheet = false"></div>
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl p-6 max-h-[85vh] overflow-y-auto"
             style="padding-bottom: calc(env(safe-area-inset-bottom) + 24px);"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">

            {{-- Handle --}}
            <div class="w-12 h-1.5 rounded-full bg-gray-300 mx-auto mb-6"></div>

            {{-- Header akun --}}
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white font-bold text-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <p class="font-bold text-gray-900 text-base">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">Guru Piket</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Menu Grid 2x2 --}}
            <div class="grid grid-cols-2 gap-3 mb-6">
                <a href="{{ route('guru.pengajuan.index') }}" @click="sheet = false"
                   class="flex items-center px-4 py-3 rounded-xl bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-file-siMOBIgnature"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-900">Verifikasi</p>
                        <p class="text-[10px] text-blue-600">Dispensasi</p>
                    </div>
                </a>

                <a href="{{ route('guru.laporan.MOBIindex') }}" @click="sheet = false"
                   class="flex items-center px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-900">Laporan</p>
                        <p class="text-[10px] text-emerald-600">Statistik</p>
                    </div>
                </a>

                <a href="{{ route('guru.scan') }}" @click="sheet = false"
                   class="flex items-center px-4 py-3 rounded-xl bg-purple-50 border border-purple-100 hover:bg-purple-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-purple-900">Scan QR</p>
                        <p class="text-[10px] text-purple-600">Backup</p>
                    </div>
                </a>

                {{-- PROFIL MOBILE --}}
                <a href="{{ route('profil.show') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                          {{ request()->routeIs('profil.show') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fas fa-user-circle w-5 text-center"></i>
                    <span class="font-medium">Profil Saya</span>
                </a>

                <a href="{{ route('panduan') }}" @click="sheet = false"
                   class="flex items-center px-4 py-3 rounded-xl bg-amber-50 border border-amber-100 hover:bg-amber-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center mr-3">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900">Panduan</p>
                        <p class="text-[10px] text-amber-600">Bantuan</p>
                    </div>
                </a>
            </div>

            {{-- Divider --}}
            <div class="border-t border-gray-200 my-4"></div>

            {{-- Logout Button --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-3.5 rounded-xl text-sm font-bold text-red-600 border-2 border-red-200 bg-red-50 hover:bg-red-100 active:bg-red-200 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar dari Akun
                </button>
            </form>

            {{-- Close Button --}}
            <button @click="sheet = false"
                    class="w-full mt-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                Tutup
            </button>
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
