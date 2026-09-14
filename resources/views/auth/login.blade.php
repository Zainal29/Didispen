<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DIDISPEN</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- ✅ TAMBAHKAN INI DI SINI (di dalam <head>) -->
          <link rel="icon" type="image/png" href="{{ asset('images/logo-didispen.png') }}">
          <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-didispen.png') }}">
          <link rel="apple-touch-icon" href="{{ asset('images/logo-didispen.png') }}">

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
<body class="bg-slate-50 font-sans antialiased text-slate-800 min-h-screen flex flex-col">

    <!-- BACKGROUND IMAGE (Optimized) -->
    <div class="fixed inset-0 z-0">
        <img src="{{ asset('images/foto-smk.png') }}" alt="Background" class="w-full h-full object-cover opacity-50" decoding="async">
        <div class="absolute inset-0 bg-white/30"></div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="relative z-10 flex-1 flex flex-col items-center justify-center p-4 sm:p-6" x-data="loginForm()">

        <!-- TAGLINE BANNER - OPTIMIZED FOR MOBILE PERFORMANCE -->
        <div class="w-full max-w-md mb-4 sm:mb-6 flex justify-center px-4">
            <div class="bg-white/95 px-4 py-2 sm:px-6 sm:py-3 rounded-lg shadow-sm border border-gray-200 flex justify-center items-center">
                <img src="{{ asset('images/tagline.png') }}" alt="Banner SMK" class="h-10 sm:h-14 lg:h-16 w-auto object-contain" decoding="async">
            </div>
        </div>

        <!-- LOGIN CARD -->
        <div class="w-full max-w-4xl bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden flex flex-col lg:flex-row">

            <!-- LEFT PANEL: BRANDING -->
            <div class="bg-blue-600 px-6 py-8 sm:px-8 sm:py-10 flex flex-col items-center justify-center text-center lg:w-5/12 relative">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>

                <div class="relative z-10 flex flex-col items-center w-full">
                    <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center mb-4 shadow-sm">
                        <img src="{{ asset('images/logo-didispen.png') }}" alt="Logo" class="w-12 h-12 object-contain" decoding="async">
                    </div>

                    <h1 class="text-2xl font-bold text-white mb-1">DIDISPEN</h1>
                    <p class="text-blue-100 text-sm font-medium mb-6">Digital Dispensasi Pendidikan</p>

                    <div class="bg-white/10 rounded-lg p-4 border border-white/20 max-w-xs">
                        <i class="fas fa-quote-left text-blue-200 text-sm mb-2 block"></i>
                        <p class="text-sm text-white/90 leading-relaxed">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, dan terintegrasi."
                        </p>
                    </div>

                    <div class="mt-6 flex items-center gap-6 text-white/80 text-xs font-medium">
                        <div class="flex items-center gap-1.5"><i class="fas fa-shield-alt"></i> Aman</div>
                        <div class="flex items-center gap-1.5"><i class="fas fa-bolt"></i> Cepat</div>
                        <div class="flex items-center gap-1.5"><i class="fas fa-link"></i> Terintegrasi</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: FORM -->
            <div class="px-6 py-8 sm:px-8 sm:py-10 flex flex-col justify-center lg:w-7/12 bg-white">

                <!-- ROLE TABS -->
                <div class="mb-6">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-3 text-center lg:text-left">Pilih Peran Masuk</p>
                    <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-lg border border-slate-200">
                        <template x-for="role in roles" :key="role.id">
                            <button type="button" @click="switchRole(role.id)" class="py-2.5 rounded-md text-xs sm:text-sm font-semibold flex items-center justify-center gap-2 transition-all" :class="activeRole === role.id ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'">
                                <i :class="role.icon"></i>
                                <span x-text="role.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- TITLE -->
                <div class="text-center lg:text-left mb-6">
                    <h2 class="text-xl font-bold text-slate-800 mb-1" x-text="'Login ' + getActiveRole().label"></h2>
                    <p class="text-slate-500 text-sm" x-text="getActiveRole().description"></p>
                </div>

                <!-- ERROR MESSAGE -->
                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm flex items-start gap-2" x-data="{ show: true }" x-show="show">
                        <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                        <div class="flex-1">
                            <span class="font-semibold">Gagal:</span>
                            <span class="block mt-0.5">{{ $errors->first() }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 flex-shrink-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- FORMS CONTAINER -->
                <div class="w-full">
                    <!-- SISWA FORM -->
                    <div x-show="activeRole === 'student'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ loginId: '{{ old('email') }}' }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="siswa">

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email / NIS Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required placeholder="Masukkan NIS atau email siswa" class="w-full h-11 pl-10 pr-4 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                </div>
                            </div>

                            <div x-data="{ showPw: false }">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-11 pl-10 pr-10 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-11 mt-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- GURU FORM -->
                    <div x-show="activeRole === 'teacher'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ loginId: '{{ old('email') }}', showPw: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="guru">

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email / NIP Guru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required autocomplete="username" placeholder="Masukkan NIP atau email guru" class="w-full h-11 pl-10 pr-4 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan password" class="w-full h-11 pl-10 pr-10 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-11 mt-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- SATPAM FORM -->
                    <div x-show="activeRole === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPw: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="satpam">

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email / ID Satpam</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" value="{{ old('email') }}" required placeholder="Masukkan ID atau email satpam" class="w-full h-11 pl-10 pr-4 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="Masukkan password" class="w-full h-11 pl-10 pr-10 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full h-11 mt-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="mt-6 text-center w-full text-xs text-slate-400 font-medium">
            © 2026 DIDISPEN. All rights reserved.
        </div>
    </div>

    <!-- ALPINE JS -->
    <script>
        function loginForm() {
            return {
                activeRole: '{{ old("role") == "siswa" ? "student" : (old("role") == "guru" ? "teacher" : (old("role") == "satpam" ? "security" : "student")) }}',
                loading: false,
                roles: [
                    { id: 'student', label: 'Siswa', icon: 'fas fa-user-graduate', description: 'Gunakan NIS atau email siswa untuk masuk' },
                    { id: 'teacher', label: 'Guru Piket', icon: 'fas fa-chalkboard-teacher', description: 'Gunakan NIP atau email guru untuk masuk' },
                    { id: 'security', label: 'Satpam', icon: 'fas fa-user-shield', description: 'Gunakan ID atau email satpam untuk masuk' }
                ],
                switchRole(roleId) {
                    this.activeRole = roleId;
                    this.loading = false;
                },
                getActiveRole() {
                    return this.roles.find(role => role.id === this.activeRole);
                }
            }
        }
    </script>
</body>
</html>
