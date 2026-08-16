<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PAMIT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(96, 165, 250, 0.6);
        }
    </style>
</head>
<body class="bg-gray-950 font-sans antialiased overflow-x-hidden">
    <div class="min-h-screen relative flex items-center justify-center p-4 sm:p-6 lg:p-8" x-data="loginForm()">
        
        <!-- BACKGROUND IMAGE DENGAN OVERLAY -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/fotosmk.PNG') }}" alt="Background Sekolah" class="w-full h-full object-cover scale-105 filter brightness-90">
            <!-- Gradient Overlay Biru Ungu Estetik -->
            <div class="absolute inset-0 bg-gradient-to-tr from-blue-950/90 via-indigo-950/80 to-slate-950/85"></div>
            <!-- Efek Noise Tipis -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.8%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>
        </div>

        <!-- MAIN CONTAINER (SPLIT GLASS CARD) -->
        <div class="relative z-10 w-full max-w-4xl glass-card rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 text-white">
            
            <!-- LEFT SIDE: BRANDING & QUOTE PANEL -->
            <div class="p-8 sm:p-10 flex flex-col justify-between items-center text-center lg:border-r border-white/10 relative">
                <!-- Decorative Glow -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-4 relative z-10 w-full flex flex-col items-center">
                    <!-- Logo Sekolah -->
                    <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 p-2.5 shadow-lg flex items-center justify-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">     
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-white">PAMIT</h1>
                        <p class="text-blue-200/80 text-[11px] mt-1 font-semibold tracking-wide">Permission & Absence Management Information Tracking</p>
                    </div>
                </div>

                <!-- Quote / Tagline Box -->
                <div class="my-8 relative z-10 w-full">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <p class="text-xs text-blue-100/90 italic leading-relaxed">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, transparan, dan terintegrasi."
                        </p>
                    </div>
                </div>

                <!-- Role Selector Tabs Kecil di Bawah Kiri -->
                <div class="relative z-10 w-full">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-2 font-semibold">Pilih Peran Masuk</p>
                    <div class="grid grid-cols-3 gap-1.5 bg-black/20 p-1 rounded-xl border border-white/10">
                        <template x-for="role in roles" :key="role.id">
                            <button
                                type="button"
                                @click="activeRole = role.id"
                                class="py-1.5 px-2 rounded-lg text-[11px] font-semibold transition-all duration-300"
                                :class="activeRole === role.id ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white'"
                                x-text="role.label"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE: FORM LOGIN PANEL -->
            <div class="p-8 sm:p-10 flex flex-col justify-between relative bg-black/20">
                
                <div class="space-y-6">
                    <!-- Header Form -->
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-white" x-text="'Login ' + getActiveRole().label"></h2>
                        <p class="text-gray-300 text-xs mt-1" x-text="getActiveRole().description"></p>
                    </div>

                    <!-- Error Alert -->
                    @if ($errors->any())
                        <div class="p-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200 text-xs flex items-start space-x-2">
                            <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                            <div>
                                <span class="font-bold">Gagal Masuk:</span>
                                <p class="mt-0.5">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- FORM UTAMA DENGAN TRANSISI HALUS -->
                    <div class="relative min-h-[220px]">
                        
                        <!-- ================= SISWA ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'student' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="siswa">
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Email Siswa</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="email" value="{{ old('email') }}" required placeholder="zainal@gmail.com" class="glass-input w-full h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>

                                <div x-data="{ showPw: false }">
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" :disabled="loading" class="w-full h-[42px] rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                    <i x-show="!loading" class="fas fa-arrow-right text-[10px]"></i>
                                </button>
                            </form>
                        </div>

                        <!-- ================= GURU / ADMIN ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'teacher' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="guru">
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Email / Username Guru</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="text" value="{{ old('email') }}" required placeholder="gurupiket@smkn1bangsri.sch.id" class="glass-input w-full h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>

                                <div x-data="{ showPw: false }">
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" :disabled="loading" class="w-full h-[42px] rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                    <i x-show="!loading" class="fas fa-arrow-right text-[10px]"></i>
                                </button>
                            </form>
                        </div>

                        <!-- ================= SATPAM ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'security' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="satpam">
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Email Satpam</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="email" value="{{ old('email') }}" required placeholder="satpam@smkn1bangsri.sch.id" class="glass-input w-full h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>

                                <div x-data="{ showPw: false }">
                                    <label class="block text-xs font-medium text-gray-300 mb-1.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-xs"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" :disabled="loading" class="w-full h-[42px] rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                    <i x-show="!loading" class="fas fa-arrow-right text-[10px]"></i>
                                </button>
                            </form>
                        </div>

                    </div>

                    <!-- Akun Demo Accordion -->
                    <div class="pt-2 border-t border-white/10" x-data="{ showDemo: false }">
                        <button @click="showDemo = !showDemo" class="w-full flex items-center justify-between text-[11px] text-gray-400 hover:text-white transition-colors">
                            <span>🔑 Lihat Kredensial Akun Demo</span>
                            <i class="fas fa-chevron-down transition-transform duration-300 text-[10px]" :class="showDemo ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="showDemo" x-transition class="mt-2 space-y-1.5 text-[10px] text-gray-300 bg-white/5 p-3 rounded-xl border border-white/10 backdrop-blur-md">
                            <p>👨‍🏫 <strong>Guru Piket:</strong> <span class="text-blue-300 font-mono">gurupiket@smkn1bangsri.sch.id</span> (gurupiket2026)</p>
                            <p>👮 <strong>Satpam:</strong> <span class="text-blue-300 font-mono">satpam@smkn1bangsri.sch.id</span> (password)</p>
                            <p>👨‍🎓 <strong>Siswa:</strong> <span class="text-blue-300 font-mono">zainal@gmail.com</span> (password)</p>
                            <p>👨‍💼 <strong>Admin:</strong> <span class="text-blue-300 font-mono">admin@sch.id</span> (password)</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Copyright -->
                <div class="mt-8 text-center text-[10px] text-gray-500">
                    <p>© 2026 PAMIT. All rights reserved.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function loginForm() {
            return {
                activeRole: 'student',
                loading: false,
                roles: [
                    { id: 'student', label: 'Siswa', description: 'Masukkan akun email siswa Anda' },
                    { id: 'teacher', label: 'Guru / Admin', description: 'Masukkan akun email guru atau admin' },
                    { id: 'security', label: 'Satpam', description: 'Masukkan akun email pos satpam' }
                ],
                getActiveRole() {
                    return this.roles.find(r => r.id === this.activeRole);
                }
            }
        }
    </script>
</body>
</html>