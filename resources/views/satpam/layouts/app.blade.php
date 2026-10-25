<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e11d48">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN Satpam">
    <title>@yield('title', 'Satpam') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="/manifest.json">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('components.sweetalert-theme')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .break-words { word-break: break-word; overflow-wrap: break-word; }
    </style>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-rose-500 selection:text-white min-h-screen">
@php
    $user   = auth()->user();
    $navOn  = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 shadow-xs transition-colors';
    $navOff = 'flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-rose-50/80 hover:text-rose-700 transition-colors';
    $mobOn  = 'text-rose-600 font-bold';
    $mobOff = 'text-slate-400 hover:text-slate-600';

    $isDashboard = request()->routeIs('satpam.dashboard') || request()->routeIs('satpam.dispensasi.*');
    $isScan = request()->routeIs('satpam.scan');
    $isLaporan = request()->routeIs('satpam.laporan.*');
    $isPanduan = request()->routeIs('panduan');
    $isProfil = request()->routeIs('profil.*');

    $countSiswaKeluar = 0;
    $countLaporanMenunggu = 0;
    if ($user && $user->role === 'satpam') {
        $countSiswaKeluar = \App\Models\Dispensasi::where('status', 'keluar')
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->count();
        $countLaporanMenunggu = \App\Models\LaporanSatpam::where('status', 'menunggu')->count();
    }
@endphp

<div class="min-h-screen" x-data="{ sheet: false }">

    {{-- ================================================== --}}
    {{-- SIDEBAR — DESKTOP SAJA                              --}}
    {{-- ================================================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 bg-white border-r border-slate-200 flex-col z-30 shadow-2xs">

        <div class="flex items-center space-x-3 px-5 py-5 border-b border-slate-100">
            <div class="relative flex items-center justify-center w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex-shrink-0 shadow-2xs">
                @if(file_exists(public_path('images/logo-didispen.png')))
                    <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-1">
                @else
                    <i class="ti ti-shield-check text-xl"></i>
                @endif
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-snug">DIDISPEN</h1>
                <p class="text-[10px] text-slate-500 font-medium">Pos Keamanan / Satpam</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1.5">
            <a href="{{ route('satpam.dashboard') }}" class="{{ $isDashboard ? $navOn : $navOff }}">
                <i class="ti ti-dashboard w-5 mr-3 text-center text-lg"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('satpam.scan') }}"
               class="flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-colors {{ $isScan ? 'ring-2 ring-rose-200' : '' }}">
                <i class="ti ti-qrcode w-5 mr-3 text-center text-lg"></i>
                <span>Scan QR Code</span>
                <i class="ti ti-chevron-right ml-auto text-xs"></i>
            </a>
            <a href="{{ route('satpam.laporan.index') }}" class="{{ $isLaporan ? $navOn : $navOff }}">
                <i class="ti ti-shield-exclamation w-5 mr-3 text-center text-lg"></i>
                <span>Laporan Temuan</span>
                @if($countLaporanMenunggu > 0)
                    <span class="ml-auto min-w-[18px] h-4 px-1 rounded-full bg-amber-500 text-white text-[9px] font-bold flex items-center justify-center">
                        {{ $countLaporanMenunggu }}
                    </span>
                @endif
            </a>
            <a href="{{ route('profil.show') }}"
               class="flex items-center w-full px-4 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ $isProfil ? 'bg-rose-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-rose-50/80 hover:text-rose-700' }}">
                <i class="ti ti-user w-5 mr-3 text-center text-lg"></i>
                <span>Profil Saya</span>
            </a>
            <a href="{{ route('panduan') }}" class="{{ $isPanduan ? $navOn : $navOff }}">
                <i class="ti ti-book-2 w-5 mr-3 text-center text-lg"></i>
                <span>Panduan</span>
            </a>
        </nav>

        <div class="p-3.5 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 border border-rose-200 bg-rose-50/40 hover:bg-rose-50 transition-colors">
                    <i class="ti ti-logout mr-2 text-base"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ================================================== --}}
    {{-- MAIN AREA                                          --}}
    {{-- ================================================== --}}
    <div class="flex flex-col min-h-screen lg:pl-64">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-slate-200/90 shadow-2xs">
            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-2.5">
                    <div class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex-shrink-0 shadow-2xs">
                        @if(file_exists(public_path('images/logo-didispen.png')))
                            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                        @else
                            <i class="ti ti-shield-check text-base"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-none">DIDISPEN</h1>
                        <p class="text-[10px] text-slate-500 font-medium mt-0.5">@yield('page-title', 'Dashboard')</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online
                    </span>
            </div>
            {{-- Desktop Header --}}
            <div class="hidden lg:flex items-center justify-between px-6 py-3.5">
                <h2 class="text-base font-bold text-slate-900">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-3.5">
                    <span class="flex items-center text-xs font-medium text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                        <i class="ti ti-calendar mr-1.5 text-rose-600 text-sm"></i> {{ now()->format('d M Y') }}
                    </span>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-600 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="text-[10px] text-rose-600 font-semibold">Petugas Satpam</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- KONTEN --}}
        <main class="flex-1 p-4 sm:p-6 pb-32 sm:pb-36 lg:pb-8">
            @include('components.alert')
            @yield('content')
        </main>
    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAV — MOBILE (HP-FRIENDLY, SELALU DI BAWAH)  --}}
    {{-- ================================================== --}}
    <nav id="mobileBottomNav"
         class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] transform-gpu transition-all duration-200"
         style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
        <div class="grid grid-cols-5 h-16 w-full max-w-md mx-auto items-center">
            {{-- 1. Beranda --}}
            <a href="{{ route('satpam.dashboard') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-95 {{ $isDashboard ? $mobOn : $mobOff }}">
                <div class="relative">
                    <i class="ti ti-smart-home text-xl"></i>
                    @if($countSiswaKeluar > 0)
                        <span class="absolute -top-1 -right-2.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white shadow-xs" title="{{ $countSiswaKeluar }} siswa sedang di luar">
                            {{ $countSiswaKeluar > 99 ? '99+' : $countSiswaKeluar }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Beranda</span>
            </a>

            {{-- 2. Laporan Temuan --}}
            <a href="{{ route('satpam.laporan.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-95 {{ $isLaporan ? $mobOn : $mobOff }}">
                <div class="relative">
                    <i class="ti ti-shield-exclamation text-xl"></i>
                    @if($countLaporanMenunggu > 0)
                        <span class="absolute -top-1 -right-2 min-w-[14px] h-3.5 px-0.5 rounded-full bg-amber-500 text-white text-[8px] font-bold flex items-center justify-center border border-white">
                            {{ $countLaporanMenunggu }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Laporan</span>
            </a>

            {{-- 3. Center FAB: Scan QR --}}
            <div class="relative flex flex-col items-center justify-end h-full pb-1">
                <a href="{{ route('satpam.scan') }}"
                   aria-label="Scan QR Code"
                   class="absolute -top-5 w-12 h-12 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xl flex items-center justify-center shadow-lg shadow-rose-600/35 border-4 border-slate-50 active:scale-95 transition-all duration-200 {{ $isScan ? 'ring-4 ring-rose-500/25 scale-105' : '' }}">
                    <i class="ti ti-qrcode"></i>
                </a>
                <span class="text-[10px] leading-tight tracking-tight mt-auto {{ $isScan ? 'font-bold text-rose-600' : 'font-semibold text-slate-500' }}">Scan</span>
            </div>

            {{-- 4. Manual --}}
            <a href="{{ route('satpam.scan') }}#manual"
               id="navManualBtn"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-95 {{ $mobOff }} hover:text-rose-600">
                <i class="ti ti-search text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Manual</span>
            </a>

            {{-- 5. Akun --}}
            <button type="button"
                    @click="sheet = true"
                    class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-95 {{ $isProfil ? $mobOn : $mobOff }}">
                <div class="w-6 h-6 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold flex items-center justify-center shadow-2xs">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Akun</span>
            </button>
        </div>
    </nav>

    {{-- ================================================== --}}
    {{-- BOTTOM SHEET AKUN                                   --}}
    {{-- ================================================== --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-[60] lg:hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" @click="sheet = false"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl p-5 sm:p-6 shadow-2xl border-t border-slate-100" style="padding-bottom: calc(env(safe-area-inset-bottom) + 24px);"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">

            <div class="w-12 h-1.5 rounded-full bg-slate-200 mx-auto mb-5"></div>

            <div class="flex items-center space-x-3.5 mb-5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-700 font-bold text-lg flex items-center justify-center flex-shrink-0 shadow-2xs">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-900 text-sm sm:text-base break-words">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Petugas Keamanan • Pos Gerbang</span>
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('panduan') }}" @click="sheet = false" class="w-full min-h-[46px] flex items-center px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors">
                    <i class="ti ti-book-2 text-rose-600 mr-3 text-lg"></i>
                    <span>Panduan Penggunaan</span>
                </a>
                <a href="{{ route('profil.show') }}" class="flex items-center min-h-[46px] px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold transition-colors {{ request()->routeIs('profil.show') ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-700 bg-slate-50 border border-slate-200 hover:bg-rose-50 hover:text-rose-700' }}">
                    <i class="ti ti-user w-5 mr-3 text-center text-lg"></i>
                    <span>Profil Saya</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full min-h-[46px] flex items-center justify-center px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors mt-2">
                        <i class="ti ti-logout mr-2 text-base"></i> Keluar dari Akun
                    </button>
                </form>
            </div>

            <button type="button" @click="sheet = false" class="w-full min-h-[44px] mt-4 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                Tutup
            </button>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bottomNav = document.getElementById('mobileBottomNav');
    if (!bottomNav) return;

    // Sembunyikan halus saat keyboard virtual HP terbuka agar tidak menutupi input
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () {
            const isKeyboardOpen = (window.innerHeight - window.visualViewport.height) > 150;
            if (isKeyboardOpen) {
                bottomNav.style.transform = 'translateY(100%)';
                bottomNav.style.opacity = '0';
                bottomNav.style.pointerEvents = 'none';
            } else {
                bottomNav.style.transform = 'translateY(0)';
                bottomNav.style.opacity = '1';
                bottomNav.style.pointerEvents = 'auto';
            }
        });
    }

    // Deteksi hash #manual untuk penandaan navigasi & auto-focus
    function checkHashManual() {
        if (window.location.hash === '#manual') {
            const manualBtn = document.getElementById('navManualBtn');
            if (manualBtn) {
                manualBtn.classList.remove('text-slate-400');
                manualBtn.classList.add('text-rose-600', 'font-bold');
            }
            const manualInput = document.getElementById('manualCode');
            if (manualInput) {
                setTimeout(() => manualInput.focus(), 300);
            }
        }
    }
    checkHashManual();
    window.addEventListener('hashchange', checkHashManual);
});
</script>
@stack('scripts')
</body>
</html>

