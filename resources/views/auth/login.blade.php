<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login — DIDISPEN</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tabler Icons Webfont (Modern, Stroke-based, Konsisten dengan Siswa, Guru & Satpam) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PWA & Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="manifest" href="/manifest.json">

    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DIDISPEN">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(error => {
                    console.error('Gagal mendaftarkan Service Worker:', error);
                });
            });
        }
    </script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
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
    </style>
</head>
<body class="bg-slate-900 font-sans antialiased text-slate-800 min-h-screen flex flex-col justify-between" x-data="loginForm()" x-init="initPwa()">
    @include('components.pwa-splash')

    <!-- BACKGROUND IMAGE TANPA HEAVY BLUR AGAR RINGAN DI HP -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/foto-smk.png') }}" alt="Background" class="w-full h-full object-cover opacity-100" decoding="async">
        <div class="absolute inset-0 bg-slate-950/80"></div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="relative z-10 flex-1 flex flex-col items-center justify-center p-3 sm:p-6 min-h-screen">

        <!-- TAGLINE BANNER -->
        <div class="w-full max-w-md mb-3 sm:mb-4 flex justify-center px-2">
            <div class="bg-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-xl shadow-md border border-slate-100 flex justify-center items-center">
                <img src="{{ asset('images/tagline.png') }}" alt="Banner SMK" class="h-8 sm:h-11 lg:h-12 w-auto object-contain" decoding="async">
            </div>
        </div>

        <!-- LOGIN CARD -->
        <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col lg:flex-row border border-slate-100">

           <!-- LEFT PANEL: BRANDING -->
<div class="bg-blue-600 px-6 py-6 sm:px-8 sm:py-8 flex flex-col items-center justify-center text-center lg:w-5/12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>

    <div class="relative z-10 flex flex-col items-center w-full">
        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center mb-3 shadow-md">
            <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo" class="w-10 h-10 object-contain" decoding="async">
        </div>

        <!-- DIBAWAH INI ADALAH TULISAN DIDISPEN DAN TOMBOL YANG SUDAH DIGABUNGKAN -->
<div class="relative w-full flex justify-center items-center mb-0.5">
    <h1 class="text-xl font-bold text-white">DIDISPEN</h1>
            
            <!-- BADGE VERSI DIGESER KE KANAN TANPA MERUSAK POSISI TENGAH TEKS -->
    <div class="absolute left-[calc(50%+4.5rem)] flex items-center">
        <button type="button" 
                @click="showChangelogModal = true"
                class="group flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-900/90 hover:bg-slate-900 text-white border border-slate-700/80 shadow-md backdrop-blur-md transition-all duration-200 hover:scale-105 active:scale-95 text-[10px] font-medium cursor-pointer"
                title="Lihat Catatan Pembaruan Sistem">
            <span class="relative flex h-1.5 w-1.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-blue-500"></span>
            </span>
            <span class="px-1 py-0.5 rounded-full text-[9px] font-mono font-bold bg-blue-600/30 text-blue-300 border border-blue-500/30">v2.4</span>
        </button>
    </div>
</div>


        <p class="text-blue-100 text-xs font-medium mb-4">Digital Dispensasi Pendidikan</p>

        <div class="bg-white/10 rounded-xl p-3.5 border border-white/15 max-w-xs text-left">
            <i class="ti ti-quote text-blue-200 text-sm mb-1 block"></i>
            <p class="text-xs text-white/90 leading-relaxed">
                "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, dan terintegrasi."
            </p>
        </div>

        <div class="mt-4 flex items-center gap-4 text-white/80 text-[11px] font-medium">
            <div class="flex items-center gap-1.5"><i class="ti ti-shield-check text-sm"></i> Aman</div>
            <div class="flex items-center gap-1.5"><i class="ti ti-bolt text-sm"></i> Cepat</div>
            <div class="flex items-center gap-1.5"><i class="ti ti-link text-sm"></i> Terintegrasi</div>
        </div>
    </div>
</div>


            <!-- RIGHT PANEL: FORM -->
            <div class="px-6 py-6 sm:px-8 sm:py-8 flex flex-col justify-center lg:w-7/12 bg-white">

                <!-- ROLE TABS -->
                <div class="mb-5">
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider font-bold mb-2 text-center lg:text-left">Pilih Peran Masuk</p>
                    <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200/60">
                        <template x-for="role in roles" :key="role.id">
                            <button type="button" @click="switchRole(role.id)" class="min-h-[38px] py-1.5 px-1 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all" :class="activeRole === role.id ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/40'">
                                <i :class="role.icon" class="text-sm"></i>
                                <span x-text="role.label" class="truncate"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- TITLE -->
                <div class="text-center lg:text-left mb-5">
                    <h2 class="text-lg font-bold text-slate-800 mb-0.5" x-text="'Login ' + getActiveRole().label"></h2>
                    <p class="text-slate-500 text-xs" x-text="getActiveRole().description"></p>
                </div>

                <!-- ERROR MESSAGE -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 flex items-start gap-2.5 shadow-sm text-xs" x-data="{ show: true }" x-show="show">
                        <i class="ti ti-alert-circle text-red-600 text-lg flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-red-800">Gagal!</p>
                            <p class="text-red-700 mt-0.5">{{ $errors->first() }}</p>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                            <i class="ti ti-x text-sm"></i>
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-2.5 shadow-sm text-xs">
                        <i class="ti ti-circle-check text-emerald-600 text-lg flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-emerald-800">Berhasil!</p>
                            <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- FORMS CONTAINER -->
                <div class="w-full">
                    <!-- SISWA FORM -->
                    <div x-show="activeRole === 'student'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <form method="POST" action="{{ route('login') }}" class="space-y-3.5" x-data="{ loginId: '{{ old('email') }}' }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="siswa">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Email / NIS Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-mail"></i></span>
                                    <input name="email" type="text" x-model="loginId" required placeholder="Masukkan NIS atau email siswa" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div x-data="{ showPw: false }">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'ti ti-eye-off' : 'ti ti-eye'" class="text-base"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label class="flex items-center gap-2 text-slate-600 hover:text-slate-800 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                    <span>Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="ti ti-loader-2 animate-spin text-base"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- GURU FORM -->
                    <div x-show="activeRole === 'teacher'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-3.5" x-data="{ loginId: '{{ old('email') }}', showPw: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="guru">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Email / NIP Guru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-mail"></i></span>
                                    <input name="email" type="text" x-model="loginId" required autocomplete="username" placeholder="Masukkan NIP atau email guru" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'ti ti-eye-off' : 'ti ti-eye'" class="text-base"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label class="flex items-center gap-2 text-slate-600 hover:text-slate-800 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                    <span>Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="ti ti-loader-2 animate-spin text-base"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- SATPAM FORM -->
                    <div x-show="activeRole === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-3.5" x-data="{ showPw: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="satpam">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Email / ID Satpam</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-mail"></i></span>
                                    <input name="email" type="text" value="{{ old('email') }}" required placeholder="Masukkan ID atau email satpam" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-base"><i class="ti ti-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'ti ti-eye-off' : 'ti ti-eye'" class="text-base"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label class="flex items-center gap-2 text-slate-600 hover:text-slate-800 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                    <span>Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="ti ti-loader-2 animate-spin text-base"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- TOMBOL INSTAL SHORTCUT DENGAN LOGO SMK -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <button type="button" 
                                @click="handleInstallClick()" 
                                id="btn-install-shortcut"
                                class="w-full flex items-center justify-between p-2.5 rounded-xl border border-blue-100 bg-blue-50/60 hover:bg-blue-100/80 transition-colors text-left group gap-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-lg bg-white border border-blue-100 flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden p-1">
                                    <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo SMK" class="w-full h-full object-contain" decoding="async">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-xs font-bold text-slate-800 truncate" x-text="isInstalled ? 'Aplikasi Sudah Terpasang' : 'Pasang Pintasan Aplikasi'"></span>
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-600 text-white rounded tracking-wide shrink-0" x-show="!isInstalled">SHORTCUT</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate" x-text="isInstalled ? 'DIDISPEN berjalan sebagai aplikasi' : 'Akses cepat dari Layar Utama HP / PC'"></p>
                                </div>
                            </div>
                            <div class="text-xs font-semibold text-blue-600 pl-2 pr-1 flex items-center gap-1 shrink-0">
                                <span x-text="isInstalled ? 'Buka' : 'Pasang'"></span>
                                <i class="ti ti-chevron-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER (Dengan background tipis agar tetap terbaca jelas di atas foto) -->
        <div class="mt-4 text-center w-full text-[11px] text-slate-300 font-medium drop-shadow-sm">
            Butuh bantuan? Hubungi kami sekarang!  <a href="https://wa.me/6289509507443"  class="text-white hover:underline">Muhammad Zainal Arief</a>
            <a href="https://wa.me/62895396259831" class="text-white hover:underline">Muhammad Sabrian Nuh</a>
            <a href="https://wa.me/6282325433087" class="text-white hover:underline">Maulana Fahri Oktavian</a>
            <br>
            © 2026 DIDISPEN. Digawe 3M .
        </div>
    </div>

    <!-- MODAL PANDUAN MANUAL -->
    <div x-show="showGuideModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         style="display: none;">
        
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
             @click.away="showGuideModal = false">
            
            <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="ti ti-download text-lg"></i>
                    <h3 class="font-bold text-sm">Pasang Pintasan Layar Utama</h3>
                </div>
                <button type="button" @click="showGuideModal = false" class="text-white/80 hover:text-white">
                    <i class="ti ti-x text-base"></i>
                </button>
            </div>

            <div class="p-5">
                <div class="flex p-1 bg-slate-100 rounded-xl mb-4 text-xs font-semibold">
                    <button type="button" 
                            @click="activePlatform = 'android'" 
                            class="flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all"
                            :class="activePlatform === 'android' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600'">
                        <i class="ti ti-brand-android text-emerald-500 text-base"></i> Android
                    </button>
                    <button type="button" 
                            @click="activePlatform = 'ios'" 
                            class="flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all"
                            :class="activePlatform === 'ios' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600'">
                        <i class="ti ti-brand-apple text-base"></i> iPhone / iPad
                    </button>
                </div>

                <div x-show="activePlatform === 'android'" class="space-y-3 text-xs text-slate-600">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <p>Ketuk ikon menu titik tiga (<strong class="text-slate-800">⋮</strong>) di pojok kanan atas browser Chrome.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                        <p>Pilih menu <strong class="text-blue-700">"Tambahkan ke Layar Utama"</strong> atau <strong class="text-blue-700">"Instal Aplikasi"</strong>.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</span>
                        <p>Ketuk <strong class="text-slate-800">"Instal"</strong>. Ikon DIDISPEN akan muncul di Layar Utama HP.</p>
                    </div>
                </div>

                <div x-show="activePlatform === 'ios'" class="space-y-3 text-xs text-slate-600" style="display: none;">
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <p>Di browser Safari, ketuk tombol <strong class="text-blue-600">Bagikan (Share)</strong> di bilah bawah layar.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                        <p>Gulir ke bawah dan pilih <strong class="text-blue-700">"Tambah ke Layar Utama"</strong>.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</span>
                        <p>Ketuk <strong class="text-slate-800">"Tambah"</strong> di pojok kanan atas.</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showGuideModal = false" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- FLOATING CHANGELOG BUTTON -->
<!-- <div class="fixed bottom-4 left-1/2 -translate-x-1/2  sm:bottom-6 z-50">
    <button type="button" 
            @click="showChangelogModal = true"
            class="group flex items-center gap-2 px-3.5 py-2 rounded-full bg-slate-900/90 hover:bg-slate-900 text-white border border-slate-700/80 shadow-lg shadow-black/30 backdrop-blur-md transition-all duration-200 hover:scale-105 active:scale-95 text-xs font-medium cursor-pointer"
            title="Lihat Catatan Pembaruan Sistem">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
        </span>
        <i class="ti ti-history text-blue-400 group-hover:rotate-[-30deg] transition-transform duration-200"></i>
        <span class="text-slate-200 font-semibold hidden xs:inline sm:inline">Pembaruan</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-600/30 text-blue-300 border border-blue-500/30">v2.4</span>
    </button>
</div> -->


    <!-- MODAL CHANGELOG / CATATAN RILIS -->
    <div x-show="showChangelogModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-xs"
         style="display: none;">
        
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[88vh]"
             @click.away="showChangelogModal = false">
            
            <!-- HEADER -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-900 via-slate-850 to-blue-950 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400">
                        <i class="ti ti-git-branch text-base"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm sm:text-base text-white">Catatan Pembaruan Sistem</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30 font-mono">v2.5.0</span>
                        </div>
                        <p class="text-slate-400 text-xs mt-0.5">Riwayat peningkatan fitur & stabilitas DIDISPEN</p>
                    </div>
                </div>
                <button type="button" @click="showChangelogModal = false" class="w-8 h-8 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                    <i class="ti ti-x text-sm"></i>
                </button>
            </div>

            <!-- BODY LIST -->
            <div class="p-4 sm:p-5 overflow-y-auto space-y-5 text-xs text-slate-700">
                
                <!-- VERSI 2.5.0 -->
                <div class="relative pl-6 border-l-2 border-blue-500 space-y-2.5">
                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-xs"></div>
                    
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm">Versi 2.5.0</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Terbaru</span>
                        </div>
                        <span class="text-slate-600 text-[11px] font-medium">23 Oktober 2026</span>
                    </div>

                    <p class="text-slate-600 text-[11px] leading-relaxed">
                        Penyempurnaan aturan kedisiplinan keterlambatan transparan, kombinasi cerdas batas waktu tunggu permohonan, proteksi anti double-scan 4 lapis berkecepatan tinggi, dan penyelarasan laporan admin.
                    </p>

                    <div class="space-y-2 pt-1">
                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-clock-exclamation text-amber-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Aturan Keterlambatan Transparan & Sisa Toleransi Gerbang</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Siswa yang kembali melewati jam izin resmi dicatat "Terlambat" di rekapitulasi dan badge status. Toleransi 5 menit pos satpam berfungsi sebagai pelindung antrean gerbang dengan info sisa waktu toleransi.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-hourglass-low text-blue-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Kombinasi Cerdas Batas Waktu Menunggu ACC</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Batas kadaluarsa menunggu persetujuan Guru Piket dihitung cerdas antara jam pembuatan dan jam keluar terpilih. Siswa yang mengajukan lebih awal tidak lagi hangus sebelum jam keluar tiba.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-qrcode text-emerald-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Anti Double-Scan 4 Lapis & Scanner Satpam Kilat (1.5 Detik)</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Proteksi cooldown 5 detik per-QR, kunci transaksional database, proteksi jeda fisik 1 menit keluar-kembali, serta Mode Cepat (Express) super responsif untuk antrean ramai.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-layout-dashboard text-indigo-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Desain Laporan Admin & Single Desktop Logout</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Filter status realisasi lengkap (Sedang Keluar, Kembali Tepat Waktu, Terlambat, Sampai Pulang, Tidak Scan Kembali), navigasi rapi dengan tombol logout tunggal di header profil.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VERSI 2.4.0 -->
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-2.5">
                    <div class="absolute -left-[7px] top-0.5 w-3 h-3 rounded-full bg-slate-400 border-2 border-white"></div>
                    
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-800 text-xs">Versi 2.4.0</span>
                        </div>
                        <span class="text-slate-600 text-[11px] font-medium">Oktober 2026</span>
                    </div>

                    <p class="text-slate-600 text-[11px] leading-relaxed">
                        Peningkatan ketepatan waktu operasional pos satpam, otomatisasi status kadaluarsa, pencegahan penutupan dispensasi aktif di HP siswa, dan penyempurnaan responsivitas kartu.
                    </p>

                    <div class="space-y-2 pt-1">
                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-hourglass-low text-blue-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Toleransi 20 Menit & Status Kadaluarsa Otomatis</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Siswa yang disetujui memiliki waktu toleransi 20 menit untuk scan keluar ke satpam sebelum izin berstatus kadaluarsa.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-bell text-amber-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Notifikasi Layar & Anti-Keluar HP Siswa</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Peringatan status bar dan pengingat kembali jika aplikasi di-minimize, serta dialog pencegah tutup tab saat izin masih berjalan.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-shield-check text-emerald-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Mode Pos Satpam Express & Screen Wake Lock</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Penyimpanan login permanen akun satpam dan guru, mode scanner auto-confirm dengan audio beep, serta pencegah layar redup otomatis.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <i class="ti ti-user-check text-indigo-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <div>
                                <p class="font-bold text-slate-800">Fitur Ingat Saya & Tampilan Responsif</p>
                                <p class="text-slate-500 text-[11px] mt-0.5">Kotak centang Ingat Saya di login, serta perbaikan badge status dan nomor surat di dashboard siswa, guru, dan satpam.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VERSI 2.3.0 -->
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-2">
                    <div class="absolute -left-[7px] top-0.5 w-3 h-3 rounded-full bg-slate-400 border-2 border-white"></div>
                    
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="font-bold text-slate-800 text-xs">Versi 2.3.0</span>
                        <span class="text-slate-600 text-[11px]">September 2026</span>
                    </div>

                    <div class="space-y-1.5 text-slate-600 text-[11px]">
                        <div class="flex items-start gap-2">
                            <i class="ti ti-users text-purple-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <span>Dukungan Dispensasi Rombongan untuk izin kelompok dalam satu surat resmi.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-printer text-slate-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <span>Pencetakan struk bukti dispensasi via printer thermal bluetooth 58mm.</span>
                        </div>
                    </div>
                </div>

                <!-- VERSI 2.2.0 -->
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-2">
                    <div class="absolute -left-[7px] top-0.5 w-3 h-3 rounded-full bg-slate-400 border-2 border-white"></div>
                    
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="font-bold text-slate-800 text-xs">Versi 2.2.0</span>
                        <span class="text-slate-600 text-[11px]">Agustus 2026</span>
                    </div>

                    <div class="space-y-1.5 text-slate-600 text-[11px]">
                        <div class="flex items-start gap-2">
                            <i class="ti ti-brand-whatsapp text-emerald-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <span>Notifikasi WhatsApp otomatis kepada siswa saat pengajuan disetujui atau melewati batas waktu.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-qrcode text-blue-600 mt-0.5 flex-shrink-0 text-base"></i>
                            <span>Verifikasi QR Code terenkripsi untuk validasi pos gerbang satpam dan guru piket.</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium truncate">
                    <i class="ti ti-shield-check text-emerald-600 text-sm"></i>
                    <span class="truncate">Sistem Resmi SMK Negeri 1 Bangsri</span>
                </div>
                <button type="button" @click="showChangelogModal = false" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors shrink-0">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- ALPINE JS -->
    <script>
        function loginForm() {
            return {
                activeRole: '{{ old("role") == "siswa" ? "student" : (old("role") == "guru" ? "teacher" : (old("role") == "satpam" ? "security" : "student")) }}',
                loading: false,
                deferredPrompt: null,
                isInstalled: false,
                showGuideModal: false,
                showChangelogModal: false,
                activePlatform: 'android',

                roles: [
                    { id: 'student', label: 'Siswa', icon: 'ti ti-school', description: 'Gunakan NIS atau email siswa untuk masuk' },
                    { id: 'teacher', label: 'Guru Piket', icon: 'ti ti-user-check', description: 'Gunakan NIP atau email guru untuk masuk' },
                    { id: 'security', label: 'Satpam', icon: 'ti ti-shield-check', description: 'Gunakan ID atau email satpam untuk masuk' }
                ],

                initPwa() {
                    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                    if (isStandalone) {
                        this.isInstalled = true;
                    }

                    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                    if (isIos) {
                        this.activePlatform = 'ios';
                    }

                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                    });

                    window.addEventListener('appinstalled', () => {
                        this.isInstalled = true;
                        this.deferredPrompt = null;
                        this.showGuideModal = false;
                    });
                },

                switchRole(roleId) {
                    this.activeRole = roleId;
                    this.loading = false;
                },

                getActiveRole() {
                    return this.roles.find(role => role.id === this.activeRole);
                },

                async handleInstallClick() {
                    if (this.isInstalled) {
                        alert('Aplikasi DIDISPEN sudah terpasang di perangkat Anda!');
                        return;
                    }

                    if (this.deferredPrompt) {
                        try {
                            this.deferredPrompt.prompt();
                            const { outcome } = await this.deferredPrompt.userChoice;
                            if (outcome === 'accepted') {
                                this.isInstalled = true;
                                this.deferredPrompt = null;
                            }
                        } catch (e) {
                            this.showGuideModal = true;
                        }
                    } else {
                        this.showGuideModal = true;
                    }
                }
            }
        }
    </script>
</body>
</html>