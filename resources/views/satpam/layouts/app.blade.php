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

        @keyframes fabGlowSatpam {
            0%, 100% { box-shadow: 0 10px 24px -3px rgba(225, 29, 72, 0.5), 0 0 0 4px rgba(225, 29, 72, 0.2); }
            50% { box-shadow: 0 14px 28px -3px rgba(225, 29, 72, 0.65), 0 0 0 6px rgba(225, 29, 72, 0.3); }
        }
        .fab-active-glow {
            animation: fabGlowSatpam 2.5s infinite ease-in-out;
        }

        /* Sembunyikan bottom bar saat keyboard virtual terbuka */
        body.keyboard-open #mobileBottomNav {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(140%) !important;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease !important;
        }

        /* Sembunyikan bottom bar secara murni & instan via CSS native saat modal, popup, SweetAlert aktif */
        body.modal-open #mobileBottomNav,
        body.camera-open #mobileBottomNav,
        body.swal2-shown #mobileBottomNav,
        body:has(.swal2-container) #mobileBottomNav,
        body:has(#loading-overlay:not(.hidden)) #mobileBottomNav,
        body:has(#scanModal:not(.hidden)) #mobileBottomNav,
        body:has(#modalManualGuru:not(.hidden)) #mobileBottomNav,
        body:has(#photoModal:not(.hidden)) #mobileBottomNav,
        body:has(#modalBuatLaporan:not([style*="display: none"]):not([style*="display:none"])) #mobileBottomNav,
        body:has(#securityModal:not([style*="display: none"])) #mobileBottomNav,
        body:has([id*="loading"]:not(.hidden)) #mobileBottomNav,
        body:has([role="dialog"]:not(.hidden):not([style*="display: none"]):not([style*="display:none"])) #mobileBottomNav {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(140%) !important;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease !important;
        }
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
            <div class="relative flex items-center justify-center w-12 h-12 rounded-xl bg-rose-50/80 border border-rose-100 text-rose-600 flex-shrink-0 shadow-2xs p-1">
                @if(file_exists(public_path('images/logo-didispen.png')))
                    <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
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

        {{-- TOPBAR (Normal di atas halaman — ikut tergulung saat scroll agar tidak menghalangi konten) --}}
        <header class="w-full bg-white border-b border-slate-200/90 shadow-xs">
            {{-- Mobile Header (h-16) --}}
            <div class="lg:hidden flex items-center justify-between px-4 h-16">
                <div class="flex items-center space-x-2.5 min-w-0">
                    @if(!$isDashboard)
                        <a href="{{ route('satpam.dashboard') }}"
                           class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 flex items-center justify-center transition-colors -ml-1 shrink-0"
                           title="Kembali ke Dashboard">
                            <i class="ti ti-arrow-left text-base"></i>
                        </a>
                    @endif
                    <div class="relative flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-rose-50/80 border border-rose-100 text-rose-600 flex-shrink-0 shadow-2xs p-1">
                        @if(file_exists(public_path('images/logo-didispen.png')))
                            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
                        @else
                            <i class="ti ti-shield-check text-xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-none truncate">DIDISPEN</h1>
                        <p class="text-[10px] text-slate-500 font-medium mt-0.5 truncate">@yield('page-title', 'Dashboard')</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online
                </span>
            </div>
            {{-- Desktop Header (h-16) --}}
            <div class="hidden lg:flex items-center justify-between px-6 h-16">
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
    {{-- BOTTOM NAV — FLOATING DOCK NAVIGATION (MODERN MOBILE DOCK) --}}
    {{-- ================================================== --}}
    <nav id="mobileBottomNav"
         aria-label="Navigasi Utama Mobile"
         class="lg:hidden fixed inset-x-3 sm:inset-x-6 max-w-md mx-auto z-50 rounded-2xl sm:rounded-3xl bg-white/90 backdrop-blur-xl border border-white/80 shadow-[0_12px_36px_-6px_rgba(15,23,42,0.14),0_0_0_1px_rgba(226,232,240,0.85)] transform-gpu transition-all duration-200"
         style="bottom: max(env(safe-area-inset-bottom, 0px) + 10px, 14px);">
        <div class="grid grid-cols-5 h-16 w-full items-center px-1.5 sm:px-2">
            {{-- 1. Beranda --}}
            <a href="{{ route('satpam.dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 py-1 rounded-xl transition-all active:scale-95 {{ $isDashboard ? 'text-rose-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <div class="relative flex items-center justify-center">
                    <i class="ti ti-smart-home text-xl"></i>
                    @if($countSiswaKeluar > 0)
                        <span class="absolute -top-1 -right-2.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white shadow-xs" title="{{ $countSiswaKeluar }} siswa sedang di luar">
                            {{ $countSiswaKeluar > 99 ? '99+' : $countSiswaKeluar }}
                        </span>
                    @endif
                    @if($isDashboard)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-rose-600"></span>
                    @endif
                </div>
                <span class="text-[10px] leading-tight tracking-tight">Beranda</span>
            </a>

            {{-- 2. Laporan Temuan --}}
            <a href="{{ route('satpam.laporan.index') }}"
               class="flex flex-col items-center justify-center gap-0.5 py-1 rounded-xl transition-all active:scale-95 {{ $isLaporan ? 'text-rose-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <div class="relative flex items-center justify-center">
                    <i class="ti ti-shield-exclamation text-xl"></i>
                    @if($countLaporanMenunggu > 0)
                        <span class="absolute -top-1 -right-2 min-w-[14px] h-3.5 px-0.5 rounded-full bg-amber-500 text-white text-[8px] font-bold flex items-center justify-center border border-white">
                            {{ $countLaporanMenunggu }}
                        </span>
                    @endif
                    @if($isLaporan)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-rose-600"></span>
                    @endif
                </div>
                <span class="text-[10px] leading-tight tracking-tight">Laporan</span>
            </a>

            {{-- 3. Center Elevated FAB: Scan QR --}}
            <div class="relative flex flex-col items-center justify-center h-full">
                <a href="{{ route('satpam.scan') }}"
                   aria-label="Scan QR Code"
                   class="absolute -top-5 w-12 h-12 rounded-full bg-gradient-to-tr from-rose-600 to-rose-500 text-white text-xl flex items-center justify-center shadow-[0_10px_20px_-3px_rgba(225,29,72,0.45)] border-[3.5px] border-white active:scale-95 transition-all duration-200 {{ $isScan ? 'ring-4 ring-rose-500/25 scale-105 fab-active-glow' : '' }}">
                    <i class="ti ti-qrcode text-xl"></i>
                </a>
                <span class="text-[10px] leading-tight tracking-tight mt-7 {{ $isScan ? 'font-bold text-rose-600' : 'font-medium text-slate-500' }}">Scan</span>
            </div>

            {{-- 4. Manual --}}
            <a href="{{ route('satpam.scan') }}#manual"
               id="navManualBtn"
               class="flex flex-col items-center justify-center gap-0.5 py-1 rounded-xl transition-all active:scale-95 text-slate-400 hover:text-slate-600">
                <div class="relative flex items-center justify-center">
                    <i class="ti ti-search text-xl"></i>
                </div>
                <span class="text-[10px] leading-tight tracking-tight">Manual</span>
            </a>

            {{-- 5. Akun --}}
            <button type="button"
                    @click="sheet = true"
                    class="flex flex-col items-center justify-center gap-0.5 py-1 rounded-xl transition-all active:scale-95 {{ $isProfil ? 'text-rose-600 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <div class="relative flex items-center justify-center">
                    <div class="w-6 h-6 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold flex items-center justify-center shadow-2xs">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @if($isProfil)
                        <span class="absolute -bottom-1 w-1 h-1 rounded-full bg-rose-600"></span>
                    @endif
                </div>
                <span class="text-[10px] leading-tight tracking-tight">Akun</span>
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
                document.body.classList.add('keyboard-open');
            } else {
                document.body.classList.remove('keyboard-open');
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

