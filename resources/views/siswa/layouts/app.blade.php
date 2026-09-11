<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Siswa') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">

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

    <div class="flex h-screen overflow-hidden">

        {{-- ================================================== --}}
        {{-- SIDEBAR — HANYA MUNCUL DI DESKTOP (lg ke atas)      --}}
        {{-- ================================================== --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-white border-r border-gray-200">

            {{-- Brand --}}
            <div class="p-5 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                        @if(file_exists(public_path('images/logo-didispen.jpeg')))
                            <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo" class="w-full h-full object-contain">
                        @else
                            <i class="fas fa-school text-blue-600 text-lg"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-gray-900">DIDISPEN</h1>
                        <p class="text-xs text-gray-500">Panel Siswa</p>
                    </div>
                </div>
            </div>

            {{-- Nav Desktop --}}
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="{{ route('siswa.dashboard') }}"
                   class="{{ request()->routeIs('siswa.dashboard') ? $navOn : $navOff }}">
                    <i class="fas fa-home w-5 mr-3"></i> Dashboard
                </a>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 px-4">Dispensasi</p>
                <a href="{{ route('siswa.pengajuan.index') }}"
                   class="{{ request()->routeIs('siswa.pengajuan.index') ? $navOn : $navOff }}">
                    <i class="fas fa-file-alt w-5 mr-3"></i> Pengajuan
                </a>
                <a href="{{ route('siswa.pengajuan.create') }}"
                   class="{{ request()->routeIs('siswa.pengajuan.create') ? $navOn : $navOff }}">
                    <i class="fas fa-plus-circle w-5 mr-3"></i> Buat Pengajuan
                </a>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 px-4">Lainnya</p>
                <a href="{{ route('siswa.notifikasi.index') }}"
                   class="{{ request()->routeIs('siswa.notifikasi.*') ? $navOn : $navOff }}">
                    <i class="fas fa-bell w-5 mr-3"></i> Notifikasi
                    @if($notif > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $notif }}</span>
                    @endif
                </a>
                <a href="{{ route('panduan') }}"
                   class="{{ request()->routeIs('panduan') ? $navOn : $navOff }}">
                    <i class="fas fa-book w-5 mr-3"></i> Panduan
                </a>
                <a href="{{ route('profil.show') }}"
                   class="{{ request()->routeIs('profil.*') ? $navOn : $navOff }}">
                    <i class="fas fa-user w-5 mr-3"></i> Profil Saya
                </a>
            </nav>

            {{-- Logout Desktop --}}
            <div class="p-4 border-t border-gray-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- ================================================== --}}
        {{-- MAIN AREA                                           --}}
        {{-- ================================================== --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- TOPBAR --}}
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3">

                {{-- Topbar MOBILE: ringkas, muat di layar kecil --}}
                <div class="lg:hidden flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            @if(file_exists(public_path('images/logo-didispen.jpeg')))
                                <img src="{{ asset('images/logo-didispen.jpeg') }}" alt="Logo" class="w-full h-full object-contain">
                            @else
                                <i class="fas fa-school text-blue-600 text-sm"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-gray-900">DIDISPEN</h1>
                            <p class="text-xs text-gray-500">@yield('page-title', 'Dashboard')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-50 text-green-700 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                            Online
                        </span>
                    </div>
                </div>

                {{-- Topbar DESKTOP --}}
                <div class="hidden lg:flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('siswa.pengajuan.create') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i> Buat Pengajuan
                        </a>
                        <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">Siswa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- KONTEN (pb-28 agar tidak tertutup bottom nav di HP) --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 pb-24 lg:pb-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- BOTTOM NAVIGATION BAR — KHUSUS MOBILE               --}}
    {{-- ================================================== --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-2 py-2 z-50">
        <div class="flex items-center justify-around">

            {{-- 1. Beranda --}}
            <a href="{{ route('siswa.dashboard') }}"
               class="flex flex-col items-center gap-1 px-3 py-2 rounded-lg {{ request()->routeIs('siswa.dashboard') ? $mobOn : $mobOff }}">
                <i class="fas fa-home text-lg"></i>
                <span class="text-xs font-medium">Beranda</span>
            </a>

            {{-- 2. Riwayat --}}
            <a href="{{ route('siswa.pengajuan.index') }}"
               class="flex flex-col items-center gap-1 px-3 py-2 rounded-lg {{ request()->routeIs('siswa.pengajuan.index') ? $mobOn : $mobOff }}">
                <i class="fas fa-history text-lg"></i>
                <span class="text-xs font-medium">Riwayat</span>
            </a>

            {{-- 3. FAB: Buat Pengajuan (tombol utama, menonjol ke atas) --}}
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="flex flex-col items-center -mt-6">
                <div class="w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/30 active:scale-95 transition-transform">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 mt-1">Buat</span>
            </a>

            {{-- 4. Notifikasi (+badge) --}}
            <a href="{{ route('siswa.notifikasi.index') }}"
               class="flex flex-col items-center gap-1 px-3 py-2 rounded-lg relative {{ request()->routeIs('siswa.notifikasi.*') ? $mobOn : $mobOff }}">
                <i class="fas fa-bell text-lg"></i>
                @if($notif > 0)
                    <span class="absolute top-1 right-2 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center">{{ $notif }}</span>
                @endif
                <span class="text-xs font-medium">Notifikasi</span>
            </a>

            {{-- 5. Akun (membuka bottom sheet) --}}
            <button onclick="document.getElementById('accountSheet').classList.remove('translate-y-full')"
                    class="flex flex-col items-center gap-1 px-3 py-2 rounded-lg {{ request()->routeIs('profil.*') ? $mobOn : $mobOff }}">
                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-xs font-bold text-gray-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <span class="text-xs font-medium">Akun</span>
            </button>
        </div>
    </nav>

    {{-- ================================================== --}}
    {{-- BOTTOM SHEET AKUN (muncul saat tombol Akun ditekan) --}}
    {{-- ================================================== --}}
    <div id="accountSheet" class="lg:hidden fixed inset-0 z-50 flex items-end translate-y-full transition-transform duration-300">
        <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('translate-y-full')"></div>
        <div class="relative bg-white rounded-t-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-xl font-bold text-blue-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $siswa->kelas?->nama_kelas ?? '-' }} • NIS {{ $user->nis_nip }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('panduan') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-book w-5 mr-3 text-gray-400"></i> Panduan Penggunaan
                    </a>
                    <a href="{{ route('profil.show') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user w-5 mr-3 text-gray-400"></i> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4 pt-4 border-t border-gray-200">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-3 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-5 mr-3"></i> Keluar dari Akun
                        </button>
                    </form>
                    <button onclick="document.getElementById('accountSheet').classList.add('translate-y-full')"
                            class="w-full mt-2 px-4 py-3 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>
</html>
