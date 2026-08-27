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

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.03);
        }

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

        .role-tabs-wrapper {
            position: relative;
            background: #f1f5f9;
            border-radius: 0.75rem;
            padding: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .role-tab-slider {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc(33.333% - 4px);
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4), 0 2px 4px rgba(37, 99, 235, 0.2);
            transition: transform 0.3s ease;
            z-index: 1;
        }

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
            font-weight: 600;
        }

        .role-tab-btn:not(.active):hover {
            color: #334155;
        }

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
            padding: 1rem;
            position: relative;
        }

        @media (max-width: 1024px) {
            .login-card { max-width: 600px; }
        }

        @media (max-width: 640px) {
            .fullscreen-container { padding: 0.75rem; }
            .login-card { max-width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">

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
        <div class="relative z-10 w-full max-w-4xl mb-4 sm:mb-6 flex justify-center px-2">
            <div class="banner-glass px-4 py-3 sm:px-6 sm:py-3.5 rounded-xl flex justify-center">
                <img src="{{ asset('images/tagline.png') }}" alt="Banner SMK" class="w-[240px] sm:w-[380px] lg:w-[480px] h-auto object-contain">
            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="relative z-10 w-full max-w-4xl login-card rounded-2xl overflow-hidden flex flex-col lg:flex-row shadow-2xl">

            <!-- ===== LEFT PANEL: BRANDING (BLUE) ===== -->
            <div class="left-panel px-6 py-8 sm:px-8 sm:py-10 flex flex-col items-center justify-center text-center lg:w-[42%] relative min-h-[260px] lg:min-h-full">
                <div class="grid-pattern"></div>

                <div class="relative z-10 flex flex-col items-center w-full">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl icon-badge p-2.5 sm:p-3 shadow-2xl flex items-center justify-center mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white leading-none mb-1">DIDISPEN</h1>
                    <p class="text-blue-100 text-xs sm:text-sm font-semibold tracking-wider uppercase">Digital Dispensasi Pendidikan</p>

                    <div class="w-12 h-1 bg-white/40 rounded-full my-5"></div>

                    <div class="quote-card rounded-xl p-4 sm:p-5 max-w-[260px] mx-auto">
                        <i class="fas fa-quote-left text-white/60 text-sm mb-2 block"></i>
                        <p class="text-xs sm:text-sm text-white italic leading-relaxed font-medium">
                            "Pelacakan informasi manajemen izin dan ketidakhadiran siswa kini lebih cepat, aman, transparan, dan terintegrasi."
                        </p>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3 w-full max-w-[240px]">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-1.5 backdrop-blur-sm">
                                <i class="fas fa-shield-alt text-white text-sm"></i>
                            </div>
                            <p class="text-[10px] sm:text-xs font-medium text-white/90">Aman</p>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-1.5 backdrop-blur-sm">
                                <i class="fas fa-bolt text-white text-sm"></i>
                            </div>
                            <p class="text-[10px] sm:text-xs font-medium text-white/90">Cepat</p>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-1.5 backdrop-blur-sm">
                                <i class="fas fa-link text-white text-sm"></i>
                            </div>
                            <p class="text-[10px] sm:text-xs font-medium text-white/90">Terintegrasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT PANEL: ROLE SELECTOR + FORM ===== -->
            <div class="px-6 py-8 sm:px-8 sm:py-10 flex flex-col justify-center lg:w-[58%] bg-white/50">

                <!-- ROLE TABS -->
                <div class="mb-5 sm:mb-6">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-bold mb-2 text-center lg:text-left">Pilih Peran Masuk</p>

                    <div class="role-tabs-wrapper" x-ref="tabsWrapper">
                        <div class="role-tab-slider"
                             :style="`transform: translateX(calc(${activeRoleIndex()} * 100% + ${activeRoleIndex()} * 4px))`">
                        </div>

                        <div class="relative z-10 flex">
                            <template x-for="(role, index) in roles" :key="role.id">
                                <button type="button"
                                        @click="switchRole(role.id)"
                                        class="role-tab-btn flex-1 py-2.5 sm:py-3 rounded-md text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer"
                                        :class="{ 'active': activeRole === role.id }">
                                        <i :class="role.icon"></i>
                                        <span x-text="role.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Form Title -->
                <div class="text-center lg:text-left mb-5 sm:mb-6 min-h-[44px]">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800 mb-1" x-text="'Login ' + getActiveRole().label"></h2>
                    <p class="text-slate-500 text-xs sm:text-sm" x-text="getActiveRole().description"></p>
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="mb-5 p-3 sm:p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs sm:text-sm flex items-start space-x-2"
                         x-data="{ show: true }"
                         x-show="show">
                        <i class="fas fa-exclamation-circle mt-0.5 text-red-500 flex-shrink-0"></i>
                        <div class="flex-1">
                            <span class="font-bold">Gagal:</span>
                            <span class="mt-0.5 block">{{ $errors->first() }}</span>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-700 flex-shrink-0 ml-2 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

               <!-- FORM AREA -->
                <div class="w-full">

                    <!-- ================= SISWA (OTOMATIS / SIPINTU) ================= -->
                    <div x-show="activeRole === 'student'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ loginId: '{{ old('email') }}' }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="siswa">

                            <!-- Input Email/NIS -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Email / NIS Siswa</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required placeholder="Masukkan NIS atau email siswa" class="form-input w-full h-11 sm:h-12 pl-10 pr-4 rounded-xl text-sm text-slate-700 focus:outline-none">
                                </div>
                            </div>

                            <!-- Input Password (Otomatis & Dikunci) -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Password (Terisi Otomatis)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" type="password" :value="loginId.split('@')[0]" readonly required placeholder="••••••••" class="form-input w-full h-11 sm:h-12 pl-10 pr-10 rounded-xl text-sm text-slate-500 bg-slate-100 cursor-not-allowed focus:outline-none border-slate-200">
                                </div>

                            </div>

                            <button type="submit" :disabled="loading" class="btn-submit w-full h-11 sm:h-12 mt-2 rounded-xl text-sm sm:text-base font-bold text-white flex items-center justify-center space-x-2 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- ================= GURU PIKET / ADMIN (OTOMATIS / SIPINTU) ================= -->
                    <div x-show="activeRole === 'teacher'" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ loginId: '{{ old('email') }}' }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="guru">

                            <!-- Input Email/NIP -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Email / NIP Guru</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" x-model="loginId" required placeholder="Masukkan NIP atau email guru" class="form-input w-full h-11 sm:h-12 pl-10 pr-4 rounded-xl text-sm text-slate-700 focus:outline-none">
                                </div>
                            </div>

                            <!-- Input Password (Otomatis & Dikunci) -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Password (Terisi Otomatis)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" type="password" :value="loginId.split('@')[0]" readonly required placeholder="••••••••" class="form-input w-full h-11 sm:h-12 pl-10 pr-10 rounded-xl text-sm text-slate-500 bg-slate-100 cursor-not-allowed focus:outline-none border-slate-200">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-primary-600 focus:outline-none transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>

                            </div>

                            <button type="submit" :disabled="loading" class="btn-submit w-full h-11 sm:h-12 mt-2 rounded-xl text-sm sm:text-base font-bold text-white flex items-center justify-center space-x-2 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- ================= SATPAM (MANUAL INPUT / DATABASE LOKAL) ================= -->
                    <div x-show="activeRole === 'security'" style="display: none;">
                        <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="role" value="satpam">

                            <!-- Input Email/ID -->
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Email / ID Satpam</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-envelope"></i></span>
                                    <input name="email" type="text" value="{{ old('email') }}" required placeholder="Masukkan ID atau email satpam" class="form-input w-full h-11 sm:h-12 pl-10 pr-4 rounded-xl text-sm text-slate-700 focus:outline-none">
                                </div>
                            </div>

                            <!-- Input Password (Bisa Diketik Manual) -->
                            <div x-data="{ showPw: false }">
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5 ml-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fas fa-lock"></i></span>
                                    <input name="password" :type="showPw ? 'text' : 'password'" required placeholder="••••••••" class="form-input w-full h-11 sm:h-12 pl-10 pr-10 rounded-xl text-sm text-slate-700 focus:outline-none">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-primary-600 focus:outline-none transition-colors">
                                        <i :class="showPw ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="btn-submit w-full h-11 sm:h-12 mt-2 rounded-xl text-sm sm:text-base font-bold text-white flex items-center justify-center space-x-2 disabled:opacity-70">
                                <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                                <span x-text="loading ? 'Memproses...' : 'Masuk ke Sistem'"></span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 text-center w-full text-xs text-slate-500 mt-6 px-4">
            <p class="font-medium tracking-wide">© 2026 DIDISPEN. All rights reserved.</p>
        </div>

    </div>

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
