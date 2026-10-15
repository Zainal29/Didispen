@php
    $user = auth()->user();

    $layout = match ($user->role) {
        'guru'   => 'guru.layouts.app',
        'satpam' => 'satpam.layouts.app',
        'admin'  => 'admin.layouts.app',
        default  => 'siswa.layouts.app',
    };
@endphp

@extends($layout)

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
@if($user->role === 'admin')
    @include('components.alert')
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-28 sm:pb-12">

    {{-- ========================================================= --}}
    {{-- KARTU IDENTITAS --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 h-fit lg:sticky lg:top-6 overflow-hidden min-w-0">

        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-sm mb-3">
                {{ strtoupper(substr($user->name ?? '-', 0, 1)) }}
            </div>

            <h3 class="font-bold text-slate-900 text-base sm:text-lg">
                {{ $user->name }}
            </h3>

            <span class="mt-2 px-3 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider border border-blue-100">
                {{ ucfirst($user->role) }}
            </span>
        </div>

        {{-- ID DINAMIS (NIS / NIP / ID) --}}
        @php
            $idLabel = match($user->role) {
                'siswa'  => 'NIS',
                'guru'   => 'NIP',
                'satpam' => 'ID Petugas',
                'admin'  => 'ID Admin',
                default  => 'ID'
            };
            $idValue = $user->nis_nip ?? '-';
        @endphp

        <dl class="text-xs sm:text-sm space-y-3 divide-y divide-slate-100">
            <div class="pt-3 flex justify-between items-center gap-3">
                <dt class="text-slate-500 font-medium">{{ $idLabel }}</dt>
                <dd class="font-mono font-semibold text-slate-800 text-right break-all">
                    {{ $idValue }}
                </dd>
            </div>

            {{-- EMAIL --}}
            <div class="pt-3 flex justify-between items-center gap-3">
                <dt class="text-slate-500 font-medium">Email Sekolah</dt>
                <dd class="text-slate-800 truncate max-w-[180px] text-right font-medium" title="{{ $user->email }}">
                    {{ $user->email ?? '-' }}
                </dd>
            </div>

            {{-- TERDAFTAR --}}
            <div class="pt-3 flex justify-between items-center gap-3">
                <dt class="text-slate-500 font-medium">Terdaftar Sejak</dt>
                <dd class="text-slate-800 font-medium">
                    {{ $user->created_at?->format('d M Y') ?? '-' }}
                </dd>
            </div>

            {{-- NOMOR TELEPON --}}
            @if($user->role === 'siswa' && $user->siswa)
                <div class="pt-3">
                    <dt class="text-slate-500 font-medium mb-1.5">No. Telepon / WA</dt>
                    <dd class="flex items-center justify-end">
                        @if($user->siswa->no_telepon)
                            <a href="tel:{{ $user->siswa->no_telepon }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors border border-emerald-200 text-xs font-bold break-all">
                                <i class="ti ti-phone flex-shrink-0 text-sm"></i>
                                <span>{{ str_replace('+62', '0', $user->siswa->no_telepon) }}</span>
                            </a>
                        @else
                            <span class="text-slate-400 italic text-xs">Belum diatur</span>
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-500 flex items-start gap-2">
            <i class="ti ti-info-circle text-slate-400 mt-0.5 flex-shrink-0 text-sm"></i>
            <span>Data identitas utama dikelola oleh sistem pusat sekolah.</span>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- KONTEN KANAN --}}
    {{-- ========================================================= --}}
    <div class="lg:col-span-2 space-y-6 min-w-0">

        {{-- ========================================================= --}}
        {{-- DATA KONTAK & TAMBAHAN SISWA --}}
        {{-- ========================================================= --}}
        @if($user->role === 'siswa' && $user->siswa)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 overflow-hidden">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="ti ti-user-edit text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Data Kontak & Tambahan</h3>
                        <p class="text-xs text-slate-500">Lengkapi data untuk kebutuhan administrasi permohonan dispensasi.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profil.update-additional') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 min-w-0">
                        {{-- NOMOR TELEPON --}}
                        <div class="md:col-span-2 min-w-0">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                <i class="ti ti-phone mr-1 text-emerald-600 text-sm"></i> No. Telepon / WhatsApp
                            </label>
                            <div class="flex w-full min-w-0 max-w-full overflow-hidden rounded-xl border border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                                <span class="inline-flex items-center px-3 sm:px-3.5 bg-slate-50 text-slate-600 font-bold text-xs sm:text-sm flex-shrink-0 whitespace-nowrap border-r border-slate-200">
                                    <i class="ti ti-world text-slate-400 mr-1 text-sm"></i> +62
                                </span>
                                <input type="tel" name="no_telepon" id="phone_input" inputmode="numeric"
                                       value="{{ old('no_telepon', preg_replace('/^\+62/', '', $user->siswa->no_telepon ?? '')) }}"
                                       placeholder="81234567890"
                                       class="w-full min-w-0 flex-1 px-3 sm:px-4 py-2.5 outline-none transition-all text-base sm:text-sm font-medium @error('no_telepon') bg-rose-50 @enderror">
                            </div>
                            @error('no_telepon')
                                <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i> {{ $message }}</p>
                            @else
                                <p class="text-slate-400 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-info-circle"></i> Masukkan nomor tanpa angka 0 di depan.</p>
                            @enderror
                        </div>

                        {{-- TANGGAL LAHIR --}}
                        <div class="min-w-0">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                <i class="ti ti-calendar mr-1 text-blue-600 text-sm"></i> Tanggal Lahir
                            </label>
                            <input type="date" name="tanggal_lahir"
                                   value="{{ old('tanggal_lahir', $user->siswa->tanggal_lahir ? \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->format('Y-m-d') : '') }}"
                                   max="{{ now()->subYears(7)->format('Y-m-d') }}"
                                   class="w-full min-w-0 px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 outline-none transition-all text-base sm:text-sm font-medium @error('tanggal_lahir') border-rose-500 bg-rose-50 @enderror">
                            @error('tanggal_lahir')
                                <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                            @else
                                <p class="text-slate-400 text-xs mt-1.5">Opsional. Usia minimal 7 tahun.</p>
                            @enderror
                        </div>

                        {{-- ALAMAT --}}
                        <div class="min-w-0">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                <i class="ti ti-map-pin mr-1 text-rose-500 text-sm"></i> Alamat Lengkap
                            </label>
                            <textarea name="alamat" rows="3" placeholder="RT/RW, Desa, Kecamatan..."
                                      class="w-full min-w-0 px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 outline-none transition-all text-base sm:text-sm font-medium resize-none @error('alamat') border-rose-500 bg-rose-50 @enderror">{{ old('alamat', $user->siswa->alamat) }}</textarea>
                            @error('alamat')
                                <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="w-full sm:w-auto min-h-[44px] px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                            <i class="ti ti-device-floppy text-base"></i>
                            <span>Simpan Data Profil</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- KEAMANAN AKUN                                             --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 overflow-hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                    <i class="ti ti-shield-check text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Keamanan Akun</h3>
                    <p class="text-xs text-slate-500">Pengaturan autentikasi dan sesi akun Anda.</p>
                </div>
            </div>

            @if($user->role === 'admin')
                {{-- FORM GANTI PASSWORD (HANYA UNTUK ADMIN) --}}
                <form method="POST" action="{{ route('profil.update-password') }}" class="mb-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                Password Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="input_admin_current_pw" name="current_password" required
                                       class="w-full pl-4 pr-11 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 outline-none text-base sm:text-sm @error('current_password') border-rose-500 bg-rose-50 @enderror"
                                       placeholder="Masukkan password lama Anda">
                                <button type="button" onclick="togglePasswordVisibility('input_admin_current_pw', this)"
                                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                    <i class="ti ti-alert-circle"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                Password Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="input_admin_new_pw" name="new_password" required minlength="8"
                                       class="w-full pl-4 pr-11 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 outline-none text-base sm:text-sm @error('new_password') border-rose-500 bg-rose-50 @enderror"
                                       placeholder="Minimal 8 karakter">
                                <button type="button" onclick="togglePasswordVisibility('input_admin_new_pw', this)"
                                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                    <i class="ti ti-alert-circle"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                Konfirmasi Password Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="input_admin_confirm_pw" name="new_password_confirmation" required minlength="8"
                                       class="w-full pl-4 pr-11 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 outline-none text-base sm:text-sm"
                                       placeholder="Ulangi password baru">
                                <button type="button" onclick="togglePasswordVisibility('input_admin_confirm_pw', this)"
                                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password">
                                    <i class="ti ti-eye text-base"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="w-full sm:w-auto min-h-[44px] px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                            <i class="ti ti-key text-base"></i>
                            <span>Perbarui Password</span>
                        </button>
                    </div>
                </form>

                <hr class="border-slate-100 my-6">
            @else
                {{-- PESAN INFORMATIF UNTUK SISWA, GURU, DAN SATPAM --}}
                <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100 text-xs sm:text-sm text-blue-900 leading-relaxed mb-6">
                    <div class="flex gap-3 items-start">
                        <i class="ti ti-info-circle text-blue-600 mt-0.5 flex-shrink-0 text-lg"></i>
                        <div>
                            <p class="font-bold mb-1">Password Dikelola oleh Administrator</p>
                            <p class="text-blue-700">
                                Untuk alasan keamanan, password akun Anda dikelola secara terpusat oleh pihak sekolah.
                                Jika Anda lupa password atau mengalami kendala login, silakan hubungi <strong>Admin Sekolah</strong> untuk bantuan reset akun.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========================================================= --}}
            {{-- FITUR LOGOUT PERANGKAT LAIN --}}
            {{-- ========================================================= --}}
            <div>
                <h4 class="text-sm font-bold text-slate-900 mb-1 flex items-center gap-2">
                    <i class="ti ti-device-laptop text-rose-500 text-base"></i> Keluarkan Perangkat Lain
                </h4>
                <p class="text-xs text-slate-500 mb-4">
                    Gunakan fitur ini jika Anda lupa logout di komputer lab atau ponsel lain. Masukkan password untuk konfirmasi.
                </p>

                <form method="POST" action="{{ route('profil.logout-other-devices') }}">
                    @csrf
                    <div class="max-w-md">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Konfirmasi Password Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="input_logout_password" name="current_password" required
                                   class="w-full pl-4 pr-11 py-2.5 border border-slate-300 rounded-xl focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 outline-none text-base sm:text-sm @error('current_password') border-rose-500 bg-rose-50 @enderror"
                                   placeholder="Masukkan password Anda">
                            <button type="button" onclick="togglePasswordVisibility('input_logout_password', this)"
                                    class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                                    title="Tampilkan / Sembunyikan Password">
                                <i class="ti ti-eye text-base"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @else
                            <p class="text-slate-400 text-xs mt-1.5 flex items-center gap-1">
                                <i class="ti ti-info-circle"></i> Sesi di perangkat/browser lain akan diakhiri seketika.
                            </p>
                        @enderror
                    </div>
                    <div class="mt-4">
                        <button type="submit"
                                class="w-full sm:w-auto min-h-[44px] px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                            <i class="ti ti-logout text-base"></i>
                            <span>Keluarkan Semua Perangkat Lain</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- INFORMASI PRIBADI --}}
        {{-- ========================================================= --}}
        @if($user->role === 'siswa' && $user->siswa)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 overflow-hidden">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="ti ti-calendar text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Informasi Tambahan Siswa</h3>
                        <p class="text-xs text-slate-500">Ringkasan usia dan data tanggal lahir.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    {{-- TANGGAL LAHIR --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                                <i class="ti ti-calendar text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tanggal Lahir</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5 truncate">
                                    @if($user->siswa->tanggal_lahir)
                                        {{ \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->translatedFormat('d F Y') }}
                                    @else
                                        <span class="text-slate-400 font-normal">Belum diisi</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- USIA --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                <i class="ti ti-calendar-user text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Usia Siswa</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5 truncate">
                                    @if($user->siswa->tanggal_lahir)
                                        {{ \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->age }} tahun
                                    @else
                                        <span class="text-slate-400 font-normal">-</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- LOG AKTIVITAS LOGIN --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="ti ti-history text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Log Aktivitas</h3>
                        <p class="text-xs text-slate-500">Riwayat sesi login akun Anda.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(isset($loginActivities) && $loginActivities->count() > 0)
                        <button type="button"
                                onclick="confirmClearAllLoginHistory()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 active:bg-rose-200 border border-rose-200 text-rose-700 text-xs font-bold transition-colors shadow-xs"
                                title="Bersihkan seluruh riwayat login agar tidak menumpuk">
                            <i class="ti ti-trash text-sm"></i>
                            <span>Bersihkan Riwayat</span>
                        </button>
                    @endif
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700">
                        <i class="ti ti-shield-check text-emerald-600"></i> Terpantau Aman
                    </span>
                </div>
            </div>

            @if(isset($loginActivities) && $loginActivities->count() > 0)
                @php
                    $latestLogin = $loginActivities->first();
                    $deviceChanged = $deviceChanged ?? false;
                @endphp

                @if($deviceChanged && $latestLogin)
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                        <div class="flex items-start gap-3">
                            <i class="ti ti-alert-triangle text-amber-600 mt-0.5 flex-shrink-0 text-lg"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-bold text-amber-900">Perangkat Login Berbeda</p>
                                <p class="text-xs text-amber-800 mt-1">
                                    Perangkat yang digunakan saat ini berbeda dari riwayat sebelumnya. Jika bukan Anda, segera ubah password dan hubungi administrator.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- KARTU LOGIN TERAKHIR --}}
                <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50/60 p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-blue-700">Terakhir Login</p>
                            <p class="mt-1 text-xs sm:text-base font-bold text-slate-900">
                                {{ $latestLogin->created_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB
                            </p>
                            <p class="text-[11px] text-blue-800 font-medium">
                                {{ $latestLogin->created_at?->diffForHumans() ?? 'Belum tercatat' }}
                            </p>

                            @php
                                $isSSOLastLogin = str_contains($latestLogin->action ?? '', 'sso') || str_contains($latestLogin->action ?? '', 'sipintu');
                            @endphp
                            @if($isSSOLastLogin)
                                <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                    <i class="ti ti-shield-check"></i> Login via SSO SiPintu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                    <i class="ti ti-key"></i> Login Manual
                                </span>
                            @endif
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Perangkat</p>
                            <p class="mt-1 text-xs font-bold text-slate-800">
                                {{ $latestLogin->device_type ?? 'Unknown' }} • {{ $latestLogin->os ?? 'Unknown' }}
                            </p>
                            <p class="text-[11px] text-slate-500">{{ $latestLogin->browser ?? 'Unknown Browser' }}</p>
                        </div>
                    </div>
                </div>

                {{-- DAFTAR RIWAYAT LOGIN --}}
                <div class="divide-y divide-slate-100">
                    @forelse($loginActivities as $login)
                        @php
                            $loginTime = $login->created_at;
                            $deviceType = $login->device_type ?? 'Unknown';
                            $deviceIcon = match ($deviceType) {
                                'HP' => 'ti-device-mobile',
                                'Tablet' => 'ti-device-tablet',
                                default => 'ti-device-laptop',
                            };
                            $isSSO = str_contains($login->action ?? '', 'sso') || str_contains($login->action ?? '', 'sipintu');
                        @endphp
                        <div class="py-3.5 first:pt-0 last:pb-0">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $loop->first ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center flex-shrink-0">
                                    <i class="ti {{ $deviceIcon }} text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                            <span class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $deviceType }} • {{ $login->os ?? 'Unknown OS' }}</span>

                                            @if($loop->first)
                                                <span class="px-2 py-0.2 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">Sesi Ini</span>
                                            @endif

                                            @if($isSSO)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <i class="ti ti-shield-check text-[10px]"></i> SSO
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                    <i class="ti ti-key text-[10px]"></i> Manual
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $loginTime?->diffForHumans() }}</span>
                                            <button type="button"
                                                    onclick="confirmDeleteSingleLogin({{ $login->id }})"
                                                    class="w-6 h-6 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 inline-flex items-center justify-center transition-colors text-xs"
                                                    title="Hapus riwayat login ini">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2 pt-2 border-t border-slate-100 text-slate-600 text-xs">
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Waktu</p>
                                            <p class="font-medium text-slate-700 flex items-center gap-1"><i class="ti ti-clock text-slate-400"></i> {{ $loginTime?->translatedFormat('H:i') ?? '-' }} WIB</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Tanggal</p>
                                            <p class="font-medium text-slate-700 flex items-center gap-1"><i class="ti ti-calendar text-slate-400"></i> {{ $loginTime?->translatedFormat('d M Y') ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Browser</p>
                                            <p class="font-medium text-slate-700 flex items-center gap-1"><i class="ti ti-world text-slate-400"></i> {{ $login->browser ?? 'Unknown' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">IP Address</p>
                                            <p class="font-mono text-slate-700 font-medium">{{ $login->ip_address ?? '-' }}</p>
                                        </div>
                                    </div>

                                    @if($login->user_agent)
                                        <details class="mt-2.5">
                                            <summary class="cursor-pointer text-[11px] text-slate-500 hover:text-blue-600 transition-colors inline-flex items-center gap-1">
                                                <i class="ti ti-code"></i> Detail User Agent
                                            </summary>
                                            <div class="mt-1.5 p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                                                <p class="text-[10px] text-slate-600 break-all leading-relaxed font-mono">{{ $login->user_agent }}</p>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center border border-dashed border-slate-200 rounded-xl">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                <i class="ti ti-history text-xl"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Belum ada aktivitas login</p>
                            <p class="text-xs text-slate-400 mt-1">Riwayat login akan muncul setelah Anda masuk ke sistem.</p>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="py-10 text-center border border-dashed border-slate-200 rounded-xl">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        <i class="ti ti-history text-xl"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum ada log aktivitas</p>
                    <p class="text-xs text-slate-400 mt-1">Aktivitas login Anda akan tercatat secara otomatis.</p>
                </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script>
const phoneInput = document.getElementById('phone_input');
if (phoneInput) {
    phoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.startsWith('0')) {
            value = value.substring(1);
        }
        if (value.startsWith('62')) {
            value = value.substring(2);
        }
        e.target.value = value;
    });
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.className = 'ti ti-eye-off text-base';
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.className = 'ti ti-eye text-base';
        }
    }
}

function confirmClearAllLoginHistory() {
    Swal.fire({
        title: 'Bersihkan Riwayat Login?',
        text: 'Semua catatan riwayat login Anda akan dihapus permanen agar tidak menumpuk dan menjaga performa sistem tetap lancar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="ti ti-trash mr-1"></i> Ya, Bersihkan Semua',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitProfileDeleteForm('{{ route('profil.clear-login-history') }}');
        }
    });
}

function confirmDeleteSingleLogin(id) {
    Swal.fire({
        title: 'Hapus Catatan Login?',
        text: 'Catatan login ini akan dihapus dari riwayat aktivitas Anda.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-slate-200',
            confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
            cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitProfileDeleteForm(`/profil/riwayat-login/${id}`);
        }
    });
}

function submitProfileDeleteForm(url) {
    Swal.fire({
        title: 'Menghapus...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
