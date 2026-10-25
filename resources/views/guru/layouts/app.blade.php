<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, interactive-widget=overlays-content, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN Guru">
    <title>@yield('title', 'Guru') - DIDISPEN</title>
    <!-- Google Fonts: Inter (Sama seperti siswa: crisp, smooth, dan nyaman dibaca) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tabler Icons Webfont (Modern, Stroke-based, Konsisten dengan Siswa) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="/manifest.json">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('components.sweetalert-theme')
    <style>
        [x-cloak] { display: none !important; }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.011em;
        }
        * {
            -webkit-tap-highlight-color: transparent;
        }
        /* Custom slim & smooth scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* FAB Active subtle breathing animation */
        @keyframes softPulse {
            0%, 100% {
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            }
            50% {
                box-shadow: 0 4px 22px rgba(37, 99, 235, 0.65);
            }
        }
        .fab-active-glow {
            animation: softPulse 2.8s ease-in-out infinite;
        }

        /* Smooth transitions for interactive controls */
        button, a, input, select, textarea {
            transition: color 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Sembunyikan bottom bar saat keyboard virtual HP terbuka agar tidak ikut keangkat */
        body.keyboard-open .mobile-bottom-nav,
        body.keyboard-open #mobileBottomNav,
        body.keyboard-open #btnLihatJadwal {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(120%) !important;
            transition: transform 0.2s ease, opacity 0.2s ease !important;
        }
    </style>

</head>
<body class="bg-slate-50 text-slate-800 antialiased overflow-x-hidden">
    @include('components.pwa-splash')

@php
    $user    = auth()->user();
    $pending = $stats['menunggu'] ?? $stats['pending'] ?? 0;
    $keluar  = isset($siswaKeluar) ? $siswaKeluar->count() : 0;
    $navOn   = 'flex items-center w-full px-3.5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 shadow-sm shadow-blue-500/20';
    $navOff  = 'flex items-center w-full px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors';
    $mobOn   = 'text-blue-600';
    $mobOff  = 'text-slate-400';
    $isPiketActiveUser = $user?->guru ? app(\App\Services\GuruPiketService::class)->isGuruBertugasSaatIni($user->guru) : false;
@endphp

<div class="min-h-screen overflow-x-hidden max-w-full" x-data="{ sheet: false }">

    {{-- ================================================== --}}
    {{-- SIDEBAR — DESKTOP SAJA                              --}}
    {{-- ================================================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex-col z-30">

        <div class="flex items-center space-x-3 px-5 py-5 border-b border-slate-100">
            <div class="relative">
               <div class="relative flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 shadow-xs overflow-hidden flex-shrink-0">
                    @if(file_exists(public_path('images/logo-didispen.png')))
                        <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                    @else
                        <i class="ti ti-school text-blue-600 text-2xl"></i>
                    @endif
                </div>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-900 tracking-tight leading-tight">DIDISPEN</h1>
                <p class="text-[11px] text-slate-500 font-medium">Panel Guru Piket</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
            <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? $navOn : $navOff }}">
                <i class="ti ti-smart-home text-lg w-5 mr-3"></i> Dashboard
            </a>
            <a href="{{ route('guru.checklog.index') }}" class="{{ request()->routeIs('guru.checklog.*') ? $navOn : $navOff }}">
                <i class="ti ti-door-enter text-lg w-5 mr-3"></i> Keluar/Masuk
            </a>

            <p class="px-3.5 mb-2 mt-5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Dispensasi</p>
            <div class="space-y-1">
                <a href="{{ route('guru.pengajuan.index') }}" class="{{ request()->routeIs('guru.pengajuan.index') ? $navOn : $navOff }}">
                    <i class="ti ti-clipboard-check text-lg w-5 mr-3"></i> Verifikasi
                    @if($pending > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
                    @endif
                </a>

                <a href="{{ route('guru.scan') }}" class="{{ request()->routeIs('guru.scan') ? $navOn : $navOff }}">
                    <i class="ti ti-qrcode text-lg w-5 mr-3"></i> Scan QR
                </a>

                <a href="{{ route('guru.pengajuan.create') }}" class="{{ request()->routeIs('guru.pengajuan.create') ? $navOn : $navOff }}">
                    <i class="ti ti-circle-plus text-lg w-5 mr-3"></i>
                    <span class="flex-1">Buat Dispensasi</span>
                    @if(!$isPiketActiveUser)
                        <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 flex items-center gap-1 font-semibold" title="Khusus Guru Piket Bertugas">
                            <i class="ti ti-lock text-[10px]"></i>Piket
                        </span>
                    @endif
                </a>
                <a href="{{ route('guru.laporan.index') }}" class="{{ request()->routeIs('guru.laporan.*') ? $navOn : $navOff }}">
                    <i class="ti ti-chart-bar text-lg w-5 mr-3"></i> Laporan
                </a>
                @php
                    $pendingLaporanSatpamGuru = \App\Models\LaporanSatpam::where('status', 'menunggu')->count();
                @endphp
                <a href="{{ route('guru.laporan-satpam.index') }}" class="{{ request()->routeIs('guru.laporan-satpam.*') ? $navOn : $navOff }}">
                    <i class="ti ti-shield-exclamation text-lg w-5 mr-3"></i>
                    <span class="flex-1">Laporan Satpam</span>
                    @if($pendingLaporanSatpamGuru > 0)
                        <span class="ml-auto min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full bg-amber-500 text-white text-[10px] font-bold leading-none">
                            {{ $pendingLaporanSatpamGuru > 99 ? '99+' : $pendingLaporanSatpamGuru }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('panduan') }}" class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                    <i class="ti ti-book-2 text-lg w-5 mr-3"></i> Panduan
                </a>
                @php
                    $pendingSwapCount = \App\Models\PertukaranJadwalPiket::query()
                        ->where('guru_pengganti_id', auth()->user()->guru?->id)
                        ->where('status', 'menunggu')
                        ->count();
                @endphp
                <a href="{{ route('guru.piket.swap.incoming') }}"
                   class="{{ request()->routeIs('guru.piket.swap.*') ? $navOn : $navOff }}">
                    <i class="ti ti-arrows-exchange text-lg w-5 mr-3"></i>
                    <span>Ajukan Tukar Jadwal</span>

                    @if($pendingSwapCount > 0)
                        <span class="ml-auto min-w-[20px] h-5 px-1.5 flex items-center justify-center
                                     rounded-full bg-rose-500 text-white text-[10px] font-bold leading-none">
                            {{ $pendingSwapCount > 99 ? '99+' : $pendingSwapCount }}
                        </span>
                    @endif
                </a>
                {{-- PROFIL --}}
                <a href="{{ route('profil.show') }}"
                   class="{{ request()->routeIs('profil.*') ? $navOn : $navOff }}">
                    <i class="ti ti-user text-lg w-5 mr-3"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-600 border border-rose-200 hover:bg-rose-50 transition-colors">
                    <i class="ti ti-logout text-lg mr-2"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ================================================== --}}
    {{-- MAIN AREA                                          --}}
    {{-- ================================================== --}}
    <div class="flex flex-col min-h-screen lg:pl-72 min-w-0 max-w-full overflow-x-hidden">

        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200">
            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <div class="relative">
                        <div class="relative flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 shadow-2xs overflow-hidden flex-shrink-0">
                            @if(file_exists(public_path('images/logo-didispen.png')))
                                <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                            @else
                                <i class="ti ti-school text-blue-600 text-base"></i>
                            @endif
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-none">DIDISPEN</h1>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">@yield('page-title', 'Dashboard')</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-[11px] font-semibold flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Online
                </span>
            </div>
            {{-- Desktop Header --}}
            <div class="hidden lg:flex items-center justify-between px-6 py-3">
                <h2 class="text-base font-bold text-slate-900">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-4">
                    <span class="flex items-center text-xs font-medium text-slate-500">
                        <i class="ti ti-calendar mr-1.5 text-blue-600 text-sm"></i> {{ now()->format('d M Y') }}
                    </span>
                    <div class="h-5 w-px bg-slate-200"></div>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-[11px] text-blue-600 font-medium">Guru Piket</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-3 sm:p-6 pb-28 lg:pb-6 min-w-0 max-w-full">
            @include('components.alert')
            @yield('content')
        </main>

    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAV — MOBILE (Smooth, Non-Slop, seperti Siswa) --}}
    {{-- ================================================== --}}
    @php
        $isPengajuanIndexActive = request()->routeIs('guru.pengajuan.index') || request()->routeIs('guru.pengajuan.show');
    @endphp
    <nav id="mobileBottomNav"
         class="mobile-bottom-nav lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] transform-gpu transition-all duration-200"
         style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
        <div class="grid grid-cols-5 h-16 w-full max-w-md mx-auto items-center">
            {{-- 1. Beranda --}}
            <a href="{{ route('guru.dashboard') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('guru.dashboard') ? $mobOn : $mobOff }}">
                <i class="ti ti-smart-home text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Beranda</span>
            </a>

            {{-- 2. Keluar/Masuk --}}
            <a href="{{ route('guru.checklog.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('guru.checklog.*') ? $mobOn : $mobOff }}">
                <i class="ti ti-door-enter text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Keluar/Masuk</span>
            </a>

            {{-- 3. FAB: Verifikasi (Elevated Center Button) --}}
            <div class="relative flex flex-col items-center justify-end h-full pb-1.5">
                <a href="{{ route('guru.pengajuan.index') }}"
                   aria-label="Verifikasi Dispensasi"
                   class="absolute -top-5 w-12 h-12 rounded-full text-white text-xl flex items-center justify-center border-4 border-slate-50 transition-all duration-200 active:scale-95 {{ $isPengajuanIndexActive ? 'bg-blue-600 shadow-lg shadow-blue-500/35 ring-4 ring-blue-500/25 scale-105 fab-active-glow' : 'bg-blue-600 shadow-md hover:bg-blue-700' }}">
                    <i class="ti ti-clipboard-check"></i>
                    @if($pending > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white">
                            {{ $pending > 99 ? '99+' : $pending }}
                        </span>
                    @endif
                </a>
                <span class="text-[10px] leading-tight tracking-tight mt-auto {{ $isPengajuanIndexActive ? 'font-bold text-blue-600' : 'font-semibold text-slate-700' }}">Verifikasi</span>
            </div>

            {{-- 4. Scan QR --}}
            <a href="{{ route('guru.scan') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('guru.scan') ? $mobOn : $mobOff }}">
                <i class="ti ti-qrcode text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Scan QR</span>
            </a>

            {{-- 5. Akun --}}
            <button type="button" onclick="document.getElementById('accountSheet').classList.remove('translate-y-full')"
                    class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('profil.*') ? $mobOn : $mobOff }}">
                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 text-[10px] font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Akun</span>
            </button>
        </div>
    </nav>

{{-- BOTTOM SHEET AKUN --}}
<div id="accountSheet"
     class="lg:hidden fixed inset-0 z-[60] flex items-end translate-y-full transition-transform duration-300">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xs"
         onclick="this.parentElement.classList.add('translate-y-full')"></div>

    {{-- Sheet --}}
    <div class="relative bg-white rounded-t-2xl w-full max-h-[85vh] overflow-y-auto shadow-2xl">
        <div class="p-5 sm:p-6">

            {{-- Drag Handle Indicator --}}
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto mb-4"></div>

            {{-- Informasi Akun --}}
            <div class="flex items-center gap-3.5 mb-5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-base font-bold text-blue-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-slate-900 break-words">
                        {{ $user->name }}
                    </h3>
                    <p class="text-xs text-blue-600 font-medium">
                        Guru Piket
                    </p>
                    <p class="text-[11px] text-slate-400 break-all">
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            @php
                $guruId = auth()->user()->guru?->id;

                $pendingSwapCount = \App\Models\PertukaranJadwalPiket::query()
                    ->where('guru_pengganti_id', $guruId)
                    ->where('status', 'menunggu')
                    ->count();
            @endphp

            <div class="space-y-1">

                {{-- Verifikasi Dispensasi --}}
                <a href="{{ route('guru.pengajuan.index') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-clipboard-check text-lg w-5 mr-3 text-slate-400"></i>
                    <span>Verifikasi Dispensasi</span>
                    @if($pending > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
                    @endif
                </a>

                {{-- Buat Dispensasi --}}
                <a href="{{ route('guru.pengajuan.create') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-circle-plus text-lg w-5 mr-3 text-slate-400"></i>
                    <span class="flex-1">Buat Dispensasi Manual</span>
                    @if(!$isPiketActiveUser)
                        <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 flex items-center gap-1 font-semibold" title="Khusus Guru Piket Bertugas">
                            <i class="ti ti-lock text-[10px]"></i>Piket
                        </span>
                    @endif
                </a>

                {{-- Laporan --}}
                <a href="{{ route('guru.laporan.index') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-chart-bar text-lg w-5 mr-3 text-slate-400"></i>
                    <span>Laporan</span>
                </a>

                {{-- Laporan Satpam --}}
                <a href="{{ route('guru.laporan-satpam.index') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-shield-exclamation text-lg w-5 mr-3 text-slate-400"></i>
                    <span class="flex-1">Laporan Satpam</span>
                    @if($pendingLaporanSatpamGuru > 0)
                        <span class="min-w-[18px] h-4 px-1 rounded-full bg-amber-500 text-white text-[9px] font-bold flex items-center justify-center">
                            {{ $pendingLaporanSatpamGuru }}
                        </span>
                    @endif
                </a>

                {{-- Panduan --}}
                <a href="{{ route('panduan') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-book-2 text-lg w-5 mr-3 text-slate-400"></i>
                    <span>Panduan Penggunaan</span>
                </a>

                {{-- Tukar Jadwal --}}
                <a href="{{ route('guru.piket.swap.incoming') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-arrows-exchange text-lg w-5 mr-3 text-slate-400"></i>
                    <span>Ajukan Tukar Jadwal</span>

                    @if($pendingSwapCount > 0)
                        <span class="ml-auto min-w-[20px] h-5 px-1.5 flex items-center justify-center
                                     rounded-full bg-rose-500 text-white text-[10px] font-bold leading-none">
                            {{ $pendingSwapCount > 99 ? '99+' : $pendingSwapCount }}
                        </span>
                    @endif
                </a>

                {{-- Profil --}}
                <a href="{{ route('profil.show') }}"
                   class="flex items-center min-h-[44px] px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ti ti-user text-lg w-5 mr-3 text-slate-400"></i>
                    <span>Profil Saya</span>
                </a>

                {{-- Logout --}}
                <form method="POST"
                      action="{{ route('logout') }}"
                      class="mt-3 pt-3 border-t border-slate-100">
                    @csrf
                    <button type="submit"
                            class="flex items-center min-h-[44px] w-full px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                        <i class="ti ti-logout text-lg w-5 mr-3"></i>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>

                {{-- Tutup --}}
                <button type="button"
                        onclick="document.getElementById('accountSheet').classList.add('translate-y-full')"
                        class="w-full mt-2 min-h-[42px] px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
(function() {
    function handleViewportResize() {
        if (!window.visualViewport) return;
        const isKeyboard = (window.innerHeight - window.visualViewport.height) > 150;
        if (isKeyboard) {
            document.body.classList.add('keyboard-open');
        } else {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (!['input', 'textarea', 'select'].includes(activeTag)) {
                document.body.classList.remove('keyboard-open');
            }
        }
    }

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', handleViewportResize);
        window.visualViewport.addEventListener('scroll', handleViewportResize);
    }

    document.addEventListener('focusin', function(e) {
        const tag = e.target.tagName ? e.target.tagName.toLowerCase() : '';
        if (['input', 'textarea', 'select'].includes(tag) && window.innerWidth < 1024) {
            document.body.classList.add('keyboard-open');
        }
    });

    document.addEventListener('focusout', function(e) {
        setTimeout(function() {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (!['input', 'textarea', 'select'].includes(activeTag) && window.innerWidth < 1024) {
                if (!window.visualViewport || (window.innerHeight - window.visualViewport.height) <= 150) {
                    document.body.classList.remove('keyboard-open');
                }
            }
        }, 150);
    });
})();
</script>

@stack('scripts')

</body>
</html>
