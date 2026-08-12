<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .sidebar-link.active { background-color: #1e40af; color: white; }
        .sidebar-link:hover:not(.active) { background-color: #e0e7ff; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        
        {{-- SIDEBAR --}}
        <aside class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-5 border-b">
                <h1 class="text-xl font-bold text-blue-800">    
                    <i class="fas fa-school mr-2"></i>DIDISPEN
                </h1>
                <p class="text-xs text-gray-500 mt-1">Panel Administrator</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                </a>

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Master Data</p>
                <a href="{{ route('admin.jurusan.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap w-5 mr-3"></i> Jurusan
                </a>
                <a href="{{ route('admin.kelas.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard w-5 mr-3"></i> Kelas
                </a>
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

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Sistem</p>
                <a href="{{ route('admin.settings.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5 mr-3"></i> Pengaturan
                </a>
                <a href="{{ route('admin.audit.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-history w-5 mr-3"></i> Audit Log
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
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- TOPBAR --}}
            <header class="bg-white shadow-sm px-6 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-user-circle mr-1"></i>
                        {{ auth()->user()->name }}
                    </span>
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>