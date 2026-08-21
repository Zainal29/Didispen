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

        /* Sliding background indicator (Lebar disesuaikan untuk 3 tab: 33.333%) */
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

        <!-- BANNER TAGLINE -->
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

                <!-- ROLE TABS DENGAN ANIMASI GESER -->
                <div class="mb-3 sm:mb-4">
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-1.5 text-center lg:text-left">Pilih Peran Masuk</p>
                    
                    <div class="role-tabs-wrapper" x-ref="tabsWrapper">
                        <!-- Sliding Indicator -->
                        <div class="role-tab-slider"
                             :style="`transform: translateX(calc(${activeRoleIndex()} * 100% + ${activeRoleIndex()} * 4px))`">
                        </div>

                        <!-- Tab Buttons (Hanya 3: Siswa, Guru Piket, Satpam) -->
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

                <!-- FORM AREA -->
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
                                    <input name="email" type="email" value="{{ old('email') }}" required placeholder="nis@smkn1bangsri.sch.id" class="form-input w-full h-9 pl-9 pr-3 rounded-lg text-slate-700 text-xs focus:outline-none">
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

                    <!-- ================= GURU PIKET / ADMIN ================= -->
                    <div class="form-panel absolute inset-0 w-full"
                         x-show="activeRole === 'teacher'"
                         :class="activeRole === 'teacher' ? 'opacity-100' : 'opacity-0'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-2.5 sm:space-y-3" @submit="loading = true">
                            @csrf
                            <!-- Catatan: Backend harus mengizinkan role 'guru' ATAU 'admin' untuk form ini -->
                            <input type="hidden" name="role" value="guru"> 
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 ml-0.5">Email Guru / Admin</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="email" value="{{ old('email') }}" required placeholder="email@smkn1bangsri.sch.id" class="form-input w-full h-9 pl-9 pr-3 rounded-lg text-slate-700 text-xs focus:outline-none">
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
                            <p class="text-[9px] text-slate-400 text-center mt-1 italic">*Admin juga dapat login melalui form ini</p>
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
                {{-- <div class="pt-2 mt-2 border-t border-slate-100" x-data="{ showDemo: false }">
                    <button @click="showDemo = !showDemo" type="button" class="w-full flex items-center justify-center text-[10px] text-slate-400 hover:text-primary-600 transition-colors group py-1">
                        <span>🔑 Lihat Kredensial Akun Demo</span>
                        <i class="fas fa-chevron-down transition-transform duration-300 ml-1 group-hover:text-primary-500 text-[9px]" :class="showDemo ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="showDemo" x-transition class="mt-1.5 space-y-1 text-[10px] text-slate-600 demo-box p-2 rounded-lg">
                        <div class="flex items-start gap-1.5">
                            <span class="text-sm">👨‍🏫</span>
                            <div>
                                <p class="font-bold text-slate-700">Guru Piket / Admin</p>
                                <p class="text-primary-600 font-mono text-[9px]">gurupiket@smkn1bangsri.sch.id</p>
                                <p class="text-slate-400 text-[9px]">Password: gurupiket2026</p>
                                <p class="text-slate-400 text-[9px] mt-1 border-t border-slate-200 pt-1">Admin: admin@smkn1bangsri.sch.id</p>
                                <p class="text-slate-400 text-[9px]">Password: password</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="text-sm">👨‍🎓</span>
                            <div>
                                <p class="font-bold text-slate-700">Siswa</p>
                                <p class="text-primary-600 font-mono text-[9px]">zainal@gmail.com</p>
                                <p class="text-slate-400 text-[9px]">Password: password</p>
                            </div>
                        </div>
                    </div>
                </div> --}}

            </div>
        </div>

        <!-- Footer Bersih -->
        <div class="relative z-10 text-center w-full text-[10px] text-slate-400 space-y-0.5 mt-3 px-4">
            <p class="font-medium">© 2026 DIDISPEN. All rights reserved.</p>
        </div>

    </div>

    <script>
        function loginForm() {
            return {
                activeRole: 'student',
                loading: false,
                // HANYA 3 ROLE: Admin dihapus dari pilihan tab
                roles: [
                    { id: 'student', label: 'Siswa', description: 'Gunakan email siswa terdaftar' },
                    { id: 'teacher', label: 'Guru Piket', description: 'Gunakan email Guru Piket atau Admin' },
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