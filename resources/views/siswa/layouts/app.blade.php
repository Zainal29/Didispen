<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN Siswa">
    <title>@yield('title', 'Siswa') - DIDISPEN</title>
    <!-- Google Fonts: Inter (Ultra-clean, crisp, and comfortable UI font) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tabler Icons Webfont (Modern, Clean, Stroke-based) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <!-- ✅ TAMBAHKAN INI DI SINI (di dalam <head>) -->
          <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
          <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
          <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
          <link rel="manifest" href="/manifest.json">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('components.sweetalert-theme')
    <style>
        [x-cloak] {
            display: none !important;
        }
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

        /* Smooth page entrance animation */
        @keyframes pageFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }
        .page-enter {
            animation: pageFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

        /* Sembunyikan bottom bar saat keyboard virtual HP terbuka agar tidak ikut terangkat ke atas input */
        body.keyboard-open .mobile-bottom-nav,
        body.keyboard-open #siswaBottomNav {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(120%) !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease !important;
        }

        /* Sembunyikan bottom bar saat modal atau sheet terbuka agar tidak menutupi modal */
        body.modal-open .mobile-bottom-nav,
        body.modal-open #siswaBottomNav,
        body.modal-jadwal-open .mobile-bottom-nav,
        body.modal-jadwal-open #siswaBottomNav {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(120%) !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease !important;
        }
    </style>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased">

    @php
        $user   = auth()->user();
        $siswa  = $user->siswa;
        $notif  = $user->unreadNotifikasi()->count();

        // Style sidebar desktop - ANTI SLOP: solid color, no gradient
        $navOn  = 'flex items-center w-full px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600';
        $navOff = 'flex items-center w-full px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors';

        // Warna ikon bottom nav
        $mobOn  = 'text-blue-600';
        $mobOff = 'text-gray-400';
    @endphp
    
<div class="min-h-screen">

        {{-- ================================================== --}}
        {{-- SIDEBAR — HANYA MUNCUL DI DESKTOP (lg ke atas)      --}}
        {{-- ================================================== --}}
        <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 bg-white border-r border-slate-200 flex-col z-30">

            {{-- Brand --}}
            <div class="p-5 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        @if(file_exists(public_path('images/logo-didispen.png')))
                            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo" class="w-full h-full object-contain">
                        @else
                            <i class="ti ti-school text-blue-600 text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 tracking-tight">DIDISPEN</h1>
                        <p class="text-xs text-slate-500">Panel Siswa</p>
                    </div>
                </div>
            </div>

            {{-- Nav Desktop --}}
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                @if(auth()->user()->isSuperAdmin())
                    <div class="mb-3 p-3 rounded-xl bg-slate-900 text-white shadow-xs border border-slate-800">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-wide uppercase text-slate-300">
                                <i class="ti ti-shield-check text-blue-400"></i> Super Admin
                            </span>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-1.5 w-full py-1.5 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition-colors">
                            <i class="ti ti-layout-dashboard text-xs"></i> Buka Panel Admin
                        </a>
                    </div>
                @endif

                <a href="{{ route('siswa.dashboard') }}"
                   class="{{ request()->routeIs('siswa.dashboard') ? $navOn : $navOff }}">
                    <i class="ti ti-smart-home text-lg w-5 mr-3"></i> Dashboard
                </a>

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-6 mb-2 px-4">Dispensasi</p>
                <a href="{{ route('siswa.pengajuan.index') }}"
                   class="{{ request()->routeIs('siswa.pengajuan.index') ? $navOn : $navOff }}">
                    <i class="ti ti-file-text text-lg w-5 mr-3"></i> Pengajuan
                </a>
                <a href="{{ route('siswa.pengajuan.create') }}"
                   class="{{ request()->routeIs('siswa.pengajuan.create') ? $navOn : $navOff }}">
                    <i class="ti ti-circle-plus text-lg w-5 mr-3"></i> Buat Pengajuan
                </a>

                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-6 mb-2 px-4">Lainnya</p>
                <a href="{{ route('siswa.notifikasi.index') }}"
                   class="{{ request()->routeIs('siswa.notifikasi.*') ? $navOn : $navOff }}">
                    <i class="ti ti-bell text-lg w-5 mr-3"></i> Notifikasi
                    @if($notif > 0)
                        <span class="ml-auto bg-rose-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $notif }}</span>
                    @endif
                </a>
                <a href="{{ route('panduan') }}"
                   class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                    <i class="ti ti-book-2 text-lg w-5 mr-3"></i> Panduan
                </a>
                <a href="{{ route('profil.show') }}"
                   class="{{ request()->routeIs('profil.*') ? $navOn : $navOff }}">
                    <i class="ti ti-user text-lg w-5 mr-3"></i> Profil Saya
                </a>
            </nav>

            {{-- Logout Desktop --}}
            <div class="p-4 border-t border-slate-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2.5 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                        <i class="ti ti-logout text-lg w-5 mr-3"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- ================================================== --}}
        {{-- MAIN AREA                                           --}}
        {{-- ================================================== --}}
        <div class="flex flex-col min-h-screen lg:pl-64">

            {{-- TOPBAR --}}
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200 shadow-2xs px-4 sm:px-6 py-3">

                {{-- Topbar MOBILE: ringkas, muat di layar kecil --}}
                <div class="lg:hidden flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center">
                            @if(file_exists(public_path('images/logo-didispen.png')))
                                <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo" class="w-full h-full object-contain">
                            @else
                                <i class="ti ti-school text-blue-600 text-base"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-slate-900 leading-tight">DIDISPEN</h1>
                            <p class="text-[11px] text-slate-500">@yield('page-title', 'Dashboard')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 text-[11px] font-bold border border-slate-200" title="Buka Panel Admin">
                                <i class="ti ti-shield-check text-blue-600"></i> Admin
                            </a>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold border border-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Online
                        </span>
                    </div>
                </div>

                {{-- Topbar DESKTOP --}}
                <div class="hidden lg:flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center gap-4">
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200 hover:bg-slate-200 transition-colors">
                                <i class="ti ti-shield-check text-blue-600"></i>
                                <span>Panel Admin</span>
                            </a>
                        @endif
                        <a href="{{ route('siswa.pengajuan.create') }}"
                           class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                            <i class="ti ti-plus mr-1.5"></i> Buat Pengajuan
                        </a>
                        <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-blue-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">
                                    @if(auth()->user()->isSuperAdmin())
                                        <span class="text-blue-600 font-bold">Siswa &bull; Super Admin</span>
                                    @else
                                        Siswa
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- KONTEN (pb-32 sm:pb-36 lg:pb-6 agar tidak tertutup bottom nav di HP) --}}
            <main class="flex-1 p-4 sm:p-6 pb-32 sm:pb-36 lg:pb-6 page-enter">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAVIGATION BAR — KHUSUS MOBILE (OPTIMAL UNTUK HP) --}}
    {{-- ================================================== --}}
    @php
        $isCreateActive = request()->routeIs('siswa.pengajuan.create');
        $isRiwayatActive = (request()->routeIs('siswa.pengajuan.index') || request()->routeIs('siswa.pengajuan.show')) && !$isCreateActive;
    @endphp
    <nav id="siswaBottomNav"
         class="mobile-bottom-nav lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] transform-gpu transition-all duration-200"
         style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);">
        <div class="grid grid-cols-5 h-16 w-full max-w-md mx-auto items-center">
            
            {{-- 1. Beranda --}}
            <a href="{{ route('siswa.dashboard') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('siswa.dashboard') ? $mobOn : $mobOff }}">
                <i class="ti ti-smart-home text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Beranda</span>
            </a>

            {{-- 2. Riwayat --}}
            <a href="{{ route('siswa.pengajuan.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ $isRiwayatActive ? $mobOn : $mobOff }}">
                <i class="ti ti-history text-xl"></i>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Riwayat</span>
            </a>

            {{-- 3. FAB: Buat Pengajuan (Elevated Center Button) --}}
            <div class="relative flex flex-col items-center justify-end h-full pb-1.5">
                <a href="{{ route('siswa.pengajuan.create') }}"
                   aria-label="Buat Pengajuan Dispensasi"
                   class="absolute -top-5 w-12 h-12 rounded-full text-white text-xl flex items-center justify-center border-4 border-slate-50 transition-all duration-200 active:scale-95 {{ $isCreateActive ? 'bg-blue-600 shadow-lg shadow-blue-500/35 ring-4 ring-blue-500/25 scale-105 fab-active-glow' : 'bg-blue-600 shadow-md hover:bg-blue-700' }}">
                    <i class="ti ti-plus"></i>
                </a>
                <span class="text-[10px] leading-tight tracking-tight mt-auto {{ $isCreateActive ? 'font-bold text-blue-600' : 'font-semibold text-slate-700' }}">Buat</span>
            </div>

            {{-- 4. Notifikasi (+badge) --}}
            <a href="{{ route('siswa.notifikasi.index') }}"
               class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('siswa.notifikasi.*') ? $mobOn : $mobOff }}">
                <div class="relative">
                    <i class="ti ti-bell text-xl"></i>
                    @if($notif > 0)
                        <span class="absolute -top-1 -right-2 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center border-2 border-white">
                            {{ $notif > 99 ? '99+' : $notif }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Notifikasi</span>
            </a>

            {{-- 5. Akun --}}
            <button type="button"
                    onclick="document.getElementById('accountSheet').classList.remove('translate-y-full')"
                    class="flex flex-col items-center justify-center gap-1 transition-colors active:scale-90 {{ request()->routeIs('profil.*') ? $mobOn : $mobOff }}">
                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center">
                    <span class="text-[10px] font-bold text-slate-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <span class="text-[10px] font-semibold leading-tight tracking-tight">Akun</span>
            </button>
        </div>
    </nav>

    {{-- ================================================== --}}
    {{-- BOTTOM SHEET AKUN (muncul saat tombol Akun ditekan) --}}
    {{-- ================================================== --}}
    <div id="accountSheet" class="lg:hidden fixed inset-0 z-[60] flex items-end translate-y-full transition-transform duration-300">
        <div class="absolute inset-0 bg-slate-900/60" onclick="this.parentElement.classList.add('translate-y-full')"></div>
        <div class="relative bg-white rounded-t-3xl w-full max-h-[85vh] overflow-y-auto shadow-2xl">
            <div class="p-6">
                {{-- Handle drag indicator --}}
                <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-5"></div>

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-xl font-bold text-blue-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-bold text-slate-900 truncate">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $siswa?->kelas?->nama_kelas ?? '-' }} • NIS {{ $user->nis_nip }}</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <a href="{{ route('panduan') }}" class="flex items-center min-h-[44px] px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="ti ti-book-2 w-5 mr-3 text-slate-400 text-lg"></i> Panduan Penggunaan
                    </a>
                    <a href="{{ route('profil.show') }}" class="flex items-center min-h-[44px] px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="ti ti-user w-5 mr-3 text-slate-400 text-lg"></i> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4 pt-4 border-t border-slate-200">
                        @csrf
                        <button type="submit" class="flex items-center min-h-[44px] w-full px-4 py-3 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                            <i class="ti ti-logout w-5 mr-3 text-rose-500 text-lg"></i> Keluar dari Akun
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('accountSheet').classList.add('translate-y-full')"
                            class="w-full mt-2 min-h-[44px] px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Tutup
                    </button>
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

    @include('components.siswa-anti-keluar')

    @stack('modals')
    @stack('scripts')

</body>
</html>
