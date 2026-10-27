<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - DIDISPEN</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tabler Icons Webfont -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    
    <!-- Font Awesome retained for older CRUD views if needed -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="/manifest.json">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('components.sweetalert-theme')

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.011em;
        }
        * {
            -webkit-tap-highlight-color: transparent;
        }

        /* Slim sleek scrollbar for sidebar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            -webkit-overflow-scrolling: touch;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
        /* Preline UI & Uiverse.io Tactile Micro-Interactions */
        .uiverse-btn-tactile {
            position: relative;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        .uiverse-btn-tactile:hover {
            transform: translateY(-1.5px);
        }
        .uiverse-btn-tactile:active {
            transform: translateY(1px) scale(0.98);
        }
        .uiverse-card-lift {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
            will-change: transform, box-shadow;
        }
        .uiverse-card-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
        }
        .uiverse-glow-blue {
            box-shadow: 0 0 16px -2px rgba(37, 99, 235, 0.35);
        }
        .uiverse-glow-emerald {
            box-shadow: 0 0 16px -2px rgba(16, 185, 129, 0.35);
        }
        .uiverse-glow-amber {
            box-shadow: 0 0 16px -2px rgba(245, 158, 11, 0.35);
        }
        .uiverse-glow-rose {
            box-shadow: 0 0 16px -2px rgba(244, 63, 94, 0.35);
        }
    </style>

    <!-- Timepicker-UI Global Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker-ui@4.6.0/dist/css/main.css">
    <style>
        :root {
            --tp-ui-primary: #2563eb !important;
            --tp-ui-primary-hover: #1d4ed8 !important;
            --tp-ui-border-radius: 1rem !important;
            --tp-ui-font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }
        .timepicker-ui-modal {
            border-radius: 1.25rem !important;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25) !important;
        }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">

    {{-- =========================================================
        MOBILE SIDEBAR OVERLAY & DRAWER
    ========================================================== --}}
    <div x-show="sidebarOpen" 
         x-cloak
         class="fixed inset-0 z-50 lg:hidden" 
         role="dialog" 
         aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>

        {{-- Mobile Drawer Panel --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-in-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex flex-col w-72 max-w-[85vw] h-full bg-white shadow-2xl border-r border-slate-200">
            
            {{-- Mobile Drawer Header --}}
            <div class="p-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-500/20 overflow-hidden flex items-center justify-center shrink-0">
                        @if(file_exists(public_path('images/logo-didispen.png')))
                            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-1">
                        @else
                            <i class="ti ti-school text-xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-none">DIDISPEN</h1>
                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">ADMIN</span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium mt-1 truncate">Panel Administrator</p>
                    </div>
                </div>
                <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup menu">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>

            {{-- Mobile Navigation Links --}}
            <div class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1">
                @include('admin.layouts.partials.nav-links')
            </div>

            {{-- Mobile Drawer Footer --}}
            <div class="p-3 border-t border-slate-100 shrink-0 bg-slate-50/60">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">Administrator</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Logout">
                            <i class="ti ti-logout text-base"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
        DESKTOP STATIC SIDEBAR (Fixed Left, Independent Scroll)
    ========================================================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 bg-white border-r border-slate-200/90 flex-col z-30 shadow-2xs">
        {{-- Desktop Header --}}
        <div class="p-4 border-b border-slate-100 flex items-center space-x-3 shrink-0 bg-white">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-500/20 overflow-hidden flex items-center justify-center shrink-0">
                @if(file_exists(public_path('images/logo-didispen.png')))
                    <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-1">
                @else
                    <i class="ti ti-school text-xl"></i>
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight leading-none">DIDISPEN</h1>
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">ADMIN</span>
                </div>
                <p class="text-[10px] text-slate-400 font-medium mt-1 truncate">Panel Administrator</p>
            </div>
        </div>

        {{-- Desktop Navigation Links --}}
        <div class="flex-1 overflow-y-auto sidebar-scroll p-3 space-y-1">
            @include('admin.layouts.partials.nav-links')
        </div>

        {{-- Desktop Footer User Profile Link (No redundant logout) --}}
        <div class="p-3 border-t border-slate-100 shrink-0 bg-slate-50/60">
            <a href="{{ url('/profil') }}" 
               class="flex items-center gap-2.5 p-2 rounded-xl bg-white border border-slate-200/80 shadow-2xs hover:border-blue-300 hover:bg-blue-50/30 transition-all group"
               title="Buka Profil Saya">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-700 transition-colors">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium truncate">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Administrator' }}</p>
                </div>
                <i class="ti ti-chevron-right text-xs text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all"></i>
            </a>
        </div>
    </aside>

    {{-- =========================================================
        MAIN AREA (Header + Content)
    ========================================================== --}}
    <div class="flex flex-col min-h-screen lg:pl-64">

        {{-- STICKY TOPBAR --}}
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 py-3 flex items-center justify-between gap-4 shadow-2xs">
            
            {{-- Left: Mobile Toggle & Page Title --}}
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" 
                        @click="sidebarOpen = true" 
                        class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 transition-colors shrink-0" 
                        aria-label="Buka menu navigasi">
                    <i class="ti ti-menu-2 text-xl"></i>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium hidden sm:flex">
                        <span>Admin</span>
                        <i class="ti ti-chevron-right text-[10px]"></i>
                        <span class="text-slate-600 font-semibold truncate">@yield('page-title', 'Dashboard')</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight truncate">
                        @yield('page-title', 'Dashboard')
                    </h2>
                </div>
            </div>

            {{-- Right: System Status & Responsive Profile Menu --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                
                {{-- Live Indicator --}}
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Aktif
                </span>

                {{-- Date widget --}}
                <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200/70">
                    <i class="ti ti-calendar text-blue-600 text-sm"></i>
                    {{ now()->translatedFormat('d M Y') }}
                </span>

                @if(auth()->user()->isSuperAdmin())
                    <span class="hidden xl:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                        <i class="ti ti-shield-check text-blue-600 text-xs"></i> Super Admin
                    </span>
                @endif

                @if(auth()->user()->siswa)
                    <a href="{{ route('siswa.dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors"
                       title="Kembali ke Dashboard Siswa">
                        <i class="ti ti-school text-sm"></i>
                        <span class="hidden sm:inline">Portal Siswa</span>
                    </a>
                @endif

                <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                {{-- RESPONSIVE USER PROFILE DROPDOWN (1 Logout Button in Desktop/Mobile) --}}
                <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false" @keydown.escape.window="profileOpen = false">
                    
                    {{-- Trigger Button --}}
                    <button type="button" 
                            @click="profileOpen = !profileOpen"
                            class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-xl bg-slate-50 hover:bg-slate-100/90 border border-slate-200/80 text-slate-800 transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight min-w-0 max-w-[120px] lg:max-w-[160px]">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium truncate">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</p>
                        </div>
                        <i class="ti ti-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': profileOpen }"></i>
                    </button>

                    {{-- Dropdown Card --}}
                    <div x-show="profileOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150 transform"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100 transform"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-2 z-50 divide-y divide-slate-100">
                        
                        {{-- Dropdown Header Info --}}
                        <div class="p-2.5">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-400 font-medium truncate mt-0.5">{{ auth()->user()->email }}</p>
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ auth()->user()->isSuperAdmin() ? 'Super Administrator' : 'Administrator' }}
                                </span>
                            </div>
                        </div>

                        {{-- Navigation Links inside Dropdown --}}
                        <div class="py-1">
                            <a href="{{ url('/profil') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <i class="ti ti-user text-base text-slate-400"></i>
                                <span>Profil Saya</span>
                            </a>
                            <a href="{{ route('admin.settings.index') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <i class="ti ti-settings text-base text-slate-400"></i>
                                <span>Pengaturan Sistem</span>
                            </a>
                            <a href="{{ url('/panduan') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <i class="ti ti-book-2 text-base text-slate-400"></i>
                                <span>Panduan Penggunaan</span>
                            </a>
                        </div>

                        {{-- The Single Desktop/Mobile Logout Button --}}
                        <div class="pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                    <i class="ti ti-logout text-base"></i>
                                    <span>Logout dari Akun</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-7 min-w-0">
            @yield('content')
        </main>
    </div>

    <!-- Preline UI & Timepicker-UI Framework Integration -->
    <script src="https://cdn.jsdelivr.net/npm/preline@2.7.0/dist/preline.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/timepicker-ui@4.6.0/dist/index.umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TimepickerUI !== 'undefined') {
                document.querySelectorAll('input.admin-timepicker, input.timepicker-input').forEach(input => {
                    const picker = new TimepickerUI(input, {
                        clock: { type: '24h', incrementMinutes: 5 },
                        ui: { theme: 'basic', animation: true, backdrop: true },
                        labels: {
                            ok: 'Pilih',
                            cancel: 'Batal',
                            clear: 'Hapus',
                            clockTitle: input.dataset.clockTitle || 'Pilih Waktu',
                            hour: 'Jam',
                            minute: 'Menit'
                        }
                    });
                    picker.create();

                    if (input.id) {
                        const triggerBtn = document.querySelector(`[data-timepicker-trigger="${input.id}"]`);
                        if (triggerBtn) {
                            triggerBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                picker.open();
                            });
                        }
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
