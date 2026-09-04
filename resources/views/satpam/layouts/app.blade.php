<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#dc2626">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN Satpam">
    <title>@yield('title', 'Satpam') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100">
@php
    $user   = auth()->user();
    $navOn  = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 shadow-lg shadow-red-500/30';
    $navOff = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-red-50 hover:text-red-700 transition-colors';
    $mobOn  = 'text-red-600';
    $mobOff = 'text-gray-400';
@endphp

<div class="min-h-screen" x-data="{ sheet: false }">

    {{-- ================================================== --}}
    {{-- SIDEBAR — DESKTOP SAJA                              --}}
    {{-- ================================================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-72 bg-white border-r border-gray-200 flex-col z-30">

        <div class="flex items-center space-x-3 px-5 py-5 border-b border-gray-100">
            <div class="relative">
              <div class="relative flex items-center justify-center w-14 h-14 rounded-xl bg-[#fbfcf6] shadow-lg  overflow-hidden flex-shrink-0">
                    @if(file_exists(public_path('images/logo-didispen.jpeg')))
                        <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                    @else
                        <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                    @endif
                </div>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-900 tracking-tight">DIDISPEN</h1>
                <p class="text-[11px] text-gray-500 font-medium">Pos Keamanan / Satpam</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="{{ route('satpam.dashboard') }}" class="{{ request()->routeIs('satpam.dashboard') ? $navOn : $navOff }}">
                <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i> Dashboard
            </a>
            <a href="{{ route('satpam.scan') }}"
               class="flex items-center w-full px-4 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 shadow-lg shadow-red-500/30 hover:from-red-700 hover:to-red-800 transition-all {{ request()->routeIs('satpam.scan') ? 'ring-2 ring-red-300' : '' }}">
                <i class="fas fa-qrcode w-5 mr-3 text-center text-base"></i> Scan QR Code
                <i class="fas fa-arrow-right ml-auto text-[11px]"></i>
            </a>
            {{-- PROFIL --}}
            <a href="{{ route('profil.show') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                      {{ request()->routeIs('profil.show') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                <i class="fas fa-user-circle w-5 text-center"></i>
                <span class="font-medium">Profil Saya</span>
            </a>
            <a href="{{ route('panduan') }}" class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                <i class="fas fa-book-open w-5 mr-3 text-center"></i> Panduan
            </a>
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

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-200">
            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-2.5">
                    <div class="relative flex items-center justify-center w-9 h-9 rounded-lg bg-[#fbfcf6] shadow-md overflow-hidden flex-shrink-0">
                        @if(file_exists(public_path('images/logo-didispen.jpeg')))
                            <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                        @else
                            <i class="fas fa-shield-alt text-red-600 text-sm"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-sm font-black text-gray-900 tracking-tight leading-none">DIDISPEN</h1>
                        <p class="text-[11px] text-gray-500 font-medium mt-0.5">@yield('page-title', 'Dashboard')</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Online
                </span>
            </div>
            {{-- Desktop Header --}}
            <div class="hidden lg:flex items-center justify-between px-6 py-3">
                <h2 class="text-base font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-3">
                    <span class="flex items-center text-xs font-semibold text-gray-500">
                        <i class="far fa-calendar mr-1.5 text-red-600"></i> {{ now()->format('d M Y') }}
                    </span>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-red-600 to-rose-500 text-white text-sm font-bold flex items-center justify-center shadow-md shadow-red-500/30">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                            <p class="text-[11px] text-red-600 font-semibold">Petugas Satpam</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- KONTEN --}}
        <main class="flex-1 p-4 sm:p-6 pb-28 lg:pb-6 bg-gradient-to-br from-gray-50 via-white to-red-50/30">
            @include('components.alert')
            @yield('content')
        </main>
    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAV — MOBILE (5 GRID SIMETRIS)             --}}
    {{-- ================================================== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-4px_20px_rgba(220,38,38,0.08)]"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-5 h-16 w-full">

            {{-- 1. Beranda --}}
            <a href="{{ route('satpam.dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('satpam.dashboard') ? $mobOn : $mobOff }}">
                <i class="fas fa-house text-lg"></i>
                <span class="text-[9px] font-bold leading-tight">Beranda</span>
            </a>

            {{-- 2. Verifikasi Manual --}}
            <a href="{{ route('satpam.scan') }}#manual"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ $mobOff }} hover:text-red-600">
                <i class="fas fa-search text-lg"></i>
                <span class="text-[9px] font-bold leading-tight">Manual</span>
            </a>

            {{-- 3. FAB Scan QR (Center - Lebih Besar) --}}
            <div class="relative flex flex-col items-center justify-end pb-1">
                <a href="{{ route('satpam.scan') }}"
                   class="absolute -top-5 w-12 h-12 rounded-full bg-gradient-to-br from-red-600 to-rose-500 text-white text-lg flex items-center justify-center shadow-lg shadow-red-500/40 border-4 border-gray-100 active:scale-95 transition-transform {{ request()->routeIs('satpam.scan') ? 'ring-2 ring-red-300 ring-offset-2' : '' }}">
                    <i class="fas fa-qrcode"></i>
                </a>
                <span class="text-[9px] font-bold leading-tight {{ request()->routeIs('satpam.scan') ? $mobOn : $mobOff }}">Scan</span>
            </div>

            {{-- 4. Panduan --}}
            <a href="{{ route('panduan') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('panduan') ? $mobOn : $mobOff }}">
                <i class="fas fa-book-open text-lg"></i>
                <span class="text-[9px] font-bold leading-tight">Panduan</span>
            </a>

            {{-- 5. Akun --}}
            <button @click="sheet = true"
                    class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ $mobOff }} hover:text-red-600">
                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-red-600 to-rose-500 text-white text-[9px] font-bold flex items-center justify-center shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <span class="text-[9px] font-bold leading-tight">Akun</span>
            </button>
        </div>
    </nav>

    {{-- ================================================== --}}
    {{-- BOTTOM SHEET AKUN (Smooth & Rapi)                 --}}
    {{-- ================================================== --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-40 lg:hidden">
        {{-- Backdrop dengan blur --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="sheet = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Panel Sheet --}}
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl p-6"
             style="padding-bottom: calc(env(safe-area-inset-bottom) + 24px);"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">

            {{-- Handle Bar --}}
            <div class="w-12 h-1.5 rounded-full bg-gray-200 mx-auto mb-6"></div>

            {{-- Header Akun --}}
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-600 to-rose-500 text-white font-bold text-xl flex items-center justify-center shadow-md shadow-red-500/30 flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 text-base truncate">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Petugas Keamanan • Pos Gerbang</p>
                </div>
            </div>

            {{-- Menu --}}
            <div class="space-y-3">
                <a href="{{ route('panduan') }}" @click="sheet = false"
                   class="w-full flex items-center px-4 py-3.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-50 border border-gray-100 hover:bg-gray-100 active:scale-[0.98] transition-all">
                    <i class="fas fa-book-open text-red-600 mr-3 text-lg"></i> Panduan Penggunaan
                </a>
                {{-- PROFIL MOBILE --}}
                <a href="{{ route('profil.show') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all
                          {{ request()->routeIs('profil.show') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fas fa-user-circle w-5 text-center"></i>
                    <span class="font-medium">Profil Saya</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center px-4 py-3.5 rounded-xl text-sm font-bold text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 active:scale-[0.98] transition-all">
                        <i class="fas fa-sign-out-alt mr-3 text-lg"></i> Keluar dari Akun
                    </button>
                </form>
            </div>

            {{-- Tombol Tutup --}}
            <button @click="sheet = false"
                    class="w-full mt-4 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors">
                Tutup
            </button>
        </div>
    </div>

@stack('scripts')

{{-- GLOBAL SWEETALERT NOTIFICATION --}}
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
        background: '#fee2e2',
        color: '#991b1b'
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
