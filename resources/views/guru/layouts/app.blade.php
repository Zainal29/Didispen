<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guru') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .sidebar-link.active { background-color: #059669; color: white; }
        .sidebar-link:hover:not(.active) { background-color: #d1fae5; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        
        {{-- SIDEBAR --}}
        <aside class="w-64 bg-white shadow-lg flex flex-col flex-shrink-0">
            <div class="p-5 border-b">
                <h1 class="text-xl font-bold text-green-800">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>DIDISPEN
                </h1>
                <p class="text-xs text-gray-500 mt-1">Panel Guru</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="{{ route('guru.dashboard') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                </a>

                <a href="{{ route('guru.checklog.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('guru.checklog.*') ? 'active' : '' }}">
                    <i class="fas fa-door-open w-5 mr-3"></i> Keluar/Masuk
                </a>

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Dispensasi</p>
                <a href="{{ route('guru.pengajuan.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('guru.pengajuan.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature w-5 mr-3"></i> 
                    <span class="flex-1">Verifikasi</span>
                    
                    {{-- ✅ BADGE: Jumlah Pengajuan Menunggu --}}
                    @if(isset($stats) && ($stats['pending'] ?? 0) > 0)
                        <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $stats['pending'] }}
                        </span>
                    @endif
                    
                    {{-- ✅ BADGE: Jumlah Siswa Sedang Keluar (Animate Pulse) --}}
                    @if(isset($siswaKeluar) && $siswaKeluar->count() > 0)
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full ml-1 animate-pulse">
                            {{ $siswaKeluar->count() }}
                        </span>
                    @endif
                </a>

                <p class="text-xs text-gray-400 uppercase mt-4 px-3 font-semibold">Profil & Data</p>
                <a href="{{ route('guru.tanda-tangan.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('guru.tanda-tangan.*') ? 'active' : '' }}">
                    <i class="fas fa-signature w-5 mr-3"></i> Tanda Tangan
                </a>
                <a href="{{ route('guru.laporan.index') }}" 
                   class="sidebar-link flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('guru.laporan.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar w-5 mr-3"></i> Laporan
                </a>
            </nav>

            <div class="p-3 border-t bg-gray-50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-3 py-2 rounded text-red-600 hover:bg-red-50 transition font-medium">
                        <i class="fas fa-sign-out-alt w-5 mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- TOPBAR --}}
            <header class="bg-white shadow-sm px-6 py-3 flex justify-between items-center z-10">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 font-medium bg-gray-100 px-3 py-1.5 rounded-full">
                        <i class="fas fa-user-circle mr-1 text-green-600"></i>
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

    {{-- ✅ GLOBAL SWEETALERT NOTIFICATION --}}
    @if(session('success'))
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
    @endif
</body>
</html>