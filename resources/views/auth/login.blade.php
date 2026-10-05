<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login — DIDISPEN</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 font-sans antialiased text-slate-800 min-h-screen flex flex-col justify-between" x-data="loginForm()" x-init="initPwa()">

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

                    <h1 class="text-xl font-bold text-white mb-0.5">DIDISPEN</h1>
                    <p class="text-blue-100 text-xs font-medium mb-4">Digital Dispensasi Pendidikan</p>

                    <div class="bg-white/10 rounded-xl p-3.5 border border-white/15 max-w-xs text-left">
                        <i class="fas fa-quote-left text-blue-200 text-xs mb-1 block"></i>
                        <p class="text-xs text-white/90 leading-relaxed">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, dan terintegrasi."
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-4 text-white/80 text-[11px] font-medium">
                        <div class="flex items-center gap-1"><i class="fas fa-shield-alt"></i> Aman</div>
                        <div class="flex items-center gap-1"><i class="fas fa-bolt"></i> Cepat</div>
                        <div class="flex items-center gap-1"><i class="fas fa-link"></i> Terintegrasi</div>
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
                                <i :class="role.icon"></i>
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
                        <i class="fas fa-exclamation-circle text-red-600 text-base flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-red-800">Gagal!</p>
                            <p class="text-red-700 mt-0.5">{{ $errors->first() }}</p>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-2.5 shadow-sm text-xs">
                        <i class="fas fa-check-circle text-emerald-600 text-base flex-shrink-0 mt-0.5"></i>
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
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required placeholder="Masukkan NIS atau email siswa" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div x-data="{ showPw: false }">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
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
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required autocomplete="username" placeholder="Masukkan NIP atau email guru" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
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
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" value="{{ old('email') }}" required placeholder="Masukkan ID atau email satpam" class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="Masukkan password" class="w-full h-10 pl-9 pr-9 rounded-xl border border-slate-200 bg-slate-50/50 text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 w-9 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-10 mt-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
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
                                <i class="fas fa-chevron-right text-[9px] group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER (Dengan background tipis agar tetap terbaca jelas di atas foto) -->
        <div class="mt-4 text-center w-full text-[11px] text-slate-300 font-medium drop-shadow-sm">
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
                    <i class="fas fa-arrow-down-to-bracket"></i>
                    <h3 class="font-bold text-sm">Pasang Pintasan Layar Utama</h3>
                </div>
                <button type="button" @click="showGuideModal = false" class="text-white/80 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-5">
                <div class="flex p-1 bg-slate-100 rounded-xl mb-4 text-xs font-semibold">
                    <button type="button" 
                            @click="activePlatform = 'android'" 
                            class="flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all"
                            :class="activePlatform === 'android' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600'">
                        <i class="fab fa-android text-emerald-500"></i> Android
                    </button>
                    <button type="button" 
                            @click="activePlatform = 'ios'" 
                            class="flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all"
                            :class="activePlatform === 'ios' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600'">
                        <i class="fab fa-apple"></i> iPhone / iPad
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

    <!-- ALPINE JS -->
    <script>
        function loginForm() {
            return {
                activeRole: '{{ old("role") == "siswa" ? "student" : (old("role") == "guru" ? "teacher" : (old("role") == "satpam" ? "security" : "student")) }}',
                loading: false,
                deferredPrompt: null,
                isInstalled: false,
                showGuideModal: false,
                activePlatform: 'android',

                roles: [
                    { id: 'student', label: 'Siswa', icon: 'fas fa-user-graduate', description: 'Gunakan NIS atau email siswa untuk masuk' },
                    { id: 'teacher', label: 'Guru Piket', icon: 'fas fa-chalkboard-teacher', description: 'Gunakan NIP atau email guru untuk masuk' },
                    { id: 'security', label: 'Satpam', icon: 'fas fa-user-shield', description: 'Gunakan ID atau email satpam untuk masuk' }
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