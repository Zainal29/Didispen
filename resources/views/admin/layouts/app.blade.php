<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-didispen.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-didispen.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-didispen.png') }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .sidebar-link.active { background-color: #1e40af; color: white; }
        .sidebar-link:hover:not(.active) { background-color: #e0e7ff; }

        /* ===== RESPONSIVE SIDEBAR ===== */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 1023px) {
            #sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 50;
                transform: translateX(-100%);
                height: 100vh;
            }
            #sidebar.sidebar-open {
                transform: translateX(0);
            }
        }
        @media (min-width: 1024px) {
            #sidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                flex-shrink: 0;
            }
        }
        #sidebar-overlay {
            display: none;
        }
        #sidebar-overlay.overlay-open {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">

        {{-- OVERLAY (mobile only) --}}
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

        {{-- SIDEBAR --}}
        <aside id="sidebar" class="w-64 bg-white shadow-lg flex flex-col overflow-y-auto">
            <div class="p-5 border-b flex items-center justify-between">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-14 h-14 rounded-xl bg-[#fbfcf6] shadow-md overflow-hidden flex items-center justify-center flex-shrink-0">
                        @if(file_exists(public_path('images/logo-didispen.png')))
                            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo DIDISPEN" class="w-full h-full object-contain p-0.5">
                        @else
                            <i class="fas fa-school text-blue-800 text-xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl font-bold text-blue-800 truncate">DIDISPEN</h1>
                        <p class="text-xs text-gray-500 mt-1 truncate">Panel Administrator</p>
                    </div>
                </div>
                {{-- Tombol close sidebar (mobile only) --}}
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 p-1 flex-shrink-0">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <nav class="flex-1 p-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                </a>

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Master Data</p>
                <a href="{{ route('admin.siswa.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate w-5 mr-3"></i> Siswa
                </a>
                <a href="{{ route('admin.satpam.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.satpam.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield w-5 mr-3"></i> Satpam
                </a>
                <a href="{{ route('admin.guru.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ (request()->routeIs('admin.guru.index') || request()->routeIs('admin.guru.create') || request()->routeIs('admin.guru.edit')) ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher w-5 mr-3"></i> Guru
                </a>
                <a href="{{ route('admin.guru.checklog') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.guru.checklog') ? 'active' : '' }}">
                    <i class="fas fa-id-card-alt w-5 mr-3"></i> Izin Guru
                </a>
                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Operasional</p>
                <a href="{{ route('admin.piket.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.piket.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt w-5 mr-3"></i> Jadwal Piket
                </a>
                <a href="{{ route('admin.semua.pengajuan') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.semua.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt w-5 mr-3"></i> Semua Pengajuan
                </a>
                <a href="{{ route('admin.laporan.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar w-5 mr-3"></i> Laporan
                </a>
                <a href="{{ route('admin.whatsapp-templates.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.whatsapp-templates.*') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp w-5 mr-3"></i> Template WA
                </a>

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Sistem</p>
                <a href="{{ route('admin.settings.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5 mr-3"></i> Pengaturan
                </a>
                <a href="{{ route('admin.audit.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-history w-5 mr-3"></i> Audit Log
                </a>
                <a href="{{ url('/profil') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->is('profil*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle w-5 mr-3"></i> Profil
                </a>
                <a href="{{ url('/panduan') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->is('panduan*') ? 'active' : '' }}">
                    <i class="fas fa-book-open w-5 mr-3"></i> Panduan
                </a>
                <a href="{{ route('admin.tutorial-videos.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.tutorial-videos.*') ? 'active' : '' }}">
                    <i class="fas fa-video w-5 mr-3"></i> Video Tutorial
                </a>

            </nav>

            <div class="p-3 border-t">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-3 py-2 rounded text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- TOPBAR --}}
            <header class="bg-white shadow-sm px-4 sm:px-6 py-3 flex justify-between items-center gap-3 sticky top-0 z-30">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Tombol hamburger (mobile only) --}}
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-800 p-1 flex-shrink-0">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="flex items-center space-x-4 flex-shrink-0">
                    <span class="text-sm text-gray-600 hidden sm:inline-flex items-center">
                        <i class="fas fa-user-circle mr-1"></i>
                        {{ auth()->user()->name }}
                    </span>
                    <span class="text-sm text-gray-600 sm:hidden">
                        <i class="fas fa-user-circle"></i>
                    </span>
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="flex-1 p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-open');
            document.getElementById('sidebar-overlay').classList.toggle('overlay-open');
        }
    </script>

    @stack('scripts')
</body>
</html>
