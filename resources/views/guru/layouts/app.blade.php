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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="/manifest.json">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('components.sweetalert-theme')
    <style>[x-cloak] { display: none !important; }</style>

</head>
<body class="bg-gray-100">

@php
    $user    = auth()->user();
    $pending = $stats['menunggu'] ?? $stats['pending'] ?? 0;
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
                    @if(file_exists(public_path('images/logo-didispen.png')))
                        <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
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
                <a href="{{ route('guru.pengajuan.index') }}" class="{{ request()->routeIs('guru.pengajuan.index') ? $navOn : $navOff }}">
                    <i class="fas fa-file-signature w-5 mr-3 text-center"></i> Verifikasi
                    @if($pending > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
                    @endif
                </a>

                <a href="{{ route('guru.scan') }}" class="{{ request()->routeIs('guru.scan') ? $navOn : $navOff }}">
                    <i class="fas fa-qrcode w-5 mr-3 text-center"></i> Scan QR
                </a>

                {{-- <i class="fas fa-check-circle"></i> DIPERBAIKI: Tambahkan class dinamis agar berwarna saat aktif --}}
                <a href="{{ route('guru.pengajuan.create') }}" class="{{ request()->routeIs('guru.pengajuan.create') ? $navOn : $navOff }}">
                    <i class="fas fa-plus-circle w-5 mr-3 text-center"></i> Buat Dispensasi
                </a>
                <a href="{{ route('guru.laporan.index') }}" class="{{ request()->routeIs('guru.laporan.*') ? $navOn : $navOff }}">
                    <i class="fas fa-chart-bar w-5 mr-3 text-center"></i> Laporan
                </a>
                <a href="{{ route('panduan') }}" class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                    <i class="fas fa-book-open w-5 mr-3 text-center"></i> Panduan
                    
                </a>
                @php
    $pendingSwapCount = \App\Models\PertukaranJadwalPiket::query()
        ->where('guru_pengganti_id', auth()->user()->guru?->id)
        ->where('status', 'menunggu')
        ->count();
@endphp
                <a href="{{ route('guru.piket.swap.incoming') }}"
   class="{{ request()->routeIs('guru.piket.swap.*') ? $navOn : $navOff }}">
    <i class="fas fa-exchange-alt w-5 mr-3 text-center"></i>
    <span>Ajukan Tukar Jadwal</span>

    @if($pendingSwapCount > 0)
        <span class="ml-auto min-w-[22px] h-5 px-1.5 flex items-center justify-center
                     rounded-full bg-red-500 text-white text-[11px] font-bold leading-none">
            {{ $pendingSwapCount > 99 ? '99+' : $pendingSwapCount }}
        </span>
    @endif
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
                            @if(file_exists(public_path('images/logo-didispen.png')))
                                <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                            @else
                                <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                            @endif
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-black text-gray-900 tracking-tight leading-none">DIDISPEN</h1>
                        <p class="text-[11px] text-gray-500 font-medium mt-0.5">@yield('page-title', 'Dashboard')</p>
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
            @include('components.alert')
            @yield('content')
        </main>

    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAV — MOBILE (GRID SYSTEM like Satpam)      --}}
    {{-- ================================================== --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-5 h-16 w-full">
            {{-- 1. Beranda --}}
            <a href="{{ route('guru.dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('guru.dashboard') ? $mobOn : $mobOff }}">
                <i class="fas fa-home text-lg"></i>
                <span class="text-[9px] font-semibold leading-tight">Beranda</span>
            </a>

            {{-- 2. Keluar/Masuk --}}
            <a href="{{ route('guru.checklog.index') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('guru.checklog.*') ? $mobOn : $mobOff }}">
                <i class="fas fa-door-open text-lg"></i>
                <span class="text-[9px] font-semibold leading-tight">Keluar/Masuk</span>
            </a>

            {{-- 3. FAB: Verifikasi (tombol utama dengan badge) --}}
            <div class="relative flex flex-col items-center justify-end pb-1">
                <a href="{{ route('guru.pengajuan.index') }}"
                   class="absolute -top-5 w-12 h-12 rounded-full bg-blue-600 text-white text-lg flex items-center justify-center shadow-md border-4 border-gray-50 active:scale-95 transition-transform relative">
                    <i class="fas fa-check"></i>
                    @if($pending > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white">{{ $pending }}</span>
                    @endif
                </a>
                <span class="text-[9px] font-semibold leading-tight text-gray-700">Verifikasi</span>
            </div>

            {{-- 4. Scan QR --}}
            <a href="{{ route('guru.scan') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('guru.scan') ? $mobOn : $mobOff }}">
                <i class="fas fa-qrcode text-lg"></i>
                <span class="text-[9px] font-semibold leading-tight">Scan QR</span>
            </a>

            {{-- 5. Akun --}}
            <button onclick="document.getElementById('accountSheet').classList.remove('translate-y-full')"
                    class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ request()->routeIs('profil.*') ? $mobOn : $mobOff }}">
                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-[9px] font-bold text-gray-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <span class="text-[9px] font-semibold leading-tight">Akun</span>
            </button>
        </div>
    </nav>

   
{{-- BOTTOM SHEET AKUN --}}
<div id="accountSheet"
     class="lg:hidden fixed inset-0 z-[60] flex items-end translate-y-full transition-transform duration-300">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/50"
         onclick="this.parentElement.classList.add('translate-y-full')"></div>

    {{-- Sheet --}}
    <div class="relative bg-white rounded-t-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="p-6">

            {{-- Informasi Akun --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-xl font-bold text-blue-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        {{ $user->name }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        Guru Piket
                    </p>

                    <p class="text-xs text-gray-400">
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

            <div class="space-y-2">

                {{-- Verifikasi Dispensasi --}}
                <a href="{{ route('guru.pengajuan.index') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-file-alt w-5 mr-3 text-gray-400"></i>
                    <span>Verifikasi Dispensasi</span>
                </a>

                {{-- Laporan --}}
                <a href="{{ route('guru.laporan.index') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chart-bar w-5 mr-3 text-gray-400"></i>
                    <span>Laporan</span>
                </a>

                {{-- Scan QR --}}
                {{--
                <a href="{{ route('guru.scan') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-qrcode w-5 mr-3 text-gray-400"></i>
                    <span>Scan QR</span>
                </a>
                --}}

                {{-- Panduan --}}
                <a href="{{ route('panduan') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-book w-5 mr-3 text-gray-400"></i>
                    <span>Panduan</span>
                </a>

                {{-- Tukar Jadwal --}}
                <a href="{{ route('guru.piket.swap.incoming') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">

                    <i class="fas fa-exchange-alt w-5 mr-3 text-gray-400"></i>

                    <span>Ajukan Tukar Jadwal</span>

                    @if($pendingSwapCount > 0)
                        <span class="ml-auto min-w-[22px] h-5 px-1.5 flex items-center justify-center
                                     rounded-full bg-red-500 text-white text-[11px] font-bold leading-none">
                            {{ $pendingSwapCount > 99 ? '99+' : $pendingSwapCount }}
                        </span>
                    @endif
                </a>

                {{-- Profil --}}
                <a href="{{ route('profil.show') }}"
                   class="flex items-center min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-user w-5 mr-3 text-gray-400"></i>
                    <span>Profil Saya</span>
                </a>

                {{-- Logout --}}
                <form method="POST"
                      action="{{ route('logout') }}"
                      class="mt-4 pt-4 border-t border-gray-200">
                    @csrf

                    <button type="submit"
                            class="flex items-center min-h-[44px] w-full px-4 py-3 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>

                {{-- Tutup --}}
                <button type="button"
                        onclick="document.getElementById('accountSheet').classList.add('translate-y-full')"
                        class="w-full mt-2 min-h-[44px] px-4 py-3 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                    Tutup
                </button>

            </div>
        </div>
    </div>
</div>
@stack('scripts')



</body>
</html>
