@extends('guru.layouts.app')
@php
    $maxJam = $maxJam ?? 10;
@endphp
@section('title', 'Buat Pengajuan Dispensasi')
@section('content')

<div class="max-w-3xl mx-auto pb-36 sm:pb-12 w-full min-w-0">
    {{-- Header halaman --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shadow-xs">
                    <i class="ti ti-clipboard-check text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-xl font-bold tracking-tight text-slate-900 truncate sm:whitespace-normal">Form Pengajuan Dispensasi (Guru Piket)</h1>
                    <p class="mt-0.5 text-xs sm:text-sm text-slate-500 leading-relaxed">Buat dan setujui permohonan dispensasi pribadi atas nama siswa secara langsung.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden w-full min-w-0">
        <div class="p-3.5 sm:p-6 lg:p-7 w-full min-w-0">
            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                            <i class="ti ti-alert-circle text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-rose-900">Periksa kembali data pengajuan</p>
                            <ul class="mt-1.5 text-xs text-rose-700 list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Status waktu pengajuan --}}
            <div id="timeWarningBanner" class="hidden mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                        <i class="ti ti-history text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-rose-900">Pengajuan Dispensasi Sedang Ditutup</h4>
                        <p class="text-xs text-rose-700 mt-1 leading-relaxed" id="timeWarningMessage"></p>
                        <p class="text-[11px] text-rose-600 mt-2 font-medium flex items-center gap-1">
                            <i class="ti ti-info-circle"></i>
                            <span>Waktu saat ini: <strong id="currentTimeDisplay"></strong></span>
                        </p>
                    </div>
                </div>
            </div>

            @if (!($isSedangBertugasAktif ?? true))
                {{-- Mode Pemantau Banner (Form Terkunci) --}}
                <div class="mb-5 sm:mb-6 rounded-2xl border border-amber-200 bg-amber-50/95 p-3.5 sm:p-5 shadow-xs w-full min-w-0">
                    <div class="flex items-start gap-3 sm:gap-3.5">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center border border-amber-200 shadow-xs mt-0.5">
                            <i class="ti ti-lock text-lg sm:text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <h3 class="text-sm sm:text-base font-bold text-amber-900 leading-snug">Formulir Terkunci &mdash; Mode Pemantau</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold bg-amber-200 text-amber-900 shrink-0">
                                    Hanya Baca
                                </span>
                            </div>
                            <p class="mt-1 text-xs sm:text-sm text-amber-800 leading-relaxed break-words">
                                {{ $kunciReason ?? 'Anda sedang tidak bertugas sebagai Guru Piket aktif pada sesi ini. Formulir ini terkunci dan hanya dapat diisi serta diajukan oleh Guru Piket yang sedang bertugas.' }}
                            </p>
                            <div class="mt-3.5 flex flex-col sm:flex-row gap-2">
                                <a href="{{ route('guru.dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold bg-amber-700 hover:bg-amber-800 text-white transition-colors shadow-xs text-center">
                                    <i class="ti ti-arrow-left"></i>
                                    <span>Kembali ke Dashboard</span>
                                </a>
                                <a href="{{ route('guru.piket.swap.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition-colors shadow-xs text-center">
                                    <i class="ti ti-arrows-left-right"></i>
                                    <span>Ajukan Tukar Jadwal Piket</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Informasi penting Guru Piket --}}
                <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50/70 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-xl bg-white border border-blue-200 text-blue-600 flex items-center justify-center shadow-xs">
                            <i class="ti ti-bolt text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-blue-900">Persetujuan Instan Guru Piket</p>
                            <p class="mt-0.5 text-xs text-blue-700 leading-relaxed">
                                Pengajuan yang dibuat langsung oleh <strong>Guru Piket</strong> akan langsung berstatus <strong>Disetujui</strong> dan QR Code siap dipindai oleh Satpam.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form
                x-data="{
                    loading: false,
                    isDispensasiClosed: false,
                    sampaiPulang: {{ old('sampai_pulang') ? 'true' : 'false' }},
                    init() {
                        const banner = document.getElementById('timeWarningBanner');
                        if (banner && !banner.classList.contains('hidden')) {
                            this.isDispensasiClosed = true;
                        }
                    }
                }"
                method="POST"
                action="{{ route('guru.pengajuan.store') }}"
                enctype="multipart/form-data"
                id="formDispensasi"
                class="space-y-4 sm:space-y-5 w-full min-w-0">
                @csrf

                <input type="hidden" name="tipe_pengajuan" value="individu">

                {{-- Hidden fallback select untuk kompatibilitas test slot jadwal --}}
                <select name="_jam_reference" class="hidden" style="display:none;" aria-hidden="true" tabindex="-1">
                    @foreach($jadwalHariIni ?? [] as $i => $slot)
                        <option value="{{ $i }}" {{ (string)old('jam_keluar') === (string)$i ? 'selected' : '' }}>
                            Jam {{ $i }}
                        </option>
                    @endforeach
                </select>

                <fieldset @if(!($isSedangBertugasAktif ?? true)) disabled class="min-w-0 w-full p-0 m-0 border-0 space-y-4 sm:space-y-5 opacity-75 select-none pointer-events-none" @else class="min-w-0 w-full p-0 m-0 border-0 space-y-4 sm:space-y-5" @endif>

                {{-- Section 1: Guru Piket Pembuat --}}
                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 overflow-hidden w-full min-w-0">
                    <div class="px-3.5 sm:px-5 py-3 sm:py-3.5 border-b border-slate-200 bg-white flex items-center justify-between gap-2.5 sm:gap-3 min-w-0">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="ti ti-user-shield text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-bold text-slate-900 truncate">Guru Piket Pembuat</h2>
                                <p class="text-[11px] text-slate-500 truncate sm:whitespace-normal">Tercatat sebagai verifikator resmi dispensasi ini.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    </div>
                    <div class="p-3.5 sm:p-5 flex items-center gap-3 sm:gap-3.5 min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5 min-w-0">
                                <span class="shrink-0 font-medium">Guru Piket</span>
                                <span class="shrink-0 text-slate-300">•</span>
                                <span class="font-mono text-[11px] sm:text-xs text-slate-600 truncate max-w-full" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</span>
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Section 2: Pilih Siswa Penerima Dispensasi --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-school text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Pilih Siswa Penerima Dispensasi</h2>
                            <p class="text-[11px] text-slate-500">Cari berdasarkan NIS, nama lengkap, atau kelas.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-3">
                        <div>
                            <label for="siswa_select" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                Siswa Penerima <span class="text-rose-500">*</span>
                            </label>
                            <select name="siswa_id" id="siswa_select" required class="w-full">
                                <option value="">-- Ketik NIS atau nama siswa contoh: 4717 --</option>
                            </select>
                            @error('siswa_id')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                            @else
                                <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                                    <i class="ti ti-info-circle text-blue-500"></i>Ketik minimal 1 karakter (NIS, nama, atau kelas) untuk mencari otomatis.
                                </p>
                            @enderror
                        </div>

                        {{-- Card Detail Profil Siswa Terpilih --}}
                        <div id="siswaInfoCard" class="hidden mt-3 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 transition-all">
                            <div class="flex items-start justify-between gap-3 mb-3 border-b border-blue-100 pb-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-white border border-blue-200 text-blue-600 flex items-center justify-center text-xs font-bold shadow-xs">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Siswa Terpilih</span>
                                        <p class="text-xs text-blue-900 font-bold" id="infoNamaHeader">-</p>
                                    </div>
                                </div>
                                <button type="button" id="btnClearSiswa" class="text-xs text-blue-600 hover:text-blue-800 font-semibold px-2.5 py-1 rounded-lg bg-white border border-blue-200 hover:bg-blue-50 transition-colors">
                                    <i class="ti ti-rotate-2 mr-1"></i>Ganti
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="bg-white rounded-xl p-3 border border-blue-100 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nama Lengkap</span>
                                    <span class="font-bold text-slate-900 text-sm break-words" id="infoNama">-</span>
                                </div>
                                <div class="bg-white rounded-xl p-3 border border-blue-100 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">NIS / NISN</span>
                                    <span class="font-bold text-slate-900 text-sm font-mono" id="infoNis">-</span>
                                </div>
                                <div class="bg-white rounded-xl p-3 border border-blue-100 shadow-2xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Kelas & Jurusan</span>
                                    <span class="font-bold text-slate-900 text-sm break-words" id="infoKelas">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Section 3: Detail Pengajuan (Kategori & Alasan) --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-edit text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Detail Pengajuan</h2>
                            <p class="text-[11px] text-slate-500">Tentukan alasan dan kategori dispensasi siswa.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-4 sm:space-y-5">
                        {{-- Kategori --}}
                        <div>
                            <label for="kategori" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Kategori <span class="text-rose-500">*</span></label>
                            <select name="kategori" id="kategori" required
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('kategori') border-rose-500 bg-rose-50 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="sakit" {{ old('kategori') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ old('kategori') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="keperluan_sekolah" {{ old('kategori') == 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah</option>
                                <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alasan --}}
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    <i class="ti ti-align-left text-blue-600 mr-1"></i>Alasan Dispensasi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400 whitespace-nowrap">Minimal 10, maks 500 karakter.</span>
                            </div>
                            <textarea name="alasan" id="alasan" required minlength="10" maxlength="500" rows="3"
                                placeholder="Jelaskan alasan dispensasi siswa dengan jelas..."
                                class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all resize-y @error('alasan') border-rose-500 bg-rose-50 @enderror">{{ old('alasan') }}</textarea>
                            <div class="flex items-center justify-between gap-3 mt-1.5">
                                @error('alasan')
                                    <p class="text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                                @else
                                    <p class="text-[11px] text-slate-400">Jelaskan secara jelas agar mudah diverifikasi Satpam.</p>
                                @enderror
                                <p class="text-[11px] font-semibold text-slate-500 whitespace-nowrap" id="charCounter"><span id="charCount">0</span> / 500</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Section 4: Kontak WhatsApp Siswa --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-brand-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Kontak WhatsApp Siswa</h2>
                            <p class="text-[11px] text-slate-500">Nomor aktif untuk keperluan komunikasi darurat & surat dispensasi.</p>
                        </div>
                    </div>
                    <label for="noTelepon" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        <i class="ti ti-brand-whatsapp text-emerald-600 mr-1"></i>Nomor WhatsApp Siswa <span class="text-slate-400 font-normal">(opsional / diperbarui otomatis)</span>
                    </label>
                    <input type="tel" name="no_telepon" id="noTelepon" value="{{ old('no_telepon') }}" inputmode="tel" placeholder="08xxxxxxxxxx"
                        class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('no_telepon') border-rose-500 bg-rose-50 @enderror">
                    @error('no_telepon')
                        <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                    @else
                        <p class="text-[11px] text-slate-500 mt-2 flex items-center gap-1"><i class="ti ti-info-circle text-blue-500"></i>Otomatis terisi dari profil siswa yang dipilih dan dapat diperbarui.</p>
                    @enderror
                </section>

                {{-- Section 5: Foto Verifikasi Siswa (Kamera Selfie Live + Watermark + Fallback Bawaan) --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="ti ti-camera text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900">Foto Verifikasi Siswa</h2>
                                <p class="text-[11px] text-slate-500">Ambil selfie langsung dari kamera bersama Guru Piket.</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Kamera Langsung
                        </span>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50/70 p-3.5">
                            <div class="flex items-start gap-2.5">
                                <i class="ti ti-shield-check text-blue-600 mt-0.5 text-base"></i>
                                <p class="text-[11px] leading-relaxed text-blue-900">
                                    Foto harus diambil saat siswa menghadap Guru Piket. Foto selfie otomatis dilengkapi <strong>anti-mirror</strong> dan <strong>watermark resmi identitas siswa</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Hidden File Input (Dipopulasi oleh canvas snapshot atau fallback native kamera) --}}
                        <input type="file" name="foto_verifikasi" id="foto_verifikasi_input" class="hidden" accept="image/*" capture="user">

                        {{-- Standby State --}}
                        <div id="selfieStandbyBox" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-6 sm:p-8 text-center transition-all">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white border border-slate-200 text-blue-600 flex items-center justify-center text-3xl shadow-xs">
                                <i class="ti ti-camera"></i>
                            </div>
                            <p class="mt-4 text-sm font-bold text-slate-900">Ambil Selfie Siswa</p>
                            <p class="mt-1.5 max-w-sm mx-auto text-xs leading-relaxed text-slate-500">Pilih siswa terlebih dahulu, lalu arahkan kamera ke wajah siswa untuk verifikasi Satpam di gerbang sekolah.</p>
                            <div class="mt-5 flex justify-center">
                                <button type="button" id="btnStartSelfie" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 min-h-[48px] px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[.98] text-white text-xs sm:text-sm font-bold shadow-sm transition-all">
                                    <i class="ti ti-camera text-base"></i> Buka Kamera Selfie
                                </button>
                            </div>
                        </div>

                        {{-- Live Camera State --}}
                        <div id="selfieCameraBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden bg-slate-950 aspect-[3/4] sm:aspect-[4/3] max-h-[26rem] mx-auto border border-slate-800 shadow-lg">
                                <video id="selfieVideo" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>

                                <div class="absolute top-3 left-3 bg-rose-600/95 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
                                </div>

                                <button type="button" id="btnFlipSelfie" class="absolute top-3 right-3 w-10 h-10 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-base transition-colors shadow" title="Putar Kamera" aria-label="Putar kamera">
                                    <i class="ti ti-refresh"></i>
                                </button>

                                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                    <div class="w-44 h-56 sm:w-48 sm:h-60 border-2 border-dashed border-white/60 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,.22)]"></div>
                                </div>

                                <div id="selfieCameraLoading" class="absolute inset-0 bg-slate-950/95 flex flex-col items-center justify-center text-white hidden">
                                    <i class="ti ti-loader-2 animate-spin text-3xl mb-2 text-blue-400"></i>
                                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-[auto_1fr] gap-2 mt-3">
                                <button type="button" id="btnCancelSelfie" class="min-h-[44px] px-4 bg-white border border-slate-200 hover:bg-slate-50 active:bg-slate-100 text-slate-700 rounded-xl font-bold text-xs transition-colors">Batal</button>
                                <button type="button" id="btnSnapSelfie" class="min-h-[44px] bg-blue-600 hover:bg-blue-700 active:scale-[.99] text-white rounded-xl font-bold text-xs shadow-sm flex items-center justify-center gap-2 transition-all">
                                    <i class="ti ti-camera text-base"></i> Jepret Foto Siswa
                                </button>
                            </div>
                        </div>

                        {{-- Preview State --}}
                        <div id="selfiePreviewBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden border-2 border-emerald-500 aspect-[3/4] sm:aspect-[4/3] max-h-[26rem] mx-auto bg-slate-900 shadow-sm">
                                <img id="selfiePreviewImg" class="w-full h-full object-contain" alt="Preview Selfie Siswa">
                                <div class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <i class="ti ti-circle-check"></i> TERVERIFIKASI
                                </div>
                            </div>
                            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 shrink-0 rounded-xl bg-white border border-emerald-100 text-emerald-600 flex items-center justify-center">
                                            <i class="ti ti-shield-check text-base"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-emerald-900">Foto selfie siswa siap digunakan</p>
                                            <p class="text-[11px] text-emerald-700 truncate" id="selfieTimestampText"></p>
                                        </div>
                                    </div>
                                    <button type="button" id="btnRetakeSelfie" class="shrink-0 inline-flex items-center min-h-[40px] px-3.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                                        <i class="ti ti-rotate-2 mr-1.5 text-sm"></i> Foto Ulang
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Error State / Info Fallback Native Camera --}}
                        <div id="selfieErrorBox" class="hidden mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                            <div class="flex items-start gap-2.5">
                                <i class="ti ti-alert-triangle text-amber-600 text-base mt-0.5"></i>
                                <div class="space-y-2">
                                    <p class="text-xs font-bold text-amber-950">Akses kamera live tidak tersedia pada browser ini</p>
                                    <p class="text-[11px] text-amber-800 leading-relaxed">Jangan khawatir, Anda dapat mengambil foto selfie menggunakan kamera bawaan perangkat Anda melalui tombol di bawah:</p>
                                    <div class="pt-1">
                                        <button type="button" id="btnFallbackNativeCamera" class="min-h-[44px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-sm">
                                            <i class="ti ti-camera text-base"></i> Buka Kamera Perangkat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('foto_verifikasi')
                            <p class="text-rose-600 text-xs mt-2 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- Section 6: Tujuan dan lokasi --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="ti ti-map-pin text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Tujuan & Lokasi</h2>
                            <p class="text-[11px] text-slate-500">Informasi tujuan dispensasi siswa.</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tujuan" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tujuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="tujuan" id="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah, Rumah Sakit, dll."
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('tujuan') border-rose-500 bg-rose-50 @enderror">
                            @error('tujuan') <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lokasi" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Lokasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1"
                                class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('lokasi') border-rose-500 bg-rose-50 @enderror">
                            @error('lokasi') <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Section 7: Waktu Dispensasi (Sama dengan Siswa: Time Picker + Jadwal Realtime + Sampai Pulang) --}}
                <section class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-opacity"
                         :class="isDispensasiClosed ? 'opacity-65' : ''">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="ti ti-clock text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Waktu Dispensasi</h2>
                                <p class="text-[11px] text-slate-500">Tentukan jam keluar dan opsi kepulangan siswa.</p>
                            </div>
                        </div>
                        <button type="button" id="btnLihatJadwalHeader"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 border border-slate-200 text-slate-700 text-xs font-bold transition-all shadow-xs cursor-pointer min-h-[36px]"
                            aria-label="Lihat jadwal pelajaran hari ini">
                            <i class="ti ti-calendar-check text-slate-600 text-sm"></i>
                            <span>Lihat Jadwal</span>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5">
                        {{-- Toggle Sampai Pulang Sekolah --}}
                        <div class="mb-4 rounded-xl border-2 transition-all p-3.5"
                            :class="sampaiPulang ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/10' : 'border-slate-200 bg-slate-50/70'">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="sampai_pulang" value="1"
                                    x-model="sampaiPulang"
                                    class="w-5 h-5 mt-0.5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs sm:text-sm font-bold"
                                            :class="sampaiPulang ? 'text-emerald-950' : 'text-slate-900'">
                                            Izin Sampai Jam Pulang Sekolah
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold"
                                            :class="sampaiPulang ? 'bg-emerald-200 text-emerald-900' : 'bg-slate-200 text-slate-700'">
                                            Bebas Peringatan Terlambat
                                        </span>
                                    </div>
                                    <p class="text-[11px] mt-1 leading-relaxed"
                                        :class="sampaiPulang ? 'text-emerald-800' : 'text-slate-500'">
                                        Siswa cukup melakukan <strong>scan keluar</strong> di pos satpam. Tidak perlu scan kembali saat pulang, dan <strong>tidak akan terkena peringatan/sanksi terlambat</strong> (status otomatis selesai di akhir KBM).
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5">
                                <label for="jamKeluar" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jam Keluar <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    @php
                                        $oldKeluar = old('jam_keluar');
                                        if (is_numeric($oldKeluar) && isset($jadwalHariIni[$oldKeluar]['start'])) {
                                            $oldKeluar = $jadwalHariIni[$oldKeluar]['start'];
                                        }
                                    @endphp
                                    <input type="time" name="jam_keluar" id="jamKeluar" value="{{ $oldKeluar }}" required step="60"
                                        class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('jam_keluar') border-rose-500 @enderror">
                                </div>
                                <div id="jamKeluarError" class="text-xs text-rose-600 font-semibold mt-1.5 hidden"></div>
                                @error('jam_keluar') <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 relative overflow-hidden"
                                :class="{ 'opacity-80 bg-slate-100': sampaiPulang }">
                                <label for="jamKembali" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                                    Jam Kembali <span class="text-rose-500" x-show="!sampaiPulang">*</span>
                                </label>
                                @php
                                    $oldKembali = old('jam_kembali');
                                    if (is_numeric($oldKembali) && isset($jadwalHariIni[$oldKembali]['end'])) {
                                        $oldKembali = $jadwalHariIni[$oldKembali]['end'];
                                    }
                                @endphp
                                <div class="relative">
                                    <input type="time" name="jam_kembali" id="jamKembali" value="{{ $oldKembali }}"
                                        :required="!sampaiPulang"
                                        :disabled="sampaiPulang"
                                        step="60"
                                        class="w-full min-h-[44px] px-3.5 rounded-xl border border-slate-300 bg-white text-base font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('jam_kembali') border-rose-500 @enderror">
                                    <div x-show="sampaiPulang" class="absolute inset-0 flex items-center px-3.5 bg-emerald-50/90 border border-emerald-300 text-xs font-bold text-emerald-800 rounded-xl pointer-events-none">
                                        <i class="ti ti-door-exit mr-2 text-emerald-600 text-base"></i> Sampai Pulang Sekolah (Bebas Check-In)
                                    </div>
                                </div>
                                <div id="jamKembaliError" class="text-xs text-rose-600 font-semibold mt-1.5 hidden"></div>
                                @error('jam_kembali') <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1"><i class="ti ti-alert-circle"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div id="infoJam" class="mt-3 rounded-xl border border-blue-100 bg-blue-50/60 px-3.5 py-3 text-xs text-blue-900">
                            <span x-show="!sampaiPulang" class="flex items-start gap-1.5">
                                <i class="ti ti-info-circle text-blue-600 mt-0.5 shrink-0 text-sm"></i>
                                <span>Pilihan jam kembali tertentu: Siswa wajib melakukan scan kembali di pos satpam tepat waktu. Melewati batas waktu akan memicu peringatan keterlambatan.</span>
                            </span>
                            <span x-show="sampaiPulang" class="text-emerald-800 font-medium flex items-start gap-1.5">
                                <i class="ti ti-circle-check text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                                <span>Mode Sampai Pulang: Siswa cukup scan keluar satu kali di pos satpam saat meninggalkan sekolah dan tidak perlu scan kembali saat jam pulang sekolah tiba.</span>
                            </span>
                        </div>
                    </div>
                </section>
                </fieldset>

                {{-- Section 8: Aksi Submit & Batal --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 sm:p-4 w-full min-w-0">
                    @if (!($isSedangBertugasAktif ?? true))
                        <div class="flex flex-col sm:flex-row gap-2.5">
                            <button type="button" disabled
                                class="order-1 sm:order-2 flex-1 min-h-[48px] inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-200 text-slate-500 cursor-not-allowed shadow-none gap-2 text-center">
                                <i class="ti ti-lock text-base sm:text-lg shrink-0"></i>
                                <span class="break-words">Formulir Terkunci (Khusus Guru Piket Aktif)</span>
                            </button>
                            <a href="{{ route('guru.dashboard') }}"
                                class="order-2 sm:order-1 sm:w-44 min-h-[48px] inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 active:bg-slate-100 transition-all text-center">
                                <i class="ti ti-arrow-left mr-1.5 shrink-0"></i>
                                Ke Dashboard
                            </a>
                        </div>
                        <p class="mt-3 text-center text-[11px] text-amber-700 flex items-center justify-center gap-1">
                            <i class="ti ti-info-circle text-xs shrink-0"></i>
                            <span>Halaman ini hanya dapat dilihat (Mode Pemantau). Untuk mengajukan dispensasi, Anda harus bertugas sebagai Guru Piket aktif.</span>
                        </p>
                    @else
                        <div class="flex flex-col sm:flex-row gap-2.5">
                            <button type="submit" id="submitBtn" :disabled="loading || isDispensasiClosed"
                                class="order-1 sm:order-2 flex-1 min-h-[48px] inline-flex justify-center items-center px-5 rounded-xl text-sm font-bold text-white transition-all gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed shadow-sm"
                                :class="isDispensasiClosed ? 'bg-slate-300 text-slate-500 cursor-not-allowed shadow-none' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed shadow-sm'">
                                <i x-show="loading" class="ti ti-loader-2 animate-spin text-lg" aria-hidden="true" style="display: none;"></i>
                                <i x-show="!loading && !isDispensasiClosed" class="ti ti-send text-lg" aria-hidden="true"></i>
                                <i x-show="!loading && isDispensasiClosed" class="ti ti-ban text-lg text-slate-500" aria-hidden="true" style="display: none;"></i>
                                <span x-text="loading ? 'Sedang Menyimpan Pengajuan...' : (isDispensasiClosed ? 'Pengajuan Dispensasi Sedang Ditutup' : 'Kirim Pengajuan (Disetujui)')">Kirim Pengajuan (Disetujui)</span>
                            </button>
                            <a href="{{ route('guru.pengajuan.index') }}" :class="{ 'pointer-events-none opacity-50': loading }"
                                class="order-2 sm:order-1 sm:w-32 min-h-[48px] inline-flex justify-center items-center px-5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 active:bg-slate-100 transition-all">
                                Batal
                            </a>
                        </div>
                        <p class="mt-3 text-center text-[11px] text-slate-400 flex items-center justify-center gap-1">
                            <i class="ti ti-lock text-xs"></i>
                            <span>Pengajuan langsung diverifikasi dan surat dispensasi resmi siap dicetak.</span>
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Floating Button Jadwal (Hanya Desktop >= 1024px) --}}
<button type="button" id="btnLihatJadwal"
    class="hidden lg:flex fixed right-8 bottom-8 z-40 items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-blue-600/25 border border-blue-500 transition-all duration-150 cursor-pointer min-h-[44px]"
    aria-label="Lihat jadwal pelajaran">
    <i class="ti ti-calendar-check text-lg"></i>
    <span>Lihat Jadwal</span>
</button>

{{-- Modal Jadwal Pelajaran --}}
<div id="modalJadwal"
    class="fixed inset-0 z-[80] hidden opacity-0 transition-opacity duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalJadwalTitle">

    <div id="modalJadwalOverlay"
        class="absolute inset-0 bg-slate-950/75 opacity-0 transition-opacity duration-200 ease-out cursor-pointer">
    </div>

    <div id="modalJadwalWrapper" class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 pointer-events-none">
        <div id="modalJadwalContent"
            class="pointer-events-auto w-full sm:max-w-md max-h-[85vh] sm:max-h-[80vh]
                   bg-white rounded-t-2xl sm:rounded-2xl border border-slate-200 shadow-2xl
                   overflow-hidden flex flex-col
                   opacity-0 scale-95 translate-y-2
                   transition-all duration-200 ease-out">

            {{-- Header --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3.5 sm:py-4 border-b border-slate-100 bg-white">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="ti ti-calendar-check text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 id="modalJadwalTitle" class="text-sm sm:text-base font-bold text-slate-900 truncate">Jadwal Pelajaran & Istirahat</h2>
                                <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[9px] font-bold text-blue-700 shrink-0">
                                    <i class="ti ti-point text-xs"></i> HARI INI
                                </span>
                            </div>
                            <p id="modalJadwalHari" class="text-xs text-slate-500 mt-0.5 truncate">Memuat hari...</p>
                        </div>
                    </div>
                    <button type="button" id="btnTutupJadwal"
                        class="w-9 h-9 shrink-0 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 active:scale-95 transition-all duration-150 cursor-pointer"
                        aria-label="Tutup jadwal">
                        <i class="ti ti-x text-base"></i>
                    </button>
                </div>
            </div>

            {{-- List Jadwal & Istirahat --}}
            <div id="jadwalList"
                class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-2"
                style="-webkit-overflow-scrolling: touch;">
                @forelse($semuaSlotHariIni ?? [] as $slot)
                    @php
                        $isIstirahat = ($slot['type'] ?? '') === 'istirahat';
                        $isPembiasaan = ($slot['type'] ?? '') === 'pembiasaan';
                    @endphp
                    @if($isIstirahat)
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 text-sm">
                                    <i class="ti ti-coffee"></i>
                                </div>
                                <span class="text-xs font-bold text-amber-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Istirahat</span>
                                <span class="text-xs text-amber-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @elseif($isPembiasaan)
                        <div class="p-3 bg-indigo-50/80 rounded-xl border border-indigo-200 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 text-sm">
                                    <i class="ti ti-book"></i>
                                </div>
                                <span class="text-xs font-bold text-indigo-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Pembiasaan</span>
                                <span class="text-xs text-indigo-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-blue-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                                    <i class="ti ti-school text-xs"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-full hidden xs:inline">KBM</span>
                                <span class="text-xs text-slate-900 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="p-6 text-center text-xs text-slate-500">
                        <i class="ti ti-calendar-off text-3xl text-slate-400 mb-2 block"></i>
                        Tidak ada jadwal KBM aktif untuk hari ini.
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3 border-t border-slate-100 bg-slate-50/90"
                 style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 14px);">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <i class="ti ti-info-circle text-blue-600 text-sm shrink-0"></i>
                        <p class="text-[11px] text-slate-500 leading-tight truncate">
                            Daftar jam pelajaran & istirahat hari ini sebagai referensi waktu dispensasi.
                        </p>
                    </div>
                    <button type="button" onclick="document.getElementById('btnTutupJadwal').click()" class="text-xs font-bold text-slate-700 hover:text-slate-900 px-3 py-1.5 rounded-lg hover:bg-slate-200 shrink-0 min-h-[36px] bg-white border border-slate-200 shadow-2xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Select2 CSS & JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Pastikan tombol floating jadwal HANYA untuk desktop saja (>= 1024px), tidak pernah muncul di mobile/tablet */
@media (max-width: 1023.98px) {
    #btnLihatJadwal {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
}

/* Custom styling Select2 serasi dengan desain modern Tailwind */
.select2-container {
    width: 100% !important;
    max-width: 100% !important;
}
.select2-container--open {
    max-width: calc(100vw - 32px) !important;
    box-sizing: border-box !important;
}
.select2-container--default .select2-selection--single {
    height: 44px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.75rem !important;
    padding: 0.5rem 0.875rem !important;
    display: flex !important;
    align-items: center !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease-in-out !important;
    box-sizing: border-box !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    background-color: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #0f172a !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    line-height: 1.25rem !important;
    padding-left: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px !important;
    right: 12px !important;
}
.select2-dropdown {
    border: 1px solid #cbd5e1 !important;
    border-radius: 1rem !important;
    box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    overflow: hidden !important;
    z-index: 70 !important;
    box-sizing: border-box !important;
    max-width: 100% !important;
}
.select2-search--dropdown {
    padding: 8px 10px !important;
    background-color: #f8fafc !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.625rem !important;
    padding: 9px 12px !important;
    font-size: 16px !important;
    outline: none !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}
.select2-results__option {
    padding: 10px 14px !important;
    font-size: 0.875rem !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.select2-results__option:last-child {
    border-bottom: none !important;
}
.select2-results__option--highlighted[aria-selected] {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}
</style>

<!-- jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/dispensasi-watermark.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // =========================================================
    // DATA DARI BACKEND
    // =========================================================
    const settings = @json($settings);
    const jadwalHariIni = @json($jadwalHariIni ?? []);
    const semuaSlotHariIni = @json($semuaSlotHariIni ?? []);

    // State data siswa terpilih untuk watermark foto dan sync telepon
    let selectedStudent = {
        id: '',
        nama: '',
        nis: '',
        kelas: '',
        jurusan: '',
        no_telepon: ''
    };

    // =========================================================
    // ELEMEN DOM
    // =========================================================
    const formDispensasi = document.getElementById('formDispensasi');
    const submitBtn = document.getElementById('submitBtn');
    const fotoInput = document.getElementById('foto_verifikasi_input');
    const selfieStandbyBox = document.getElementById('selfieStandbyBox');
    const selfieCameraBox = document.getElementById('selfieCameraBox');
    const selfiePreviewBox = document.getElementById('selfiePreviewBox');
    const selfieErrorBox = document.getElementById('selfieErrorBox');
    const selfieCameraLoading = document.getElementById('selfieCameraLoading');
    const selfieVideo = document.getElementById('selfieVideo');
    const selfiePreviewImg = document.getElementById('selfiePreviewImg');
    const selfieTimestampText = document.getElementById('selfieTimestampText');
    const btnStartSelfie = document.getElementById('btnStartSelfie');
    const btnFallbackNativeCamera = document.getElementById('btnFallbackNativeCamera');
    const btnSnapSelfie = document.getElementById('btnSnapSelfie');
    const btnRetakeSelfie = document.getElementById('btnRetakeSelfie');
    const btnCancelSelfie = document.getElementById('btnCancelSelfie');
    const btnFlipSelfie = document.getElementById('btnFlipSelfie');

    const jamKeluar = document.getElementById('jamKeluar');
    const jamKembali = document.getElementById('jamKembali');
    const infoJam = document.getElementById('infoJam');
    const noTeleponInput = document.getElementById('noTelepon');

    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');

    // Modal Jadwal Elements
    const modalJadwal = document.getElementById('modalJadwal');
    const modalJadwalOverlay = document.getElementById('modalJadwalOverlay');
    const modalJadwalContent = document.getElementById('modalJadwalContent');
    const modalJadwalHari = document.getElementById('modalJadwalHari');
    const btnTutupJadwal = document.getElementById('btnTutupJadwal');
    const btnLihatJadwal = document.getElementById('btnLihatJadwal');
    const btnLihatJadwalHeader = document.getElementById('btnLihatJadwalHeader');

    // =========================================================
    // TIMEZONE WIB HELPERS
    // =========================================================
    function getWibNow() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        return new Date(utc + (7 * 60 * 60 * 1000));
    }

    function formatTimeInput(date) {
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function timeToMinutes(time) {
        if (!time) return null;
        const parts = String(time).split(':');
        if (parts.length < 2) return null;
        const hours = Number(parts[0]);
        const minutes = Number(parts[1]);
        if (Number.isNaN(hours) || Number.isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
            return null;
        }
        return (hours * 60) + minutes;
    }

    function getAlpineData() {
        try {
            if (window.Alpine && typeof Alpine.$data === 'function' && formDispensasi) {
                return Alpine.$data(formDispensasi);
            }
        } catch (e) {}
        return null;
    }

    let isAlertShown = false;
    function showSingleAlert(config) {
        if (isAlertShown || (typeof Swal !== 'undefined' && Swal.isVisible())) {
            return Promise.resolve();
        }
        isAlertShown = true;
        return Swal.fire(Object.assign({
            confirmButtonColor: '#2563eb',
            returnFocus: false,
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-200'
            }
        }, config)).finally(() => {
            setTimeout(() => {
                isAlertShown = false;
            }, 300);
        });
    }

    // =========================================================
    // JADWAL & SLOT LOGIC
    // =========================================================
    function getScheduleSlots() {
        const source = (Array.isArray(semuaSlotHariIni) && semuaSlotHariIni.length > 0)
            ? semuaSlotHariIni
            : Object.values(jadwalHariIni || {});

        return source
            .filter(slot => slot && slot.start && slot.end)
            .map(slot => {
                const start = String(slot.start).substring(0, 5);
                const end = String(slot.end).substring(0, 5);
                return {
                    type: slot.type || 'kbm',
                    label: slot.label || 'Jam',
                    start: start,
                    end: end,
                    startMinutes: timeToMinutes(start),
                    endMinutes: timeToMinutes(end)
                };
            })
            .filter(slot => slot.startMinutes !== null && slot.endMinutes !== null && slot.endMinutes > slot.startMinutes)
            .sort((a, b) => a.startMinutes - b.startMinutes);
    }

    function getOperatingScheduleBounds() {
        const slots = getScheduleSlots();
        if (slots.length > 0) {
            return {
                start: slots[0].start,
                end: slots[slots.length - 1].end,
                startMinutes: slots[0].startMinutes,
                endMinutes: slots[slots.length - 1].endMinutes
            };
        }
        return {
            start: '07:00',
            end: '15:15',
            startMinutes: 420,
            endMinutes: 915
        };
    }

    function updateTimeInputLimits() {
        if (!jamKeluar || !jamKembali) return;

        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const bounds = getOperatingScheduleBounds();

        // Batas Jam Keluar untuk Hari Ini
        if (currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
            jamKeluar.readOnly = true;
            jamKeluar.min = bounds.end;
            jamKeluar.max = bounds.end;
        } else if (currentMinutes !== null && currentMinutes > bounds.startMinutes) {
            jamKeluar.readOnly = false;
            jamKeluar.min = currentTime;
            jamKeluar.max = bounds.end;
        } else {
            jamKeluar.readOnly = false;
            jamKeluar.min = bounds.start;
            jamKeluar.max = bounds.end;
        }

        const alpineData = getAlpineData();
        const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;

        if (!isSampaiPulang) {
            if (currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
                jamKembali.readOnly = true;
            } else {
                jamKembali.readOnly = false;
            }

            if (jamKeluar.value) {
                const kMin = timeToMinutes(jamKeluar.value);
                if (kMin !== null) {
                    const nextMin = kMin + 1;
                    const nextH = Math.floor(nextMin / 60);
                    const nextM = nextMin % 60;
                    jamKembali.min = `${String(nextH).padStart(2, '0')}:${String(nextM).padStart(2, '0')}`;
                } else {
                    jamKembali.min = jamKeluar.min;
                }
            } else {
                jamKembali.min = jamKeluar.min;
            }
            jamKembali.max = bounds.end;
        }
    }

    // =========================================================
    // VALIDASI WAKTU KELUAR & KEMBALI
    // =========================================================
    function validateJamKeluar(triggerAlert = true, isSubmitting = false) {
        if (!jamKeluar) return true;
        const val = jamKeluar.value ? jamKeluar.value.trim() : '';
        const errEl = document.getElementById('jamKeluarError');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
        }
        jamKeluar.classList.remove('border-rose-500', 'bg-rose-50');

        if (!val) {
            if (isSubmitting) {
                const msg = 'Jam Keluar wajib diisi.';
                if (errEl) {
                    errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                    errEl.classList.remove('hidden');
                }
                jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
                if (triggerAlert) {
                    showSingleAlert({
                        icon: 'warning',
                        title: 'Jam Keluar Belum Diisi',
                        text: 'Silakan tentukan jam keluar dispensasi terlebih dahulu.'
                    });
                }
                return false;
            }
            return true;
        }

        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const selMin = timeToMinutes(val);
        const bounds = getOperatingScheduleBounds();

        // 1. Cek format jam
        if (selMin === null) {
            const msg = 'Format jam keluar tidak valid.';
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
            jamKeluar.value = '';
            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Format Jam Tidak Valid',
                    text: 'Silakan masukkan format jam keluar yang benar.'
                });
            }
            return false;
        }

        // 2. Cek apakah KBM hari ini sudah berakhir
        if (currentMinutes !== null && currentMinutes >= bounds.endMinutes) {
            const msg = `Kegiatan Belajar Mengajar (KBM) hari ini (${bounds.start} - ${bounds.end} WIB) telah berakhir.`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
            jamKeluar.value = '';

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jadwal Hari Ini Telah Berakhir',
                    html: `Kegiatan Belajar Mengajar (KBM) hari ini (<strong>${bounds.start} - ${bounds.end} WIB</strong>) telah berakhir.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>.</span>`,
                    confirmButtonText: 'Mengerti'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        // 3. Cek apakah jam keluar berada di luar jadwal KBM sekolah
        if (selMin < bounds.startMinutes || selMin > bounds.endMinutes) {
            const msg = `Jam Keluar (${val} WIB) berada di luar jadwal KBM sekolah (${bounds.start} - ${bounds.end} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
            jamKeluar.value = '';

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: `Jam Keluar (<strong>${val} WIB</strong>) berada di luar jadwal KBM sekolah hari ini.<br><span class="text-xs text-slate-500 mt-2 block">Jam operasional KBM: <strong>${bounds.start} - ${bounds.end} WIB</strong>.<br>Klik tombol <strong>Lihat Jadwal</strong> untuk melihat slot jam aktif.</span>`,
                    confirmButtonText: 'Pilih Jam Sesuai Jadwal'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        // 4. Cek apakah jam sudah lewat dari jam saat ini
        if (currentMinutes !== null && selMin < currentMinutes) {
            const msg = `Jam Keluar (${val} WIB) sudah lewat dari waktu saat ini (${currentTime} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKeluar.classList.add('border-rose-500', 'bg-rose-50');
            jamKeluar.value = '';

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jam Tidak Valid',
                    html: `Jam Keluar (<strong>${val} WIB</strong>) tidak dapat dipilih karena waktu tersebut sudah lewat.<br><span class="text-xs text-slate-500 mt-2 block">Waktu saat ini: <strong>${currentTime} WIB</strong>.</span>`,
                    confirmButtonText: 'Pilih Jam Lain'
                });
            }
            updateTimeInputLimits();
            return false;
        }

        updateTimeInputLimits();
        return true;
    }

    function validateJamKembali(triggerAlert = true, isSubmitting = false) {
        if (!jamKembali) return true;
        const alpineData = getAlpineData();
        const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;
        if (isSampaiPulang) {
            const errEl = document.getElementById('jamKembaliError');
            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }
            jamKembali.classList.remove('border-rose-500', 'bg-rose-50');
            return true;
        }

        const val = jamKembali.value ? jamKembali.value.trim() : '';
        const errEl = document.getElementById('jamKembaliError');

        if (errEl) {
            errEl.textContent = '';
            errEl.classList.add('hidden');
        }
        jamKembali.classList.remove('border-rose-500', 'bg-rose-50');

        if (!val) {
            if (isSubmitting) {
                const msg = 'Jam Kembali wajib diisi jika bukan izin sampai pulang.';
                if (errEl) {
                    errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                    errEl.classList.remove('hidden');
                }
                jamKembali.classList.add('border-rose-500', 'bg-rose-50');
                if (triggerAlert) {
                    showSingleAlert({
                        icon: 'warning',
                        title: 'Jam Kembali Belum Diisi',
                        text: 'Silakan tentukan jam kembali atau centang izin sampai pulang sekolah.'
                    });
                }
                return false;
            }
            return true;
        }

        const keluarMin = timeToMinutes(jamKeluar?.value);
        const kembaliMin = timeToMinutes(val);
        const bounds = getOperatingScheduleBounds();

        if (kembaliMin === null) {
            const msg = 'Format jam kembali tidak valid.';
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKembali.classList.add('border-rose-500', 'bg-rose-50');
            jamKembali.value = '';
            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Format Jam Tidak Valid',
                    text: 'Silakan masukkan format jam kembali yang benar.'
                });
            }
            return false;
        }

        if (keluarMin === null) {
            jamKembali.value = '';
            if (triggerAlert) {
                showSingleAlert({
                    icon: 'info',
                    title: 'Tentukan Jam Keluar Dahulu',
                    text: 'Harap tentukan jam keluar sebelum memilih jam kembali.'
                });
            }
            return false;
        }

        if (kembaliMin <= keluarMin) {
            const msg = `Jam Kembali (${val} WIB) harus lebih besar dari Jam Keluar (${jamKeluar.value} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKembali.classList.add('border-rose-500', 'bg-rose-50');
            jamKembali.value = '';

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Jam Kembali Tidak Valid',
                    html: `Jam Kembali (<strong>${val} WIB</strong>) harus lebih besar daripada Jam Keluar (<strong>${jamKeluar.value} WIB</strong>).`,
                    confirmButtonText: 'Sesuaikan Jam'
                });
            }
            return false;
        }

        if (kembaliMin < bounds.startMinutes || kembaliMin > bounds.endMinutes) {
            const msg = `Jam Kembali (${val} WIB) berada di luar jadwal KBM sekolah (${bounds.start} - ${bounds.end} WIB).`;
            if (errEl) {
                errEl.innerHTML = `<i class="ti ti-alert-circle mr-1"></i>${msg}`;
                errEl.classList.remove('hidden');
            }
            jamKembali.classList.add('border-rose-500', 'bg-rose-50');
            jamKembali.value = '';

            if (triggerAlert) {
                showSingleAlert({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: `Jam Kembali (<strong>${val} WIB</strong>) berada di luar batas jadwal KBM sekolah hari ini (<strong>${bounds.start} - ${bounds.end} WIB</strong>).`,
                    confirmButtonText: 'Pilih Jam Sesuai Jadwal'
                });
            }
            return false;
        }

        return true;
    }

    if (jamKeluar) {
        jamKeluar.addEventListener('change', function () {
            validateJamKeluar(true, false);
            if (jamKembali && jamKembali.value) {
                validateJamKembali(true, false);
            }
        });
    }

    if (jamKembali) {
        jamKembali.addEventListener('change', function () {
            validateJamKembali(true, false);
        });
    }

    // =========================================================
    // INISIALISASI SELECT2 PENCARIAN SISWA (NIS / NAMA)
    // =========================================================
    $('#siswa_select').select2({
        placeholder: 'Ketik NIS atau nama siswa contoh: 4717',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route("guru.pengajuan.search-siswa") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Ketik minimal 1 karakter untuk mencari...';
            },
            searching: function () {
                return 'Mencari data siswa...';
            },
            noResults: function () {
                return 'Siswa tidak ditemukan atau belum aktif.';
            }
        },
        templateResult: function(item) {
            if (item.loading) return item.text;
            if (!item.id) return item.text;

            const nama = item.nama || item.text;
            const nis = item.nis || '-';
            const kelas = item.kelas || '-';

            return $(`
                <div class="py-1">
                    <div class="font-bold text-slate-900 text-sm leading-tight">${nama}</div>
                    <div class="text-xs text-slate-500 flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-mono font-semibold text-[11px] border border-blue-100">NIS: ${nis}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-600 font-medium">${kelas}</span>
                    </div>
                </div>
            `);
        },
        templateSelection: function(item) {
            if (!item.id) return item.text;
            if (item.nama) {
                return `${item.nama} (NIS: ${item.nis || '-'}) - ${item.kelas || '-'}`;
            }
            return item.text;
        }
    });

    // Event ketika siswa dipilih
    $('#siswa_select').on('select2:select', function (e) {
        const data = e.params.data;
        if (data && data.id) {
            selectedStudent = {
                id: data.id,
                nama: data.nama || '',
                nis: data.nis || '',
                kelas: data.kelas || '',
                jurusan: data.jurusan || '',
                no_telepon: data.no_telepon || ''
            };

            const infoNamaHeader = document.getElementById('infoNamaHeader');
            const infoNama = document.getElementById('infoNama');
            const infoNis = document.getElementById('infoNis');
            const infoKelas = document.getElementById('infoKelas');
            const card = document.getElementById('siswaInfoCard');

            if (infoNamaHeader) infoNamaHeader.textContent = selectedStudent.nama;
            if (infoNama) infoNama.textContent = selectedStudent.nama;
            if (infoNis) infoNis.textContent = selectedStudent.nis;
            if (infoKelas) infoKelas.textContent = selectedStudent.kelas;
            if (card) card.classList.remove('hidden');

            // Auto-fill nomor WhatsApp siswa jika ada
            if (noTeleponInput && (!noTeleponInput.value || noTeleponInput.dataset.autoFilled === 'true')) {
                noTeleponInput.value = selectedStudent.no_telepon || '';
                noTeleponInput.dataset.autoFilled = 'true';
            }
        }
    });

    if (noTeleponInput) {
        noTeleponInput.addEventListener('input', function() {
            delete noTeleponInput.dataset.autoFilled;
        });
    }

    // Event ketika siswa di-clear
    $('#siswa_select').on('select2:clear', function () {
        resetSelectedStudent();
    });

    function resetSelectedStudent() {
        selectedStudent = { id: '', nama: '', nis: '', kelas: '', jurusan: '', no_telepon: '' };
        const card = document.getElementById('siswaInfoCard');
        if (card) card.classList.add('hidden');
        if (noTeleponInput && noTeleponInput.dataset.autoFilled === 'true') {
            noTeleponInput.value = '';
            delete noTeleponInput.dataset.autoFilled;
        }
    }

    const btnClearSiswa = document.getElementById('btnClearSiswa');
    if (btnClearSiswa) {
        btnClearSiswa.addEventListener('click', function () {
            $('#siswa_select').val(null).trigger('change');
            resetSelectedStudent();
            $('#siswa_select').select2('open');
        });
    }

    // =========================================================
    // KAMERA SELFIE & ANTI-MIRRORING + WATERMARK RESMI
    // =========================================================
    let selfieStream = null;
    let currentFacingModeSelfie = 'user';
    let currentObjectUrl = null;

    function cleanupObjectUrl() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
    }

    function stopSelfieCamera() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => {
                try { track.stop(); } catch (err) {}
            });
            selfieStream = null;
        }
        if (selfieVideo) {
            selfieVideo.srcObject = null;
        }
        if (selfieCameraLoading) {
            selfieCameraLoading.classList.add('hidden');
        }
    }

    function startSelfieCamera(facingMode = 'user') {
        if (!selectedStudent.id) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Siswa Terlebih Dahulu',
                text: 'Harap cari dan pilih siswa penerima dispensasi sebelum membuka kamera selfie.',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                $('#siswa_select').select2('open');
            });
            return;
        }

        stopSelfieCamera();
        if (selfieErrorBox) selfieErrorBox.classList.add('hidden');
        if (selfieStandbyBox) selfieStandbyBox.classList.add('hidden');
        if (selfiePreviewBox) selfiePreviewBox.classList.add('hidden');
        if (selfieCameraBox) selfieCameraBox.classList.remove('hidden');
        if (selfieCameraLoading) selfieCameraLoading.classList.remove('hidden');

        const isSecureEnv = (window.isSecureContext === true) || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !isSecureEnv) {
            console.warn('getUserMedia live kamera tidak aktif atau tidak didukung pada konteks browser ini.');
            stopSelfieCamera();
            if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
            if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
            if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');
            if (fotoInput) {
                fotoInput.click();
            }
            return;
        }

        const constraints = {
            video: {
                facingMode: { ideal: facingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        function attachStream(stream) {
            selfieStream = stream;
            if (selfieVideo) {
                selfieVideo.srcObject = stream;
                selfieVideo.onloadedmetadata = function () {
                    selfieVideo.play().catch(function () {});
                    if (selfieCameraLoading) selfieCameraLoading.classList.add('hidden');
                };
            }
        }

        navigator.mediaDevices.getUserMedia(constraints)
            .then(attachStream)
            .catch(function (err) {
                console.warn('Constraint ideal kamera gagal, mencoba fallback standard video:true', err);
                navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                    .then(attachStream)
                    .catch(function (fallbackErr) {
                        console.warn('Kamera live tidak dapat dibuka, mengalihkan ke kamera bawaan perangkat:', fallbackErr);
                        stopSelfieCamera();
                        if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                        if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
                        if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');

                        Swal.fire({
                            icon: 'info',
                            title: 'Buka Kamera Perangkat',
                            text: 'Live kamera tidak dapat diakses langsung pada browser ini. Silakan gunakan kamera bawaan perangkat Anda.',
                            confirmButtonColor: '#2563eb',
                            confirmButtonText: '<i class="ti ti-camera mr-1.5"></i> Buka Kamera'
                        }).then(function (result) {
                            if (result.isConfirmed && fotoInput) {
                                fotoInput.click();
                            }
                        });
                    });
            });
    }

    async function processImageFileAndWatermark(file) {
        if (!file) return;

        if (!selectedStudent.id) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Siswa Terlebih Dahulu',
                text: 'Harap cari dan pilih siswa penerima dispensasi sebelum memproses foto verifikasi.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        Swal.fire({
            title: 'Memproses Foto...',
            text: 'Membubuhkan watermark verifikasi identitas resmi.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
                img.src = objectUrl;
            });
            URL.revokeObjectURL(objectUrl);

            let maxDim = 1280;
            let width = img.naturalWidth || img.width || 640;
            let height = img.naturalHeight || img.height || 480;

            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const nowWib = getWibNow();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(nowWib.getDate())}/${pad(nowWib.getMonth() + 1)}/${nowWib.getFullYear()}`;
            const timeStr = `${pad(nowWib.getHours())}:${pad(nowWib.getMinutes())}:${pad(nowWib.getSeconds())} WIB`;

            const sName = selectedStudent.nama || 'SISWA DISPENSASI';
            const sNis = selectedStudent.nis ? ` (${selectedStudent.nis})` : '';
            const sKelas = selectedStudent.kelas || '';
            const guruPiketName = @json(auth()->user()->name);

            if (typeof window.drawDispensasiWatermark === 'function') {
                window.drawDispensasiWatermark(ctx, canvas, [
                    'DIDISPEN VERIFIKASI',
                    `${sName}${sNis}`,
                    `${sKelas} • GURU PIKET: ${guruPiketName} • ${dateStr} ${timeStr}`,
                ]);
            }

            canvas.toBlob(function (blob) {
                Swal.close();
                if (!blob) {
                    Swal.fire('Gagal Memproses Foto', 'Gagal memproses file foto selfie.', 'error');
                    return;
                }

                const watermarkedFile = new File([blob], `selfie_guru_${Date.now()}.jpg`, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(watermarkedFile);
                if (fotoInput) fotoInput.files = dataTransfer.files;

                cleanupObjectUrl();
                currentObjectUrl = URL.createObjectURL(blob);
                if (selfiePreviewImg) selfiePreviewImg.src = currentObjectUrl;

                if (selfieTimestampText) {
                    selfieTimestampText.textContent = `${nowWib.toLocaleDateString('id-ID')} ${formatTimeInput(nowWib)} WIB (${sName})`;
                }

                if (selfieStandbyBox) selfieStandbyBox.classList.add('hidden');
                if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                if (selfieErrorBox) selfieErrorBox.classList.add('hidden');
                if (selfiePreviewBox) selfiePreviewBox.classList.remove('hidden');
            }, 'image/jpeg', 0.85);

        } catch (e) {
            Swal.close();
            console.error('Error saat memproses watermark foto selfie:', e);
            Swal.fire('Terjadi Kesalahan', 'Gagal memproses file gambar. Silakan coba lagi.', 'error');
        }
    }

    if (btnStartSelfie) {
        btnStartSelfie.addEventListener('click', function () {
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    if (btnFallbackNativeCamera) {
        btnFallbackNativeCamera.addEventListener('click', function () {
            if (!selectedStudent.id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Siswa Terlebih Dahulu',
                    text: 'Harap cari dan pilih siswa penerima dispensasi sebelum membuka kamera.',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    $('#siswa_select').select2('open');
                });
                return;
            }
            if (fotoInput) fotoInput.click();
        });
    }

    if (fotoInput) {
        fotoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                processImageFileAndWatermark(e.target.files[0]);
            }
        });
    }

    if (btnCancelSelfie) {
        btnCancelSelfie.addEventListener('click', function () {
            stopSelfieCamera();
            if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
            if (selfieStandbyBox) selfieStandbyBox.classList.remove('hidden');
        });
    }

    if (btnFlipSelfie) {
        btnFlipSelfie.addEventListener('click', function () {
            currentFacingModeSelfie = currentFacingModeSelfie === 'user' ? 'environment' : 'user';
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    if (btnSnapSelfie) {
        btnSnapSelfie.addEventListener('click', function () {
            if (!selfieVideo || !selfieStream) return;

            const canvas = document.createElement('canvas');
            const videoWidth = selfieVideo.videoWidth || 640;
            const videoHeight = selfieVideo.videoHeight || 480;
            canvas.width = videoWidth;
            canvas.height = videoHeight;
            const ctx = canvas.getContext('2d');

            // Anti-mirroring: Jika kamera depan (user), transformasikan balik agar un-mirrored
            if (currentFacingModeSelfie === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(selfieVideo, 0, 0, canvas.width, canvas.height);

            // Reset transformasi agar watermark terbaca normal
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            const nowWib = getWibNow();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(nowWib.getDate())}/${pad(nowWib.getMonth() + 1)}/${nowWib.getFullYear()}`;
            const timeStr = `${pad(nowWib.getHours())}:${pad(nowWib.getMinutes())}:${pad(nowWib.getSeconds())} WIB`;

            const sName = selectedStudent.nama || 'SISWA DISPENSASI';
            const sNis = selectedStudent.nis ? ` (${selectedStudent.nis})` : '';
            const sKelas = selectedStudent.kelas || '';
            const guruPiketName = @json(auth()->user()->name);

            if (typeof window.drawDispensasiWatermark === 'function') {
                window.drawDispensasiWatermark(ctx, canvas, [
                    'DIDISPEN VERIFIKASI',
                    `${sName}${sNis}`,
                    `${sKelas} • GURU PIKET: ${guruPiketName} • ${dateStr} ${timeStr}`,
                ]);
            }

            canvas.toBlob(function (blob) {
                if (!blob) return;

                if (blob.size > 5120 * 1024) {
                    Swal.fire('Foto Terlalu Besar', 'Ukuran foto selfie melebihi batas 5MB. Silakan ambil ulang.', 'warning');
                    return;
                }

                const file = new File([blob], `selfie_guru_${Date.now()}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                if (fotoInput) fotoInput.files = dataTransfer.files;

                cleanupObjectUrl();
                currentObjectUrl = URL.createObjectURL(blob);
                if (selfiePreviewImg) selfiePreviewImg.src = currentObjectUrl;

                if (selfieTimestampText) {
                    selfieTimestampText.textContent = `${nowWib.toLocaleDateString('id-ID')} ${formatTimeInput(nowWib)} WIB (${sName})`;
                }

                if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                if (selfiePreviewBox) selfiePreviewBox.classList.remove('hidden');
                stopSelfieCamera();
            }, 'image/jpeg', 0.85);
        });
    }

    if (btnRetakeSelfie) {
        btnRetakeSelfie.addEventListener('click', function () {
            cleanupObjectUrl();
            if (fotoInput) fotoInput.value = '';
            if (selfiePreviewImg) selfiePreviewImg.src = '';
            if (selfiePreviewBox) selfiePreviewBox.classList.add('hidden');
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    window.addEventListener('beforeunload', function () {
        cleanupObjectUrl();
        stopSelfieCamera();
    });
    window.addEventListener('pagehide', function () {
        cleanupObjectUrl();
        stopSelfieCamera();
    });

    // =========================================================
    // MODAL JADWAL PELAJARAN
    // =========================================================
    function formatHariTanggalWib() {
        try {
            return new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(new Date());
        } catch (e) {
            return 'Jadwal Hari Ini';
        }
    }

    function bukaModalJadwal() {
        if (!modalJadwal) return;
        if (modalJadwalHari) modalJadwalHari.textContent = formatHariTanggalWib();

        modalJadwal.classList.remove('hidden');
        if (btnLihatJadwal) btnLihatJadwal.classList.add('lg:hidden');
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(function () {
            modalJadwal.classList.remove('opacity-0');
            if (modalJadwalOverlay) modalJadwalOverlay.classList.remove('opacity-0');
            if (modalJadwalContent) {
                modalJadwalContent.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                modalJadwalContent.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            }
        });
    }

    function tutupModalJadwal() {
        if (!modalJadwal || modalJadwal.classList.contains('hidden')) return;

        modalJadwal.classList.add('opacity-0');
        if (modalJadwalOverlay) modalJadwalOverlay.classList.add('opacity-0');
        if (modalJadwalContent) {
            modalJadwalContent.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            modalJadwalContent.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        }

        setTimeout(function () {
            modalJadwal.classList.add('hidden');
            if (btnLihatJadwal) {
                btnLihatJadwal.classList.remove('lg:hidden');
            }
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }, 200);
    }

    if (btnLihatJadwal) {
        btnLihatJadwal.addEventListener('click', function (e) {
            e.preventDefault();
            bukaModalJadwal();
        });
    }

    if (btnLihatJadwalHeader) {
        btnLihatJadwalHeader.addEventListener('click', function (e) {
            e.preventDefault();
            bukaModalJadwal();
        });
    }

    if (btnTutupJadwal) {
        btnTutupJadwal.addEventListener('click', tutupModalJadwal);
    }

    if (modalJadwalOverlay) {
        modalJadwalOverlay.addEventListener('click', tutupModalJadwal);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalJadwal && !modalJadwal.classList.contains('hidden')) {
            tutupModalJadwal();
        }
    });

    // =========================================================
    // COUNTER ALASAN (0 / 500)
    // =========================================================
    if (alasan) {
        function updateCharCounter() {
            const length = alasan.value.length;
            if (charCount) charCount.textContent = length;
            if (charCounter) {
                if (length < 10 || length > 500) {
                    charCounter.classList.remove('text-emerald-600');
                    charCounter.classList.add('text-rose-500');
                } else {
                    charCounter.classList.remove('text-rose-500');
                    charCounter.classList.add('text-emerald-600');
                }
            }
        }
        alasan.addEventListener('input', updateCharCounter);
        updateCharCounter();
    }

    // =========================================================
    // VALIDASI FORM SUBMIT & PRE-SUBMIT CONFIRMATION (SWEETALERT2)
    // =========================================================
    if (formDispensasi) {
        function resetLoadingState() {
            delete formDispensasi.dataset.submitting;
            const alpineData = getAlpineData();
            if (alpineData) {
                alpineData.loading = false;
            }
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        formDispensasi.addEventListener('submit', function (e) {
            const alpineData = getAlpineData();
            if (alpineData && alpineData.isDispensasiClosed) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pengajuan Ditutup',
                    text: 'Pengajuan dispensasi saat ini sedang ditutup sesuai jam operasional sekolah.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            const isSampaiPulang = alpineData ? alpineData.sampaiPulang : false;

            // 1. Validasi Siswa
            const siswaIdVal = $('#siswa_select').val();
            if (!siswaIdVal) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire({
                    icon: 'warning',
                    title: 'Siswa Belum Dipilih',
                    text: 'Silakan cari dan pilih siswa penerima dispensasi terlebih dahulu.',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    $('#siswa_select').select2('open');
                });
                return;
            }

            // 2. Validasi Jam Keluar
            if (!validateJamKeluar(true, true)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                return;
            }

            // 3. Validasi Jam Kembali
            if (!isSampaiPulang && !validateJamKembali(true, true)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                return;
            }

            // 4. Validasi Foto Verifikasi (Opsional untuk guru tapi direkomendasikan)
            if (!formDispensasi.checkValidity()) {
                e.preventDefault();
                formDispensasi.reportValidity();
                resetLoadingState();
                return;
            }

            // 5. Pre-submit Confirmation Modal
            if (formDispensasi.dataset.confirmed !== 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();

                const kepulanganInfo = isSampaiPulang
                    ? `<span class="text-emerald-700 font-bold"><i class="ti ti-door-exit mr-1"></i>Sampai Pulang Sekolah (Bebas Check-In)</span>`
                    : `<span>Jam Kembali: <b>${jamKembali?.value || '-'} WIB</b></span>`;

                const namaSiswaText = selectedStudent.nama || 'Siswa Terpilih';
                const nisText = selectedStudent.nis ? ` (NIS: ${selectedStudent.nis})` : '';
                const kelasText = selectedStudent.kelas ? ` • ${selectedStudent.kelas}` : '';

                Swal.fire({
                    title: 'Buat & Setujui Dispensasi?',
                    html: `
                        <div class="text-left text-xs sm:text-sm text-slate-600 space-y-3">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-xs">
                                <div class="font-bold text-slate-900 text-sm mb-1">${namaSiswaText}${nisText}</div>
                                <div class="text-blue-700 font-medium">${kelasText}</div>
                            </div>
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs space-y-1">
                                <div>Jam Keluar: <b>${jamKeluar?.value || '-'} WIB</b></div>
                                <div>${kepulanganInfo}</div>
                            </div>
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs leading-relaxed">
                                <p class="font-bold mb-1 flex items-center gap-1.5 text-emerald-800">
                                    <i class="ti ti-bolt text-emerald-600 text-sm"></i> PERSETUJUAN OTOMATIS:
                                </p>
                                <p class="text-emerald-800">
                                    Dispensasi ini akan langsung berstatus <b>Disetujui</b> oleh Anda dan QR Code siap dipakai siswa untuk scan keluar di Satpam.
                                </p>
                            </div>
                            <p class="text-slate-500 text-xs">Pastikan seluruh data dan foto verifikasi (jika ada) sudah benar sebelum diproses.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="ti ti-send mr-1.5"></i> Ya, Buat & Setujui Sekarang',
                    cancelButtonText: 'Periksa Kembali',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-200'
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        formDispensasi.dataset.confirmed = 'true';
                        formDispensasi.requestSubmit();
                    } else {
                        delete formDispensasi.dataset.confirmed;
                        resetLoadingState();
                    }
                });
                return;
            }

            if (formDispensasi.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }
            formDispensasi.dataset.submitting = 'true';

            if (alpineData) {
                alpineData.loading = true;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        });
    }

    // =========================================================
    // CEK JADWAL OPERASIONAL PENGATURAN SEKOLAH
    // =========================================================
    function checkDispensasiTime() {
        const now = getWibNow();
        const dayOfWeek = now.getDay();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const currentMinutes = (hours * 60) + minutes;
        const currentTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

        const banner = document.getElementById('timeWarningBanner');
        const message = document.getElementById('timeWarningMessage');
        const timeDisplay = document.getElementById('currentTimeDisplay');
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        if (timeDisplay) {
            timeDisplay.textContent = `${days[dayOfWeek]}, ${currentTime} WIB`;
        }

        let isAllowed = true;
        let restrictionMsg = '';

        if (!settings.allowed_days || !settings.allowed_days.includes(dayOfWeek)) {
            isAllowed = false;
            restrictionMsg = 'Pengajuan dispensasi tidak diizinkan pada hari ini berdasarkan pengaturan sekolah.';
        } else {
            const startMinutes = timeToMinutes(settings.start_time);
            const endTime = dayOfWeek === 5 ? settings.end_time_friday : settings.end_time;
            const endMinutes = timeToMinutes(endTime);

            if (currentMinutes < startMinutes || currentMinutes > endMinutes) {
                isAllowed = false;
                restrictionMsg = `Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>${settings.start_time} - ${endTime} WIB</strong>.`;
            }
        }

        function updateAlpineDispensasiState(closed) {
            const alpineEl = document.getElementById('formDispensasi');
            if (alpineEl && window.Alpine) {
                try {
                    const data = Alpine.$data(alpineEl);
                    if (data) {
                        data.isDispensasiClosed = closed;
                    }
                } catch (e) {}
            }
        }

        const isExemptControl = function (el) {
            if (!el) return true;
            if (el.tagName === 'BUTTON') return true;
            if (el.id === 'foto_verifikasi_input') return true;
            if (el.id === 'btnLihatJadwalHeader' || el.id === 'btnLihatJadwal') return true;
            return false;
        };

        if (isAllowed) {
            if (banner) banner.classList.add('hidden');
            updateAlpineDispensasiState(false);
            if (formDispensasi) {
                formDispensasi.querySelectorAll('input, select, textarea').forEach(function (el) {
                    if (isExemptControl(el)) return;
                    el.disabled = false;
                    el.classList.remove('opacity-60', 'opacity-50', 'cursor-not-allowed', 'bg-slate-100');
                    if (!el.classList.contains('bg-slate-50')) {
                        el.classList.add('bg-white');
                    }
                });
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-white', 'bg-slate-100');
                }
            }
            if (window.jQuery && $('#siswa_select').length) {
                $('#siswa_select').prop('disabled', false);
                const s2 = $('#siswa_select').next('.select2-container');
                if (s2.length) {
                    s2.removeClass('opacity-60 cursor-not-allowed pointer-events-none');
                    s2.find('.select2-selection').removeClass('bg-slate-100 cursor-not-allowed');
                }
            }
            const btnClear = document.getElementById('btnClearSiswa');
            if (btnClear) {
                btnClear.disabled = false;
                btnClear.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            }
            [btnStartSelfie, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie, btnFallbackNativeCamera].forEach(function (b) {
                if (b) {
                    b.disabled = false;
                    b.classList.remove('opacity-60', 'opacity-50', 'cursor-not-allowed', 'bg-white', 'bg-slate-100');
                }
            });
            updateTimeInputLimits();
            return;
        }

        if (banner) banner.classList.remove('hidden');
        if (message) message.innerHTML = restrictionMsg;

        updateAlpineDispensasiState(true);

        if (formDispensasi) {
            formDispensasi.querySelectorAll('input, select, textarea').forEach(function (el) {
                if (isExemptControl(el)) return;
                el.disabled = true;
                el.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
                el.classList.remove('bg-white');
            });
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.remove('bg-white');
            }
        }
        if (window.jQuery && $('#siswa_select').length) {
            $('#siswa_select').prop('disabled', true);
            const s2 = $('#siswa_select').next('.select2-container');
            if (s2.length) {
                s2.addClass('opacity-60 cursor-not-allowed pointer-events-none');
                s2.find('.select2-selection').addClass('bg-slate-100 cursor-not-allowed');
            }
        }
        const btnClear = document.getElementById('btnClearSiswa');
        if (btnClear) {
            btnClear.disabled = true;
            btnClear.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        }
        [btnStartSelfie, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie, btnFallbackNativeCamera].forEach(function (b) {
            if (b) {
                b.disabled = true;
                b.classList.add('opacity-60', 'opacity-50', 'cursor-not-allowed');
            }
        });
        stopSelfieCamera();
    }

    checkDispensasiTime();
    setInterval(checkDispensasiTime, 60000);
    updateTimeInputLimits();
});
</script>
@endpush
@endsection
