
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

@include('components.alert')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ========================================================= --}}
    {{-- KARTU IDENTITAS --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit lg:sticky lg:top-6">

        <div class="flex flex-col items-center text-center mb-6">

            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-blue-500/30 mb-3">
                {{ strtoupper(substr($user->name ?? '-', 0, 1)) }}
            </div>

            <h3 class="font-bold text-gray-900 text-lg">
                {{ $user->name }}
            </h3>

            <span class="mt-1 px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider border border-blue-100">
                {{ $user->role }}
            </span>

        </div>


        <dl class="text-sm space-y-3 divide-y divide-gray-100">

            {{-- NIS / NIP --}}
            <div class="pt-2 flex justify-between items-center gap-3">

                <dt class="text-gray-500 font-medium">
                    NIS / NIP
                </dt>

                <dd class="font-mono font-bold text-gray-800 text-right">
                    {{ $user->nis_nip ?? '-' }}
                </dd>

            </div>


            {{-- EMAIL --}}
            <div class="pt-3 flex justify-between items-center gap-3">

                <dt class="text-gray-500 font-medium">
                    Email Sekolah
                </dt>

                <dd
                    class="text-gray-800 truncate max-w-[180px] text-right"
                    title="{{ $user->email }}"
                >
                    {{ $user->email ?? '-' }}
                </dd>

            </div>


            {{-- TERDAFTAR --}}
            <div class="pt-3 flex justify-between items-center gap-3">

                <dt class="text-gray-500 font-medium">
                    Terdaftar Sejak
                </dt>

                <dd class="text-gray-800">
                    {{ $user->created_at?->format('d M Y') ?? '-' }}
                </dd>

            </div>


            {{-- NOMOR TELEPON --}}
            @if($user->role === 'siswa' && $user->siswa)

                <div class="pt-3">

                    <dt class="text-gray-500 font-medium mb-1.5">
                        No. Telepon / WA
                    </dt>

                    <dd class="flex items-center justify-end">

                        @if($user->siswa->no_telepon)

                            <a
                                href="tel:{{ $user->siswa->no_telepon }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors border border-emerald-100"
                            >
                                <i class="fas fa-phone-alt text-xs"></i>

                                {{ str_replace('+62', '0', $user->siswa->no_telepon) }}
                            </a>

                        @else

                            <span class="text-gray-400 italic text-xs">
                                Belum tersedia
                            </span>

                        @endif

                    </dd>

                </div>

            @endif

        </dl>


        <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-gray-400 flex items-start gap-2">

            <i class="fas fa-info-circle mt-0.5"></i>

            <span>
                Data identitas utama dikelola oleh sistem pusat.
            </span>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- KONTEN KANAN --}}
    {{-- ========================================================= --}}
    <div class="lg:col-span-2 space-y-6">


        {{-- ========================================================= --}}
        {{-- DATA KONTAK & TAMBAHAN SISWA --}}
        {{-- ========================================================= --}}
        @if($user->role === 'siswa' && $user->siswa)

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fas fa-user-edit text-lg"></i>
                    </div>

                    <div>

                        <h3 class="font-bold text-gray-900">
                            Data Kontak & Tambahan
                        </h3>

                        <p class="text-xs text-gray-500">
                            Lengkapi data untuk kebutuhan administrasi dispensasi.
                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route('profil.update-additional') }}"
                >

                    @csrf


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        {{-- NOMOR TELEPON --}}
                        <div class="md:col-span-2">

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">

                                <i class="fas fa-phone-alt mr-1 text-emerald-600"></i>

                                No. Telepon / WhatsApp

                            </label>


                            <div class="flex">

                                <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 text-gray-600 font-semibold text-sm">

                                    <i class="fas fa-globe text-gray-500 mr-1.5"></i>

                                    +62

                                </span>


                                <input
                                    type="tel"
                                    name="no_telepon"
                                    id="phone_input"
                                    value="{{ old('no_telepon', preg_replace('/^\+62/', '', $user->siswa->no_telepon ?? '')) }}"
                                    placeholder="81234567890"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-r-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium @error('no_telepon') border-red-500 bg-red-50 @enderror"
                                >

                            </div>


                            @error('no_telepon')

                                <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">

                                    <i class="fas fa-exclamation-circle"></i>

                                    {{ $message }}

                                </p>

                            @else

                                <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">

                                    <i class="fas fa-info-circle"></i>

                                    Masukkan nomor tanpa angka 0 di depan.

                                </p>

                            @enderror

                        </div>


                        {{-- TANGGAL LAHIR --}}
                        <div>

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">

                                <i class="fas fa-calendar-alt mr-1 text-pink-500"></i>

                                Tanggal Lahir

                            </label>


                            <input
                                type="date"
                                name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $user->siswa->tanggal_lahir ? \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->format('Y-m-d') : '') }}"
                                max="{{ now()->subYears(7)->format('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium @error('tanggal_lahir') border-red-500 bg-red-50 @enderror"
                            >


                            @error('tanggal_lahir')

                                <p class="text-red-500 text-xs mt-1.5">
                                    {{ $message }}
                                </p>

                            @else

                                <p class="text-gray-400 text-xs mt-1.5">
                                    Opsional. Usia minimal 7 tahun.
                                </p>

                            @enderror

                        </div>


                        {{-- ALAMAT --}}
                        <div>

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">

                                <i class="fas fa-map-marker-alt mr-1 text-red-500"></i>

                                Alamat Lengkap

                            </label>


                            <textarea
                                name="alamat"
                                rows="3"
                                placeholder="RT/RW, Desa, Kecamatan..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium resize-none @error('alamat') border-red-500 bg-red-50 @enderror"
                            >{{ old('alamat', $user->siswa->alamat) }}</textarea>


                            @error('alamat')

                                <p class="text-red-500 text-xs mt-1.5">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>


                    <div class="mt-6 flex justify-end">

                        <button
                            type="submit"
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-[0.98] flex items-center gap-2"
                        >

                            <i class="fas fa-save"></i>

                            Simpan Data Profil

                        </button>

                    </div>

                </form>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- KEAMANAN AKUN --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            <div class="flex items-center gap-3 mb-5">

                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-lg"></i>
                </div>

                <div>

                    <h3 class="font-bold text-gray-900">
                        Keamanan Akun
                    </h3>

                    <p class="text-xs text-gray-500">
                        Kelola keamanan akun Anda.
                    </p>

                </div>

            </div>


            @if($user->role === 'admin')

                <form
                    method="POST"
                    action="{{ route('profil.update-password') }}"
                >

                    @csrf
                    @method('PUT')


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                        {{-- PASSWORD LAMA --}}
                        <div>

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Password Saat Ini
                            </label>

                            <input
                                type="password"
                                name="current_password"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 outline-none text-sm @error('current_password') border-red-500 bg-red-50 @enderror"
                                placeholder="Password lama"
                            >

                            @error('current_password')

                                <p class="text-red-500 text-xs mt-1.5">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- PASSWORD BARU --}}
                        <div>

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Password Baru
                            </label>

                            <input
                                type="password"
                                name="new_password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 outline-none text-sm @error('new_password') border-red-500 bg-red-50 @enderror"
                                placeholder="Minimal 8 karakter"
                            >

                            @error('new_password')

                                <p class="text-red-500 text-xs mt-1.5">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- KONFIRMASI --}}
                        <div>

                            <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Konfirmasi Password Baru
                            </label>

                            <input
                                type="password"
                                name="new_password_confirmation"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 outline-none text-sm"
                                placeholder="Ulangi password baru"
                            >

                        </div>

                    </div>


                    <div class="mt-6 flex justify-end">

                        <button
                            type="submit"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2"
                        >

                            <i class="fas fa-key"></i>

                            Ubah Password

                        </button>

                    </div>

                </form>

            @else

                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-sm text-gray-700 leading-relaxed">

                    <div class="flex gap-3">

                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>

                        <p>
                            Password akun Anda dikelola secara terpusat oleh sistem.
                            Password tidak dapat diubah melalui halaman profil.
                        </p>

                    </div>

                </div>

            @endif

        </div>


        {{-- ========================================================= --}}
        {{-- INFORMASI PRIBADI --}}
        {{-- ========================================================= --}}
        @if($user->role === 'siswa' && $user->siswa)

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-lg"></i>
                    </div>

                    <div>

                        <h3 class="font-bold text-gray-900">
                            Informasi Pribadi
                        </h3>

                        <p class="text-xs text-gray-500">
                            Informasi pribadi siswa.
                        </p>

                    </div>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                    {{-- TANGGAL LAHIR --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center">
                                <i class="fas fa-calendar-alt"></i>
                            </div>

                            <div>

                                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                    Tanggal Lahir
                                </p>

                                <p class="text-sm font-bold text-gray-800 mt-0.5">

                                    @if($user->siswa->tanggal_lahir)

                                        {{ \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->translatedFormat('d F Y') }}

                                    @else

                                        <span class="text-gray-400 font-normal">
                                            Belum diisi
                                        </span>

                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- USIA --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-user-clock"></i>
                            </div>

                            <div>

                                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                    Usia
                                </p>

                                <p class="text-sm font-bold text-gray-800 mt-0.5">

                                    @if($user->siswa->tanggal_lahir)

                                        {{ \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->age }}
                                        tahun

                                    @else

                                        <span class="text-gray-400 font-normal">
                                            -
                                        </span>

                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- LOG AKTIVITAS --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            <div class="flex items-center justify-between gap-4 mb-5">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-history text-lg"></i>
                    </div>

                    <div>

                        <h3 class="font-bold text-gray-900">
                            Log Aktivitas
                        </h3>

                        <p class="text-xs text-gray-500">
                            Riwayat login akun Anda.
                        </p>

                    </div>

                </div>


                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-100 text-[11px] font-semibold text-gray-500">

                    <i class="fas fa-clock"></i>

                    10 login terakhir

                </span>

            </div>


            {{-- PERINGATAN DEVICE BERUBAH --}}
            @if($deviceChanged)

                <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200">

                    <div class="flex gap-3">

                        <div class="w-9 h-9 shrink-0 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">

                            <i class="fas fa-triangle-exclamation"></i>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-amber-800">
                                Login dari perangkat berbeda
                            </p>

                            <p class="text-xs text-amber-700 mt-1">
                                Login terakhir terdeteksi menggunakan
                                perangkat, sistem operasi, atau browser
                                yang berbeda dari login sebelumnya.
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- LOGIN TERAKHIR --}}
            @if($latestLogin)

                <div class="mb-5 p-4 rounded-xl bg-indigo-50 border border-indigo-100">

                    <div class="flex items-center justify-between gap-3 mb-4">

                        <div class="flex items-center gap-2">

                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>

                            <span class="text-xs font-bold text-indigo-900 uppercase tracking-wide">
                                Login Terakhir
                            </span>

                        </div>

                        <span class="text-[11px] text-indigo-600">
                            {{ $latestLogin->created_at?->format('d M Y, H:i:s') ?? '-' }}
                        </span>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">


                        {{-- PERANGKAT --}}
                        <div class="bg-white rounded-lg border border-indigo-100 p-3">

                            <p class="text-[10px] font-bold uppercase text-gray-400 mb-1">
                                Perangkat
                            </p>

                            <p class="text-sm font-bold text-gray-800">

                                <i class="fas fa-desktop text-indigo-500 mr-1"></i>

                                {{ $latestLogin->device_type ?? 'Unknown' }}

                            </p>

                        </div>


                        {{-- OS --}}
                        <div class="bg-white rounded-lg border border-indigo-100 p-3">

                            <p class="text-[10px] font-bold uppercase text-gray-400 mb-1">
                                Sistem Operasi
                            </p>

                            <p class="text-sm font-bold text-gray-800">

                                <i class="fas fa-microchip text-indigo-500 mr-1"></i>

                                {{ $latestLogin->os ?? 'Unknown' }}

                            </p>

                        </div>


                        {{-- BROWSER --}}
                        <div class="bg-white rounded-lg border border-indigo-100 p-3">

                            <p class="text-[10px] font-bold uppercase text-gray-400 mb-1">
                                Browser
                            </p>

                            <p class="text-sm font-bold text-gray-800">

                                <i class="fas fa-globe text-indigo-500 mr-1"></i>

                                {{ $latestLogin->browser ?? 'Unknown' }}

                            </p>

                        </div>


                        {{-- IP --}}
                        <div class="bg-white rounded-lg border border-indigo-100 p-3">

                            <p class="text-[10px] font-bold uppercase text-gray-400 mb-1">
                                IP Address
                            </p>

                            <p class="text-sm font-bold text-gray-800 font-mono">
                                {{ $latestLogin->ip_address ?? '-' }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- RIWAYAT --}}
                <div class="space-y-3">

                    @forelse($loginHistory as $index => $login)

                        <div class="p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">

                            <div class="flex items-start gap-4">


                                <div class="w-9 h-9 shrink-0 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold">

                                    {{ $index + 1 }}

                                </div>


                                <div class="flex-1 min-w-0">


                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 mb-3">

                                        <div class="flex items-center gap-2">

                                            <i class="fas fa-right-to-bracket text-emerald-500 text-sm"></i>

                                            <span class="text-sm font-bold text-gray-800">
                                                Berhasil Login
                                            </span>


                                            @if($index === 0)

                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 text-[9px] font-bold uppercase">
                                                    Terakhir
                                                </span>

                                            @endif

                                        </div>


                                        <span class="text-[11px] text-gray-400">
                                            {{ $login->created_at?->format('d M Y, H:i:s') ?? '-' }}
                                        </span>

                                    </div>


                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">


                                        <div>

                                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">
                                                Perangkat
                                            </p>

                                            <p class="text-xs font-semibold text-gray-700">

                                                <i class="fas fa-desktop text-gray-400 mr-1"></i>

                                                {{ $login->device_type ?? 'Unknown' }}

                                            </p>

                                        </div>


                                        <div>

                                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">
                                                OS
                                            </p>

                                            <p class="text-xs font-semibold text-gray-700">

                                                <i class="fas fa-microchip text-gray-400 mr-1"></i>

                                                {{ $login->os ?? 'Unknown' }}

                                            </p>

                                        </div>


                                        <div>

                                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">
                                                Browser
                                            </p>

                                            <p class="text-xs font-semibold text-gray-700">

                                                <i class="fas fa-globe text-gray-400 mr-1"></i>

                                                {{ $login->browser ?? 'Unknown' }}

                                            </p>

                                        </div>


                                        <div>

                                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">
                                                IP Address
                                            </p>

                                            <p class="text-xs font-semibold text-gray-700 font-mono">
                                                {{ $login->ip_address ?? '-' }}
                                            </p>

                                        </div>

                                    </div>


                                    @if($login->user_agent)

                                        <details class="mt-3">

                                            <summary class="cursor-pointer text-[11px] text-gray-400 hover:text-indigo-600">

                                                <i class="fas fa-code mr-1"></i>

                                                Detail User Agent

                                            </summary>


                                            <div class="mt-2 p-3 rounded-lg bg-gray-50 border border-gray-100">

                                                <p class="text-[10px] text-gray-500 break-all leading-relaxed font-mono">
                                                    {{ $login->user_agent }}
                                                </p>

                                            </div>

                                        </details>

                                    @endif

                                </div>

                            </div>

                        </div>


                    @empty

                        <div class="py-10 text-center border border-dashed border-gray-200 rounded-xl">

                            <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">

                                <i class="fas fa-history text-lg"></i>

                            </div>

                            <p class="text-sm font-semibold text-gray-600">
                                Belum ada aktivitas login
                            </p>

                            <p class="text-xs text-gray-400 mt-1">
                                Riwayat login akan muncul setelah Anda masuk ke sistem.
                            </p>

                        </div>

                    @endforelse

                </div>


            @else

                <div class="py-10 text-center border border-dashed border-gray-200 rounded-xl">

                    <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">

                        <i class="fas fa-clock-rotate-left text-lg"></i>

                    </div>

                    <p class="text-sm font-semibold text-gray-600">
                        Belum ada log aktivitas
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        Aktivitas login Anda akan tercatat secara otomatis.
                    </p>

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

</script>

@endpush

@endsection
