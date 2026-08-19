{{-- <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Card Background dengan performa ringan di HP, blur aktif otomatis di Laptop/PC */
        .glass-card {
            background: rgba(15, 23, 42, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        @media (min-width: 768px) {
            .glass-card {
                background: rgba(15, 23, 42, 0.5);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
            }
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(96, 165, 250, 0.6);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
    </style>
</head>
<body class="bg-gray-950 font-sans antialiased overflow-x-hidden">
    <div class="min-h-screen relative flex flex-col items-center justify-center p-3 sm:p-6 lg:p-8 pb-16 sm:pb-14" x-data="loginForm()">
        
        <!-- BACKGROUND IMAGE -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/foto-smk.png') }}" alt="Background Sekolah" class="w-full h-full object-cover filter brightness-90">
            <div class="absolute inset-0 bg-gradient-to-tr from-blue-950/90 via-slate-900/85 to-indigo-950/80"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.8%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>
        </div>

        <!-- 🖼️ BANNER TAGLINE DI ATAS LAYAR (DIPERBESAR & RESPONSIVE) -->
        <div class="relative z-10 w-full max-w-4xl mb-4 sm:mb-6 flex justify-center px-2">
            <div class="glass-card px-4 py-2 sm:px-5 sm:py-3 rounded-2xl shadow-xl border border-white/15 bg-white/5">
                <img src="{{ asset('images/tagline.png') }}" alt="Banner SMK" class="w-[340px] sm:w-[500px] lg:w-[650px] h-auto object-contain">
            </div>
        </div>

        <!-- MAIN CONTAINER -->
        <div class="relative z-10 w-full max-w-[360px] sm:max-w-xl lg:max-w-4xl glass-card rounded-[1.75rem] sm:rounded-[2rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row text-white mb-6">
            
            <!-- LEFT SIDE: BRANDING -->
            <div class="px-5 py-6 sm:px-8 sm:py-10 flex flex-col items-center justify-center text-center lg:w-1/2 lg:border-r border-white/10 relative">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white/10 border border-white/20 p-2 sm:p-2.5 shadow-lg flex items-center justify-center mb-2.5 sm:mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">     
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black tracking-tight text-white leading-none">DIDISPEN</h1>
                    <p class="text-blue-200/80 text-[10px] sm:text-[11px] mt-1 font-semibold tracking-wide">Digital Dispensasi Pendidikan</p>
                </div>

                <!-- Kutipan (Hanya Tampil di Laptop) -->
                <div class="hidden lg:block my-8 relative z-10 w-full">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <p class="text-xs text-blue-100/90 italic leading-relaxed">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, transparan, dan terintegrasi."
                        </p>
                    </div>
                </div>

                <!-- TAB SELECTOR PERAN -->
                <div class="relative z-10 w-full mt-4 sm:mt-6 lg:mt-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-widest mb-1.5 font-semibold">Pilih Peran Masuk</p>
                    <div class="flex bg-black/30 p-1 rounded-xl border border-white/10">
                        <template x-for="role in roles" :key="role.id">
                            <button type="button" @click="activeRole = role.id"
                                class="flex-1 py-1.5 rounded-lg text-[10px] sm:text-[11px] font-semibold transition-all duration-300"
                                :class="activeRole === role.id ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:text-white'"
                                x-text="role.label">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE: FORM LOGIN -->
            <div class="px-5 py-6 sm:px-8 sm:py-10 flex flex-col justify-center relative bg-black/20 lg:w-1/2 border-t lg:border-t-0 border-white/10 space-y-4 sm:space-y-5">
                
                <div class="space-y-3 sm:space-y-4">
                    <!-- Form Title -->
                    <div class="text-center lg:text-left">
                        <h2 class="text-lg sm:text-2xl font-bold tracking-tight text-white" x-text="'Login ' + getActiveRole().label"></h2>
                        <p class="text-gray-300 text-[10px] sm:text-xs mt-0.5" x-text="getActiveRole().description"></p>
                    </div>

                    <!-- Error Alert -->
                    @if ($errors->any())
                        <div class="p-2 sm:p-2.5 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200 text-[10px] flex items-start space-x-2">
                            <i class="fas fa-exclamation-circle mt-0.5"></i>
                            <div>
                                <span class="font-bold">Gagal:</span>
                                <span class="mt-0.5">{{ $errors->first() }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- FORM UTAMA -->
                    <div class="relative min-h-[190px] sm:min-h-[195px]">
                        
                        <!-- ================= SISWA ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'student' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-3 sm:space-y-3.5" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="siswa">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Email Siswa</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="email" value="{{ old('email') }}" required placeholder="zainal@gmail.com" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>
                                <div x-data="{ showPw: false }">
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" :disabled="loading" class="w-full h-[40px] sm:h-[42px] mt-1 rounded-xl text-[11px] sm:text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                </button>
                            </form>
                        </div>

                        <!-- ================= GURU / ADMIN ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'teacher' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-3 sm:space-y-3.5" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="guru">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Email / Username Guru</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="text" value="{{ old('email') }}" required placeholder="gurupiket@smkn1bangsri.sch.id" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>
                                <div x-data="{ showPw: false }">
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" :disabled="loading" class="w-full h-[40px] sm:h-[42px] mt-1 rounded-xl text-[11px] sm:text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                </button>
                            </form>
                        </div>

                        <!-- ================= SATPAM ================= -->
                        <div class="absolute inset-0 w-full transition-all duration-300 ease-in-out"
                             :class="activeRole === 'security' ? 'opacity-100 translate-y-0 pointer-events-auto z-10' : 'opacity-0 translate-y-2 pointer-events-none z-0'">
                            <form method="POST" action="{{ route('login') }}" class="space-y-3 sm:space-y-3.5" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="role" value="satpam">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Email Satpam</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-envelope"></i></span>
                                        <input name="email" type="email" value="{{ old('email') }}" required placeholder="satpam@smkn1bangsri.sch.id" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-4 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                    </div>
                                </div>
                                <div x-data="{ showPw: false }">
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1 ml-0.5">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-[11px]"><i class="fas fa-lock"></i></span>
                                        <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="glass-input w-full h-[40px] sm:h-[42px] pl-10 pr-10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none">
                                        <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white text-xs focus:outline-none">
                                            <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" :disabled="loading" class="w-full h-[40px] sm:h-[42px] mt-1 rounded-xl text-[11px] sm:text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2 disabled:opacity-70">
                                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Akun Demo -->
                    <div class="pt-2 border-t border-white/10" x-data="{ showDemo: false }">
                        <button @click="showDemo = !showDemo" type="button" class="w-full flex items-center justify-center text-[10px] text-gray-400 hover:text-white transition-colors">
                            <span>🔑 Lihat Kredensial Akun Demo</span>
                            <i class="fas fa-chevron-down transition-transform duration-300 ml-1.5" :class="showDemo ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="showDemo" x-transition class="mt-2.5 space-y-1 text-[9px] text-gray-300 bg-white/5 p-2.5 rounded-xl border border-white/10">
                            <p>👨‍🏫 <strong>Guru:</strong> <span class="text-blue-300 font-mono">gurupiket@smkn1bangsri.sch.id</span> (gurupiket2026)</p>
                            <p>👮 <strong>Satpam:</strong> <span class="text-blue-300 font-mono">satpam@smkn1bangsri.sch.id</span> (password)</p>
                            <p>👨‍🎓 <strong>Siswa:</strong> <span class="text-blue-300 font-mono">zainal@gmail.com</span> (password)</p>
                            <p>👨‍💼 <strong>Admin:</strong> <span class="text-blue-300 font-mono">admin@sch.id</span> (password)</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Footer / Copyright dengan By 3M -->
        <div class="relative sm:absolute bottom-3 text-center w-full text-[10px] text-gray-400 space-y-0.5 mt-4 sm:mt-0 px-4">
            <p>© 2026 DIDISPEN. All rights reserved.</p>
            <p class="text-gray-300 font-medium">Developed with ❤️ by <span class="text-blue-400 font-semibold">By 3M</span> (Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief)</p>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                activeRole: 'student',
                loading: false,
                roles: [
                    { id: 'student', label: 'Siswa', description: 'Gunakan email siswa terdaftar' },
                    { id: 'teacher', label: 'Guru Piket', description: 'Gunakan email guru Piket' },
                    { id: 'security', label: 'Satpam', description: 'Gunakan email pos satpam' }
                ],
                getActiveRole() {
                    return this.roles.find(r => r.id === this.activeRole);
                }
            }
        }
    </script>
</body>
</html> --}}


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Watermark Background - Black & White */
        .watermark-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background-color: #f8fafc;
        }

        .watermark-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(100%) contrast(1.2) brightness(1.1);
            opacity: 0.15;
        }

        .watermark-overlay {
            position: absolute;
            inset: 0;
            background-image: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 40px,
                    rgba(0,0,0,0.02) 40px,
                    rgba(0,0,0,0.02) 80px
                );
            pointer-events: none;
        }

        /* Floating shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 12s ease-in-out infinite;
        }
        .shape-1 { width: 400px; height: 400px; background: rgba(148, 163, 184, 0.4); top: -100px; right: -100px; animation-delay: 0s; }
        .shape-2 { width: 300px; height: 300px; background: rgba(100, 116, 139, 0.35); bottom: -50px; left: -100px; animation-delay: 3s; }
        .shape-3 { width: 250px; height: 250px; background: rgba(71, 85, 105, 0.3); top: 50%; left: 50%; animation-delay: 6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.08); }
        }

        /* Card styling */
        .login-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(0, 0, 0, 0.03);
        }

        /* Input styling */
        .form-input {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            outline: none;
        }
        .form-input::placeholder { color: #94a3b8; }

        /* ROLE TABS - SLIDING INDICATOR ANIMATION */
        .role-tabs-wrapper {
            position: relative;
            background: #f1f5f9;
            border-radius: 0.75rem;
            padding: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        /* Sliding background indicator */
        .role-tab-slider {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc(33.333% - 4px);
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4), 0 2px 4px rgba(37, 99, 235, 0.2);
            transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }

        /* Tab buttons */
        .role-tab-btn {
            position: relative;
            z-index: 2;
            transition: color 0.3s ease, font-weight 0.3s ease;
            user-select: none;
        }

        .role-tab-btn.active {
            color: #ffffff;
            font-weight: 700;
        }

        .role-tab-btn:not(.active) {
            color: #64748b;
            font-weight: 500;
        }

        .role-tab-btn:not(.active):hover {
            color: #334155;
        }

        /* Submit button */
        .btn-submit {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
            position: relative;
            overflow: hidden;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-submit:hover::before { left: 100%; }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.45);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Left panel - BLUE GRADIENT */
        .left-panel {
            background: linear-gradient(160deg, #1e3a8a 0%, #2563eb 40%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 30% 20%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }

        .grid-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .quote-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .demo-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
        }

        /* Form panel - SIMPLE FADE ONLY (no slide) */
        .form-panel {
            transition: opacity 0.25s ease;
        }

        .banner-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .icon-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .fullscreen-container {
            width: 100vw;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            position: relative;
        }

        @media (max-width: 1024px) {
            .login-card { max-width: 600px; }
        }

        @media (max-width: 640px) {
            .fullscreen-container { padding: 0.5rem; }
            .login-card { max-width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Watermark Background -->
    <div class="watermark-bg">
        <img src="{{ asset('images/foto-smk.png') }}" alt="Background" class="watermark-image">
        <div class="watermark-overlay"></div>
    </div>

    <!-- Floating background shapes -->
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Main Full Screen Container -->
    <div class="fullscreen-container" x-data="loginForm()">

        <!-- 🖼️ BANNER TAGLINE -->
        <div class="relative z-10 w-full max-w-4xl mb-3 sm:mb-4 flex justify-center px-2">
            <div class="banner-glass px-3 py-2 sm:px-5 sm:py-2.5 rounded-lg flex justify-center">
                <img src="{{ asset('images/tagline.png') }}" alt="Banner SMK" class="w-[220px] sm:w-[350px] lg:w-[480px] h-auto object-contain">
            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="relative z-10 w-full max-w-4xl login-card rounded-xl overflow-hidden flex flex-col lg:flex-row shadow-2xl">

            <!-- ===== LEFT PANEL: BRANDING (BLUE) ===== -->
            <div class="left-panel px-4 py-6 sm:px-6 sm:py-8 flex flex-col items-center justify-center text-center lg:w-[40%] relative min-h-[240px] lg:min-h-full">
                <div class="grid-pattern"></div>

                <div class="relative z-10 flex flex-col items-center w-full">
                    <!-- Logo -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg icon-badge p-2 sm:p-2.5 shadow-2xl flex items-center justify-center mb-3 sm:mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>

                    <!-- Title -->
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white leading-none mb-0.5">DIDISPEN</h1>
                    <p class="text-blue-100 text-[10px] sm:text-[11px] font-semibold tracking-wider uppercase">Digital Dispensasi Pendidikan</p>

                    <!-- Decorative line -->
                    <div class="w-10 h-0.5 bg-white/40 rounded-full my-3 sm:my-4"></div>

                    <!-- Quote -->
                    <div class="quote-card rounded-lg p-3 sm:p-4 max-w-[220px] mx-auto">
                        <i class="fas fa-quote-left text-white/60 text-[10px] mb-1.5 block"></i>
                        <p class="text-[10px] sm:text-[11px] text-white italic leading-relaxed font-medium">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, transparan, dan terintegrasi."
                        </p>
                    </div>

                    <!-- Stats/Features -->
                    <div class="mt-4 grid grid-cols-3 gap-2 w-full max-w-[200px]">
                        <div class="text-center">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-0.5">
                                <i class="fas fa-shield-alt text-white text-[9px]"></i>
                            </div>
                            <p class="text-[8px] text-white/90">Aman</p>
                        </div>
                        <div class="text-center">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-0.5">
                                <i class="fas fa-bolt text-white text-[9px]"></i>
                            </div>
                            <p class="text-[8px] text-white/90">Cepat</p>
                        </div>
                        <div class="text-center">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-0.5">
                                <i class="fas fa-link text-white text-[9px]"></i>
                            </div>
                            <p class="text-[8px] text-white/90">Terintegrasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT PANEL: ROLE SELECTOR + FORM ===== -->
            <div class="px-4 py-5 sm:px-6 sm:py-6 flex flex-col justify-center lg:w-[60%] bg-white relative">

                <!-- ROLE TABS DENGAN ANIMASI GESER (SLIDING) -->
                <div class="mb-3 sm:mb-4">
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-1.5 text-center lg:text-left">Pilih Peran Masuk</p>
                    
                    <div class="role-tabs-wrapper" x-ref="tabsWrapper">
                        <!-- Sliding Indicator (Background Biru yang Bergerak) -->
                        <div class="role-tab-slider"
                             :style="`transform: translateX(calc(${activeRoleIndex()} * 100% + ${activeRoleIndex()} * 4px))`">
                        </div>

                        <!-- Tab Buttons -->
                        <div class="relative z-10 flex">
                            <template x-for="(role, index) in roles" :key="role.id">
                                <button type="button" 
                                        @click="switchRole(role.id)"
                                        class="role-tab-btn flex-1 py-2 sm:py-2.5 rounded-md text-[10px] sm:text-[11px] flex items-center justify-center gap-1 cursor-pointer"
                                        :class="{ 'active': activeRole === role.id }"
                                        x-text="role.label">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Form Title -->
                <div class="text-center lg:text-left mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight text-slate-800 mb-0.5" x-text="'Login ' + getActiveRole().label"></h2>
                    <p class="text-slate-400 text-[10px] sm:text-[11px]" x-text="getActiveRole().description"></p>
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="mb-3 p-2 rounded-lg bg-red-50 border border-red-200 text-red-600 text-[10px] flex items-start space-x-1.5">
                        <i class="fas fa-exclamation-circle mt-0.5 text-red-400 text-[9px]"></i>
                        <div>
                            <span class="font-bold">Gagal:</span>
                            <span class="mt-0.5">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <!-- FORM AREA - SIMPLE FADE (NO SLIDE ANIMATION) -->
                <div class="relative min-h-[160px] sm:min-h-[180px]">

                    <!-- ================= SISWA ================= -->
                    <div class="form-panel absolute inset-0 w-full"
                         x-show="activeRole === 'student'"
                         :class="activeRole === 'student' ? 'opacity-100' : 'opacity-0'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-2.5 sm:space-y-3" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="siswa">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Email Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="email" value="{{ old('email') }}" required placeholder="zainal@gmail.com" class="form-input w-full h-9 pl-9 pr-3 rounded-lg text-slate-700 text-xs focus:outline-none">
                                </div>
                            </div>
                            <div x-data="{ showPw: false }">
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="form-input w-full h-9 pl-9 pr-9 rounded-lg text-slate-700 text-xs focus:outline-none">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs focus:outline-none">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" :disabled="loading" class="btn-submit w-full h-9 mt-1 rounded-lg text-xs font-bold text-white flex items-center justify-center space-x-1.5 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin text-xs"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- ================= GURU PIKET ================= -->
                    <div class="form-panel absolute inset-0 w-full"
                         x-show="activeRole === 'teacher'"
                         :class="activeRole === 'teacher' ? 'opacity-100' : 'opacity-0'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-2.5 sm:space-y-3" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="guru">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Email / Username Guru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" value="{{ old('email') }}" required placeholder="gurupiket@smkn1bangsri.sch.id" class="form-input w-full h-9 pl-9 pr-3 rounded-lg text-slate-700 text-xs focus:outline-none">
                                </div>
                            </div>
                            <div x-data="{ showPw: false }">
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="form-input w-full h-9 pl-9 pr-9 rounded-lg text-slate-700 text-xs focus:outline-none">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs focus:outline-none">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" :disabled="loading" class="btn-submit w-full h-9 mt-1 rounded-lg text-xs font-bold text-white flex items-center justify-center space-x-1.5 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin text-xs"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- ================= SATPAM ================= -->
                    <div class="form-panel absolute inset-0 w-full"
                         x-show="activeRole === 'security'"
                         :class="activeRole === 'security' ? 'opacity-100' : 'opacity-0'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-2.5 sm:space-y-3" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="satpam">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Email Satpam</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="email" value="{{ old('email') }}" required placeholder="satpam@smkn1bangsri.sch.id" class="form-input w-full h-9 pl-9 pr-3 rounded-lg text-slate-700 text-xs focus:outline-none">
                                </div>
                            </div>
                            <div x-data="{ showPw: false }">
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="form-input w-full h-9 pl-9 pr-9 rounded-lg text-slate-700 text-xs focus:outline-none">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs focus:outline-none">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" :disabled="loading" class="btn-submit w-full h-9 mt-1 rounded-lg text-xs font-bold text-white flex items-center justify-center space-x-1.5 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin text-xs"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Akun Demo -->
                <div class="pt-2 mt-2 border-t border-slate-100" x-data="{ showDemo: false }">
                    <button @click="showDemo = !showDemo" type="button" class="w-full flex items-center justify-center text-[10px] text-slate-400 hover:text-primary-600 transition-colors group py-1">
                        <span>🔑 Lihat Kredensial Akun Demo</span>
                        <i class="fas fa-chevron-down transition-transform duration-300 ml-1 group-hover:text-primary-500 text-[9px]" :class="showDemo ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="showDemo" x-transition class="mt-1.5 space-y-1 text-[10px] text-slate-600 demo-box p-2 rounded-lg">
                        <div class="flex items-start gap-1.5">
                            <span class="text-sm">👨‍🏫</span>
                            <div>
                                <p class="font-bold text-slate-700">Guru Piket</p>
                                <p class="text-primary-600 font-mono text-[9px]">gurupiket@smkn1bangsri.sch.id</p>
                                <p class="text-slate-400 text-[9px]">Password: gurupiket2026</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="text-sm">👮</span>
                            <div>
                                <p class="font-bold text-slate-700">Satpam</p>
                                <p class="text-primary-600 font-mono text-[9px]">satpam@smkn1bangsri.sch.id</p>
                                <p class="text-slate-400 text-[9px]">Password: password</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="text-sm">👨🎓</span>
                            <div>
                                <p class="font-bold text-slate-700">Siswa</p>
                                <p class="text-primary-600 font-mono text-[9px]">zainal@gmail.com</p>
                                <p class="text-slate-400 text-[9px]">Password: password</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 text-center w-full text-[10px] text-slate-400 space-y-0.5 mt-3 px-4">
            <p class="font-medium">© 2026 DIDISPEN. All rights reserved.</p>
            <p class="text-slate-500">Developed with <span class="text-red-500">❤</span> by <span class="text-primary-600 font-bold">By 3M</span></p>
            <p class="text-[8px] text-slate-400">(Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief)</p>
        </div>

    </div>

    <script>
        function loginForm() {
            return {
                activeRole: 'student',
                loading: false,
                roles: [
                    { id: 'student', label: 'Siswa', description: 'Gunakan email siswa terdaftar' },
                    { id: 'teacher', label: 'Guru Piket', description: 'Gunakan email guru Piket' },
                    { id: 'security', label: 'Satpam', description: 'Gunakan email pos satpam' }
                ],
                activeRoleIndex() {
                    return this.roles.findIndex(r => r.id === this.activeRole);
                },
                getActiveRole() {
                    return this.roles.find(r => r.id === this.activeRole);
                },
                switchRole(roleId) {
                    this.activeRole = roleId;
                }
            }
        }
    </script>
</body>
</html>