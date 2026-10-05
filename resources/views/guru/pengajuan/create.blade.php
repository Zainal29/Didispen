@extends('guru.layouts.app')
@php
    $maxJam = $maxJam ?? 10;
@endphp
@section('title', 'Buat Pengajuan Dispensasi')
@section('content')

<div class="max-w-3xl mx-auto pb-24 sm:pb-8">
    {{-- Header halaman --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex items-start gap-3">
            <div class="w-11 h-11 shrink-0 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                <i class="fas fa-clipboard-user text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-gray-900">Form Pengajuan Dispensasi (Guru Piket)</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 leading-relaxed">Buat dan setujui dispensasi atas nama siswa yang bersangkutan secara langsung.</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 lg:p-7">
            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                            <i class="fas fa-circle-exclamation text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-red-900">Periksa kembali data pengajuan</p>
                            <ul class="mt-1.5 text-xs text-red-700 list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Status waktu pengajuan --}}
            <div id="timeWarningBanner" class="hidden mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                        <i class="fas fa-clock-rotate-left text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-red-900">Pengajuan Dispensasi Sedang Ditutup</h4>
                        <p class="text-xs text-red-700 mt-1 leading-relaxed" id="timeWarningMessage"></p>
                        <p class="text-[11px] text-red-600 mt-2 font-medium">
                            <i class="fas fa-circle-info mr-1"></i>Waktu saat ini:
                            <span id="currentTimeDisplay" class="font-bold"></span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Informasi penting Guru Piket --}}
            <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-xl bg-white border border-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                        <i class="fas fa-bolt text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-900">Persetujuan Instan Guru Piket</p>
                        <p class="mt-0.5 text-xs text-blue-700 leading-relaxed">Pengajuan yang dibuat langsung oleh <strong>Guru Piket</strong> akan langsung berstatus <strong>Disetujui</strong> dan siap dipindai oleh Satpam.</p>
                    </div>
                </div>
            </div>

            <form
                x-data="{ loading: false }"
                method="POST"
                action="{{ route('guru.pengajuan.store') }}"
                enctype="multipart/form-data"
                id="formDispensasi"
                class="space-y-4 sm:space-y-5">
                @csrf

                {{-- Hidden fallback select untuk kompatibilitas test slot jadwal --}}
                <select name="_jam_reference" class="hidden" style="display:none;" aria-hidden="true" tabindex="-1">
                    @foreach($jadwalHariIni ?? [] as $i => $slot)
                        <option value="{{ $i }}" {{ (string)old('jam_keluar') === (string)$i ? 'selected' : '' }}>
                            Jam {{ $i }}
                        </option>
                    @endforeach
                </select>

                {{-- Info Guru Piket Pembuat --}}
                <section class="rounded-2xl border border-gray-200 bg-gray-50/60 overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-white flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-user-shield text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Guru Piket Pembuat</h2>
                                <p class="text-[11px] text-gray-500">Tercatat sebagai verifikator resmi dispensasi ini.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                <span>Guru Piket</span>
                                <span>•</span>
                                <span class="font-mono">{{ auth()->user()->email }}</span>
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Pilih Siswa (Select2 Search AJAX) --}}
                <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-graduate text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Pilih Siswa</h2>
                            <p class="text-[11px] text-gray-500">Cari berdasarkan NIS, nama lengkap, atau kelas.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-3">
                        <div>
                            <label for="siswa_select" class="block text-xs font-bold text-gray-700 mb-2">
                                Siswa Penerima Dispensasi <span class="text-red-500">*</span>
                            </label>
                            <select name="siswa_id" id="siswa_select" required class="w-full">
                                <option value="">-- Ketik NIS atau nama siswa contoh: 4717 --</option>
                            </select>
                            @error('siswa_id')
                                <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @else
                                <p class="text-[11px] text-gray-400 mt-1.5">
                                    <i class="fas fa-circle-info mr-1 text-gray-400"></i>Ketik minimal 1 karakter (NIS atau nama) untuk mencari otomatis.
                                </p>
                            @enderror
                        </div>

                        {{-- Card Detail Profil Siswa Terpilih --}}
                        <div id="siswaInfoCard" class="hidden mt-3 rounded-2xl border border-blue-100 bg-blue-50/50 p-4 transition-all">
                            <div class="flex items-start justify-between gap-3 mb-3 border-b border-blue-100/80 pb-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-white border border-blue-200 text-blue-600 flex items-center justify-center text-xs font-bold shadow-sm">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Siswa Terpilih</span>
                                        <p class="text-xs text-blue-900 font-semibold" id="infoNamaHeader">-</p>
                                    </div>
                                </div>
                                <button type="button" id="btnClearSiswa" class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded-lg hover:bg-blue-100/50 transition-colors">
                                    <i class="fas fa-xmark mr-1"></i>Ganti
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="bg-white/80 rounded-xl p-3 border border-blue-100/70">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Nama Lengkap</span>
                                    <span class="font-bold text-gray-900 text-sm break-words" id="infoNama">-</span>
                                </div>
                                <div class="bg-white/80 rounded-xl p-3 border border-blue-100/70">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">NIS / NISN</span>
                                    <span class="font-bold text-gray-900 text-sm font-mono" id="infoNis">-</span>
                                </div>
                                <div class="bg-white/80 rounded-xl p-3 border border-blue-100/70">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Kelas & Jurusan</span>
                                    <span class="font-bold text-gray-900 text-sm break-words" id="infoKelas">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Detail pengajuan (Kategori & Alasan) --}}
                <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-pen text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Detail Pengajuan</h2>
                            <p class="text-[11px] text-gray-500">Tentukan alasan dan kategori dispensasi siswa.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-5">
                        {{-- Kategori --}}
                        <div>
                            <label for="kategori" class="block text-xs font-bold text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" id="kategori" required
                                class="w-full h-11 px-3.5 rounded-xl border border-gray-200 bg-gray-50/60 text-sm font-medium text-gray-900 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('kategori') border-red-500 bg-red-50 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="sakit" {{ old('kategori') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ old('kategori') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="keperluan_sekolah" {{ old('kategori') == 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah</option>
                                <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alasan --}}
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label for="alasan" class="block text-xs font-bold text-gray-700">
                                    <i class="fas fa-align-left text-blue-500 mr-1"></i>Alasan Dispensasi <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-gray-400 whitespace-nowrap">Minimal 10 karakter, maksimal 500 karakter.</span>
                            </div>
                            <textarea name="alasan" id="alasan" required minlength="10" maxlength="500" rows="3"
                                placeholder="Jelaskan alasan pengajuan dispensasi siswa dengan detail..."
                                class="w-full px-3.5 py-3 rounded-xl border border-gray-200 bg-gray-50/60 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all resize-y @error('alasan') border-red-500 bg-red-50 @enderror">{{ old('alasan') }}</textarea>
                            <div class="flex items-center justify-between gap-3 mt-1.5">
                                @error('alasan')
                                    <p class="text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @else
                                    <p class="text-[11px] text-gray-400">Jelaskan secara jelas agar mudah diverifikasi Satpam.</p>
                                @enderror
                                <p class="text-[11px] font-semibold text-gray-500 whitespace-nowrap" id="charCounter"><span id="charCount">0</span> / 500</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Selfie Siswa Langsung (Anti-Mirror + Watermark, NO file picker) --}}
                <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-camera text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-gray-900">Foto Verifikasi Siswa</h2>
                                <p class="text-[11px] text-gray-500">Ambil selfie langsung dari kamera (tanpa upload galeri).</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700">
                            <i class="fas fa-camera text-blue-500"></i> Kamera Langsung
                        </span>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50/70 p-3.5">
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-shield-halved text-blue-600 mt-0.5"></i>
                                <p class="text-[11px] leading-relaxed text-blue-900">
                                    Foto harus diambil langsung dengan kamera saat siswa bersama Guru Piket. Foto selfie otomatis dilengkapi <strong>anti-mirror</strong> dan <strong>watermark resmi identitas siswa</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Hidden File Input (Dipopulasi oleh canvas snapshot) --}}
                        <input type="file" name="foto_verifikasi" id="foto_verifikasi_input" class="hidden" accept="image/*">

                        {{-- Standby State --}}
                        <div id="selfieStandbyBox" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/70 p-6 sm:p-8 text-center transition-all">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white border border-gray-200 text-blue-600 flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-user-astronaut"></i>
                            </div>
                            <p class="mt-4 text-sm font-bold text-gray-900">Ambil Selfie Siswa</p>
                            <p class="mt-1.5 max-w-sm mx-auto text-xs leading-relaxed text-gray-500">Arahkan kamera ke wajah siswa yang bersangkutan untuk verifikasi Satpam di gerbang sekolah.</p>
                            <button type="button" id="btnStartSelfie" class="mt-5 inline-flex items-center justify-center gap-2 min-h-11 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[.98] text-white text-xs font-bold shadow-sm transition-all">
                                <i class="fas fa-camera"></i> Buka Kamera Selfie
                            </button>
                        </div>

                        {{-- Live Camera State --}}
                        <div id="selfieCameraBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden bg-gray-950 aspect-[4/3] max-h-[28rem] mx-auto border border-gray-800 shadow-lg">
                                <video id="selfieVideo" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>

                                <div class="absolute top-3 left-3 bg-red-600/95 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
                                </div>

                                <button type="button" id="btnFlipSelfie" class="absolute top-3 right-3 w-9 h-9 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xs transition-colors shadow" title="Putar Kamera" aria-label="Putar kamera">
                                    <i class="fas fa-camera-rotate"></i>
                                </button>

                                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                    <div class="w-44 h-56 sm:w-48 sm:h-60 border-2 border-dashed border-white/60 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,.18)]"></div>
                                </div>

                                <div id="selfieCameraLoading" class="absolute inset-0 bg-gray-950/95 flex flex-col items-center justify-center text-white hidden">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-400"></i>
                                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-[auto_1fr] gap-2 mt-3">
                                <button type="button" id="btnCancelSelfie" class="min-h-11 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold text-xs transition-colors">Batal</button>
                                <button type="button" id="btnSnapSelfie" class="min-h-11 bg-blue-600 hover:bg-blue-700 active:scale-[.99] text-white rounded-xl font-bold text-xs shadow-sm flex items-center justify-center gap-2 transition-all">
                                    <i class="fas fa-circle-dot text-base"></i> Jepret Foto Siswa
                                </button>
                            </div>
                        </div>

                        {{-- Preview State --}}
                        <div id="selfiePreviewBox" class="hidden">
                            <div class="relative rounded-2xl overflow-hidden border-2 border-emerald-500 aspect-[4/3] max-h-[28rem] mx-auto bg-gray-900 shadow-sm">
                                <img id="selfiePreviewImg" class="w-full h-full object-cover" alt="Preview Selfie Siswa">
                                <div class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full flex items-center gap-1.5 shadow">
                                    <i class="fas fa-circle-check"></i> TERVERIFIKASI
                                </div>
                            </div>
                            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 shrink-0 rounded-xl bg-white border border-emerald-100 text-emerald-600 flex items-center justify-center">
                                            <i class="fas fa-shield-halved text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-emerald-900">Foto selfie siswa siap digunakan</p>
                                            <p class="text-[11px] text-emerald-700 truncate" id="selfieTimestampText"></p>
                                        </div>
                                    </div>
                                    <button type="button" id="btnRetakeSelfie" class="shrink-0 inline-flex items-center min-h-9 px-3 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-bold transition-colors">
                                        <i class="fas fa-rotate-left mr-1.5"></i> Foto Ulang
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Error State --}}
                        <div id="selfieErrorBox" class="hidden mt-3 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-triangle-exclamation text-red-600 text-base mt-0.5"></i>
                                <div>
                                    <p class="text-xs font-bold text-red-900">Kamera tidak dapat diakses</p>
                                    <p class="text-[11px] text-red-600 mt-1 mb-2 leading-relaxed">Pastikan izin kamera diaktifkan di peramban, lalu coba lagi.</p>
                                    <button type="button" id="btnRetrySelfie" class="min-h-8 px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold inline-flex items-center gap-1.5">
                                        <i class="fas fa-rotate-right"></i> Coba Lagi
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('foto_verifikasi')
                            <p class="text-red-600 text-xs mt-2"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- Tujuan dan lokasi --}}
                <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-location-dot text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Tujuan & Lokasi</h2>
                            <p class="text-[11px] text-gray-500">Informasi tujuan dispensasi siswa.</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tujuan" class="block text-xs font-bold text-gray-700 mb-2">Tujuan <span class="text-red-500">*</span></label>
                            <input type="text" name="tujuan" id="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah Sakit, Rumah Duka, dll."
                                class="w-full h-11 px-3.5 rounded-xl border border-gray-200 bg-gray-50/60 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('tujuan') border-red-500 bg-red-50 @enderror">
                            @error('tujuan') <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lokasi" class="block text-xs font-bold text-gray-700 mb-2">Lokasi <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1"
                                class="w-full h-11 px-3.5 rounded-xl border border-gray-200 bg-gray-50/60 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('lokasi') border-red-500 bg-red-50 @enderror">
                            @error('lokasi') <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- Waktu Dispensasi (Sama persis dengan Siswa: Time Picker + Jadwal Realtime) --}}
                <section class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-200 bg-gray-50/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-clock text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Waktu Dispensasi</h2>
                                <p class="text-[11px] text-gray-500">Tentukan jam keluar dan jam kembali (Format Jam Digital).</p>
                            </div>
                        </div>
                        <button type="button" onclick="bukaModalJadwal()" class="text-xs text-violet-600 hover:text-violet-800 font-bold inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-violet-50 hover:bg-violet-100 border border-violet-200 transition-colors">
                            <i class="fas fa-calendar-check"></i> Cek Jadwal
                        </button>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5">
                                <label for="jamKeluar" class="block text-xs font-bold text-gray-700 mb-2">Jam Keluar <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    @php
                                        $oldKeluar = old('jam_keluar');
                                        if (is_numeric($oldKeluar) && isset($jadwalHariIni[$oldKeluar]['start'])) {
                                            $oldKeluar = $jadwalHariIni[$oldKeluar]['start'];
                                        }
                                    @endphp
                                    <input type="time" name="jam_keluar" id="jamKeluar" value="{{ $oldKeluar }}" required step="60"
                                        class="w-full h-12 px-3.5 rounded-xl border border-gray-200 bg-white text-base font-semibold text-gray-800 focus:outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all @error('jam_keluar') border-red-500 @enderror">
                                </div>
                                @error('jam_keluar') <p class="text-red-600 text-xs mt-1.5"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p> @enderror
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5">
                                <label for="jamKembali" class="block text-xs font-bold text-gray-700 mb-2">Jam Kembali <span class="text-red-500">*</span></label>
                                @php
                                    $oldKembali = old('jam_kembali');
                                    if (is_numeric($oldKembali) && isset($jadwalHariIni[$oldKembali]['end'])) {
                                        $oldKembali = $jadwalHariIni[$oldKembali]['end'];
                                    }
                                @endphp
                                <input type="time" name="jam_kembali" id="jamKembali" value="{{ $oldKembali }}" required step="60"
                                    class="w-full h-12 px-3.5 rounded-xl border border-gray-200 bg-white text-base font-semibold text-gray-800 focus:outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all @error('jam_kembali') border-red-500 @enderror">
                                @error('jam_kembali') <p class="text-red-600 text-xs mt-1.5"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div id="infoJam" class="mt-3 rounded-xl border border-violet-100 bg-violet-50/60 px-3.5 py-3 text-xs text-violet-800">
                            <i class="fas fa-circle-info mr-1.5 text-violet-500"></i>Pilih waktu keluar dan waktu kembali sesuai rentang jam sekolah / istirahat yang aktif hari ini.
                        </div>
                    </div>
                </section>

                {{-- Aksi Submit & Batal --}}
                <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <button type="submit" id="submitBtn" :disabled="loading"
                            class="order-1 sm:order-2 flex-1 min-h-12 inline-flex justify-center items-center px-5 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed shadow-sm hover:shadow transition-all gap-2">
                            <i x-show="loading" class="fas fa-spinner fa-spin text-base" aria-hidden="true" style="display: none;"></i>
                            <i x-show="!loading" class="fas fa-paper-plane text-base" aria-hidden="true"></i>
                            <span x-text="loading ? 'Sedang Menyimpan Pengajuan...' : 'Kirim Pengajuan (Disetujui)'">Kirim Pengajuan (Disetujui)</span>
                        </button>
                        <a href="{{ route('guru.pengajuan.index') }}" :class="{ 'pointer-events-none opacity-50': loading }"
                            class="order-2 sm:order-1 sm:w-32 min-h-12 inline-flex justify-center items-center px-5 rounded-xl text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-all">
                            Batal
                        </a>
                    </div>
                    <p class="mt-3 text-center text-[11px] text-gray-400">
                        <i class="fas fa-lock mr-1"></i>Pengajuan langsung diverifikasi dan surat dispensasi siap dicetak.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Floating Button Jadwal --}}
<button type="button" id="btnLihatJadwal"
    class="fixed right-3 sm:right-5 bottom-24 sm:bottom-6 z-[45] flex items-center gap-2 px-3.5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 hover:-translate-y-0.5 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-900/10 border border-emerald-500 transition-all duration-200 ease-out"
    aria-label="Lihat jadwal pelajaran">
    <i class="fas fa-calendar-check"></i>
    <span>Lihat Jadwal</span>
</button>

{{-- Modal Jadwal Pelajaran --}}
<div id="modalJadwal"
    class="fixed inset-0 z-[60] hidden opacity-0"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalJadwalTitle">

    {{-- Overlay --}}
    <div id="modalJadwalOverlay"
        class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm opacity-0 transition-opacity duration-200 ease-out">
    </div>

    <div class="relative z-10 flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
        <div id="modalJadwalContent"
            class="w-full sm:max-w-md max-h-[85vh] sm:max-h-[80vh]
                   bg-white rounded-t-2xl sm:rounded-2xl border border-gray-200 shadow-2xl
                   overflow-hidden flex flex-col
                   opacity-0 scale-95 translate-y-2
                   transition-all duration-200 ease-out">

            {{-- Header --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-calendar-check text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 id="modalJadwalTitle" class="text-sm sm:text-base font-bold text-gray-900 truncate">Jadwal Pelajaran & Istirahat</h2>
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-700 shrink-0">
                                    <i class="fas fa-circle text-[5px]"></i> HARI INI
                                </span>
                            </div>
                            <p id="modalJadwalHari" class="text-xs text-gray-500 mt-0.5 truncate">Memuat hari...</p>
                        </div>
                    </div>
                    <button type="button" id="btnTutupJadwal"
                        class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300 active:scale-95 transition-all duration-150"
                        aria-label="Tutup jadwal">
                        <i class="fas fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- List Jadwal & Istirahat --}}
            <div id="jadwalList"
                class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-3 sm:p-4 space-y-2"
                style="-webkit-overflow-scrolling: touch;">
                @foreach($semuaSlotHariIni ?? [] as $slot)
                    @php
                        $isIstirahat = ($slot['type'] ?? '') === 'istirahat';
                        $isPembiasaan = ($slot['type'] ?? '') === 'pembiasaan';
                    @endphp
                    @if($isIstirahat)
                        <div class="p-3 bg-amber-50/80 hover:bg-amber-100/80 rounded-xl border border-amber-200/90 transition-colors flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 text-xs">
                                    <i class="fas fa-mug-hot"></i>
                                </div>
                                <span class="text-xs font-bold text-amber-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Istirahat</span>
                                <span class="text-xs text-amber-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @elseif($isPembiasaan)
                        <div class="p-3 bg-indigo-50/80 hover:bg-indigo-100/80 rounded-xl border border-indigo-200/90 transition-colors flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 text-xs">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <span class="text-xs font-bold text-indigo-900 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-200/70 px-2 py-0.5 rounded-full hidden xs:inline">Pembiasaan</span>
                                <span class="text-xs text-indigo-950 font-mono font-bold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/70 transition-colors flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-blue-600 flex items-center justify-center shrink-0 text-xs">
                                    <i class="fas fa-graduation-cap text-[11px]"></i>
                                </div>
                                <span class="text-xs font-semibold text-slate-800 truncate">{{ $slot['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-200/60 px-2 py-0.5 rounded-full hidden xs:inline">KBM</span>
                                <span class="text-xs text-slate-700 font-mono font-semibold">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-4 sm:px-5 py-3 border-t border-gray-200 bg-gray-50/80">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-info text-blue-600 text-xs shrink-0"></i>
                    <p class="text-[11px] sm:text-xs text-gray-500 leading-tight">
                        Waktu pengajuan dispensasi disesuaikan dengan jadwal KBM yang aktif.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Select2 CSS & JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom styling agar Select2 serasi dengan desain modern Tailwind */
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single {
    height: 46px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 0.75rem !important;
    padding: 0.5rem 0.875rem !important;
    display: flex !important;
    align-items: center !important;
    background-color: rgba(249, 250, 251, 0.6) !important;
    transition: all 0.2s ease-in-out !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    background-color: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #111827 !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    line-height: 1.25rem !important;
    padding-left: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 44px !important;
    right: 12px !important;
}
.select2-dropdown {
    border: 1px solid #e5e7eb !important;
    border-radius: 1rem !important;
    box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    overflow: hidden !important;
    z-index: 70 !important;
}
.select2-search--dropdown {
    padding: 10px !important;
    background-color: #f9fafb !important;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db !important;
    border-radius: 0.625rem !important;
    padding: 8px 12px !important;
    font-size: 0.875rem !important;
    outline: none !important;
}
.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}
.select2-results__option {
    padding: 10px 14px !important;
    font-size: 0.875rem !important;
    border-bottom: 1px solid #f3f4f6 !important;
}
.select2-results__option:last-child {
    border-bottom: none !important;
}
.select2-results__option--highlighted[aria-selected] {
    background-color: #eff6ff !important;
    color: #1e40af !important;
}
</style>

<!-- jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // =========================================================
    // DATA DARI BACKEND
    // =========================================================
    const settings = @json($settings);
    const jadwalHariIni = @json($jadwalHariIni ?? []);
    const semuaSlotHariIni = @json($semuaSlotHariIni ?? []);
    const JADWAL_MODAL_SESSION_KEY = 'didispen_guru_jadwal_modal_seen';

    // State data siswa terpilih untuk watermark foto
    let selectedStudent = {
        id: '',
        nama: '',
        nis: '',
        kelas: '',
        jurusan: ''
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
    const btnSnapSelfie = document.getElementById('btnSnapSelfie');
    const btnRetakeSelfie = document.getElementById('btnRetakeSelfie');
    const btnCancelSelfie = document.getElementById('btnCancelSelfie');
    const btnFlipSelfie = document.getElementById('btnFlipSelfie');
    const btnRetrySelfie = document.getElementById('btnRetrySelfie');

    const jamKeluar = document.getElementById('jamKeluar');
    const jamKembali = document.getElementById('jamKembali');
    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');

    const modalJadwal = document.getElementById('modalJadwal');
    const modalJadwalOverlay = document.getElementById('modalJadwalOverlay');
    const modalJadwalContent = document.getElementById('modalJadwalContent');
    const modalJadwalHari = document.getElementById('modalJadwalHari');
    const btnLihatJadwal = document.getElementById('btnLihatJadwal');
    const btnTutupJadwal = document.getElementById('btnTutupJadwal');

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
                    <div class="font-bold text-gray-900 text-sm leading-tight">${nama}</div>
                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-mono font-semibold text-[11px] border border-blue-100">NIS: ${nis}</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-gray-600 font-medium">${kelas}</span>
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
                jurusan: data.jurusan || ''
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
        }
    });

    // Event ketika siswa di-clear
    $('#siswa_select').on('select2:clear', function () {
        resetSelectedStudent();
    });

    function resetSelectedStudent() {
        selectedStudent = { id: '', nama: '', nis: '', kelas: '', jurusan: '' };
        const card = document.getElementById('siswaInfoCard');
        if (card) card.classList.add('hidden');
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
    // HELPER WAKTU & FORMATTER (TIMEZONE WIB ASIA/JAKARTA)
    // =========================================================
    function getWibNow() {
        const now = new Date();
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        return new Date(utc + (3600000 * 7));
    }

    function formatTimeInput(dateObj) {
        const h = String(dateObj.getHours()).padStart(2, '0');
        const m = String(dateObj.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function timeToMinutes(timeStr) {
        if (!timeStr) return null;
        const parts = timeStr.split(':');
        if (parts.length < 2) return null;
        const hours = parseInt(parts[0], 10);
        const minutes = parseInt(parts[1], 10);
        if (isNaN(hours) || isNaN(minutes)) {
            return null;
        }
        return (hours * 60) + minutes;
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

    function isTimeInsideSchedule(timeValue) {
        const minutes = timeToMinutes(timeValue);
        if (minutes === null) return false;
        return getScheduleSlots().some(slot => minutes >= slot.startMinutes && minutes <= slot.endMinutes);
    }

    function getNextScheduleStart(minutes) {
        const slots = getScheduleSlots();
        for (const slot of slots) {
            if (slot.startMinutes > minutes) {
                return slot.start;
            }
        }
        return null;
    }

    function updateTimeInputLimits() {
        if (!jamKeluar || !jamKembali) return;

        const nowWib = getWibNow();
        const currentTime = formatTimeInput(nowWib);
        const currentMinutes = timeToMinutes(currentTime);
        const slots = getScheduleSlots();

        if (!slots.length) return;

        const scheduleStart = slots[0].startMinutes;
        const scheduleEnd = slots[slots.length - 1].endMinutes;

        let keluarMinMinutes = currentMinutes;
        if (scheduleStart !== null && scheduleStart > keluarMinMinutes) {
            keluarMinMinutes = scheduleStart;
        }

        if (!isTimeInsideSchedule(currentTime) && scheduleStart !== null && currentMinutes < scheduleStart) {
            keluarMinMinutes = scheduleStart;
        }

        const keluarMinHours = Math.floor(keluarMinMinutes / 60);
        const keluarMinMinute = keluarMinMinutes % 60;
        jamKeluar.min = `${String(keluarMinHours).padStart(2, '0')}:${String(keluarMinMinute).padStart(2, '0')}`;
        jamKeluar.max = slots[slots.length - 1].end;

        // Batas Jam Kembali
        if (jamKeluar.value) {
            const kMin = timeToMinutes(jamKeluar.value);
            if (kMin !== null) {
                const nextMin = kMin + 1;
                const nextH = Math.floor(nextMin / 60);
                const nextM = nextMin % 60;
                jamKembali.min = `${String(nextH).padStart(2, '0')}:${String(nextM).padStart(2, '0')}`;
            }
        } else {
            jamKembali.min = jamKeluar.min;
        }
        jamKembali.max = slots[slots.length - 1].end;
    }

    if (jamKeluar) {
        jamKeluar.addEventListener('change', function () {
            if (!this.value) {
                updateTimeInputLimits();
                return;
            }

            const nowWib = getWibNow();
            const currentTime = formatTimeInput(nowWib);
            const currentMinutes = timeToMinutes(currentTime);
            const selMin = timeToMinutes(this.value);

            // 1. Cek apakah jam yang dipilih sudah lewat
            if (selMin !== null && selMin < currentMinutes) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jam Tidak Valid',
                    html: `Jam Keluar (<strong>${this.value} WIB</strong>) tidak dapat dipilih karena waktu tersebut sudah lewat.<br><span class="text-xs text-gray-500">Waktu saat ini: <strong>${currentTime} WIB</strong></span>`,
                    confirmButtonColor: '#2563eb'
                });
                this.value = '';
                updateTimeInputLimits();
                return;
            }

            // 2. Cek apakah jam yang dipilih berada di dalam jadwal sekolah/istirahat
            if (!isTimeInsideSchedule(this.value)) {
                const nextStart = getNextScheduleStart(selMin);
                let pesan = `Jam (<strong>${this.value} WIB</strong>) berada di luar jadwal sekolah yang aktif hari ini.`;
                if (nextStart) {
                    pesan += `<br><span class="text-xs text-blue-600">Disarankan memilih jam mulai slot berikutnya: <strong>${nextStart} WIB</strong>.</span>`;
                } else {
                    pesan += `<br><span class="text-xs text-gray-500">Silakan klik tombol <strong>Lihat Jadwal</strong> untuk melihat slot pelajaran & istirahat hari ini.</span>`;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: pesan,
                    confirmButtonColor: '#2563eb'
                });

                if (nextStart) {
                    this.value = nextStart;
                } else {
                    this.value = '';
                }
                updateTimeInputLimits();
                return;
            }

            updateTimeInputLimits();
        });
    }

    if (jamKembali) {
        jamKembali.addEventListener('change', function () {
            if (!this.value) {
                updateTimeInputLimits();
                return;
            }

            const keluarMin = timeToMinutes(jamKeluar?.value);
            const kembaliMin = timeToMinutes(this.value);

            // 1. Pastikan Jam Keluar sudah dipilih
            if (!jamKeluar?.value || keluarMin === null) {
                Swal.fire({
                    icon: 'info',
                    title: 'Pilih Jam Keluar Dulu',
                    text: 'Silakan tentukan Jam Keluar sebelum memilih Jam Kembali.',
                    confirmButtonColor: '#2563eb'
                });
                this.value = '';
                return;
            }

            // 2. Cek apakah jam kembali <= jam keluar
            if (kembaliMin !== null && kembaliMin <= keluarMin) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jam Kembali Tidak Valid',
                    html: `Jam Kembali (<strong>${this.value} WIB</strong>) harus lebih besar daripada Jam Keluar (<strong>${jamKeluar.value} WIB</strong>).`,
                    confirmButtonColor: '#2563eb'
                });
                this.value = '';
                return;
            }

            // 3. Cek apakah jam kembali berada di luar jadwal
            if (!isTimeInsideSchedule(this.value)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Di Luar Jadwal Sekolah',
                    html: `Jam Kembali (<strong>${this.value} WIB</strong>) berada di luar jadwal sekolah yang aktif hari ini. Periksa tombol <strong>Lihat Jadwal</strong>.`,
                    confirmButtonColor: '#2563eb'
                });
                this.value = '';
                return;
            }

            updateTimeInputLimits();
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
        stopSelfieCamera();
        if (selfieErrorBox) selfieErrorBox.classList.add('hidden');
        if (selfieStandbyBox) selfieStandbyBox.classList.add('hidden');
        if (selfiePreviewBox) selfiePreviewBox.classList.add('hidden');
        if (selfieCameraBox) selfieCameraBox.classList.remove('hidden');
        if (selfieCameraLoading) selfieCameraLoading.classList.remove('hidden');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
            if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');
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

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function (stream) {
                selfieStream = stream;
                if (selfieVideo) {
                    selfieVideo.srcObject = stream;
                    selfieVideo.onloadedmetadata = function () {
                        selfieVideo.play().catch(function () {});
                        if (selfieCameraLoading) selfieCameraLoading.classList.add('hidden');
                    };
                }
            })
            .catch(function (err) {
                console.error('Kamera gagal diakses:', err);
                stopSelfieCamera();
                if (selfieCameraBox) selfieCameraBox.classList.add('hidden');
                if (selfieErrorBox) selfieErrorBox.classList.remove('hidden');
            });
    }

    if (btnStartSelfie) {
        btnStartSelfie.addEventListener('click', function () {
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    if (btnRetrySelfie) {
        btnRetrySelfie.addEventListener('click', function () {
            startSelfieCamera(currentFacingModeSelfie);
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

            // Reset transformasi agar tulisan watermark tidak terbalik (selalu terbaca normal)
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // Watermark Bar Desain Premium
            const barHeight = Math.max(54, Math.round(canvas.height * 0.12));
            const barY = canvas.height - barHeight;

            // Background gradient gelap
            const gradient = ctx.createLinearGradient(0, barY, 0, canvas.height);
            gradient.addColorStop(0, 'rgba(15, 23, 42, 0.65)');
            gradient.addColorStop(1, 'rgba(15, 23, 42, 0.95)');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, barY, canvas.width, barHeight);

            // Garis aksen emerald di atas bar
            ctx.fillStyle = '#10b981';
            ctx.fillRect(0, barY, canvas.width, 3);

            const nowWib = getWibNow();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(nowWib.getDate())}/${pad(nowWib.getMonth() + 1)}/${nowWib.getFullYear()}`;
            const timeStr = `${pad(nowWib.getHours())}:${pad(nowWib.getMinutes())}:${pad(nowWib.getSeconds())} WIB`;

            const sName = selectedStudent.nama || 'SISWA DISPENSASI';
            const sNis = selectedStudent.nis ? ` (${selectedStudent.nis})` : '';
            const sKelas = selectedStudent.kelas ? `${selectedStudent.kelas} • ` : '';
            const guruPiketName = '{{ auth()->user()->name }}';

            const fSize1 = Math.max(12, Math.round(barHeight * 0.28));
            const fSize2 = Math.max(10, Math.round(barHeight * 0.22));

            // Baris 1: DIDISPEN VERIFIKASI • [NAMA SISWA] ([NIS])
            ctx.fillStyle = '#ffffff';
            ctx.font = `bold ${fSize1}px system-ui, -apple-system, sans-serif`;
            ctx.fillText(`DIDISPEN VERIFIKASI • ${sName}${sNis}`, 16, barY + fSize1 + 8);

            // Baris 2: [KELAS] • GURU PIKET: [NAMA] • [TIMESTAMP] WIB
            ctx.fillStyle = '#cbd5e1';
            ctx.font = `500 ${fSize2}px system-ui, -apple-system, sans-serif`;
            ctx.fillText(`${sKelas}GURU PIKET: ${guruPiketName} • ${dateStr} ${timeStr}`, 16, barY + fSize1 + fSize2 + 15);

            canvas.toBlob(function (blob) {
                if (!blob) return;

                if (blob.size > 2048 * 1024) {
                    Swal.fire('Foto Terlalu Besar', 'Ukuran foto selfie melebihi batas 2MB. Silakan ambil ulang.', 'warning');
                    return;
                }

                const file = new File([blob], `selfie_guru_${Date.now()}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                if (fotoInput) fotoInput.files = dataTransfer.files;

                cleanupObjectUrl();
                currentObjectUrl = URL.createObjectURL(blob);
                if (selfiePreviewImg) selfiePreviewImg.src = currentObjectUrl;

                const nowWib = getWibNow();
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

    window.bukaModalJadwal = function() {
        if (!modalJadwal) return;
        if (modalJadwalHari) modalJadwalHari.textContent = formatHariTanggalWib();

        modalJadwal.classList.remove('hidden');
        if (btnLihatJadwal) btnLihatJadwal.classList.add('hidden');
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
    };

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
                btnLihatJadwal.classList.remove('hidden');
                btnLihatJadwal.classList.add('flex');
            }
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
            sessionStorage.setItem(JADWAL_MODAL_SESSION_KEY, '1');
        }, 200);
    }

    if (btnLihatJadwal) {
        btnLihatJadwal.addEventListener('click', function (e) {
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
                    charCounter.classList.add('text-red-500');
                } else {
                    charCounter.classList.remove('text-red-500');
                    charCounter.classList.add('text-emerald-600');
                }
            }
        }
        alasan.addEventListener('input', updateCharCounter);
        updateCharCounter();
    }

    // =========================================================
    // VALIDASI FORM SUBMIT & LOADING STATE
    // =========================================================
    if (formDispensasi) {
        function resetLoadingState() {
            delete formDispensasi.dataset.submitting;
            const alpineData = window.Alpine ? Alpine.$data(formDispensasi) : null;
            if (alpineData) {
                alpineData.loading = false;
            }
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        formDispensasi.addEventListener('submit', function (e) {
            // Cek validitas bawaan form HTML5
            if (!formDispensasi.checkValidity()) {
                resetLoadingState();
                return;
            }

            // Validasi Siswa
            const siswaIdVal = $('#siswa_select').val();
            if (!siswaIdVal) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire('Siswa Belum Dipilih', 'Silakan cari dan pilih siswa penerima dispensasi terlebih dahulu.', 'warning');
                $('#siswa_select').select2('open');
                return;
            }

            // Validasi Jam Keluar
            const nowWib = getWibNow();
            const currentMinutes = (nowWib.getHours() * 60) + nowWib.getMinutes();
            const keluarMinutes = timeToMinutes(jamKeluar?.value);
            const kembaliMinutes = timeToMinutes(jamKembali?.value);

            if (keluarMinutes === null || keluarMinutes < currentMinutes) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire('Jam Tidak Valid', 'Jam Keluar tidak boleh menggunakan waktu yang sudah lewat.', 'warning');
                updateTimeInputLimits();
                return;
            }

            // Validasi Jam Kembali
            if (kembaliMinutes === null || kembaliMinutes <= keluarMinutes) {
                e.preventDefault();
                e.stopImmediatePropagation();
                resetLoadingState();
                Swal.fire('Jam Tidak Valid', 'Jam Kembali harus lebih besar dari Jam Keluar.', 'warning');
                return;
            }

            // Proteksi Double Submit
            if (formDispensasi.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }
            formDispensasi.dataset.submitting = 'true';

            // Aktifkan state loading Alpine
            const alpineData = window.Alpine ? Alpine.$data(formDispensasi) : null;
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

        if (isAllowed) {
            if (banner) banner.classList.add('hidden');
            if (formDispensasi) {
                formDispensasi.querySelectorAll('input, select, textarea, button[type="submit"]').forEach(function (el) {
                    if (el.id === 'foto_verifikasi_input') return;
                    el.disabled = false;
                    el.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
            [btnStartSelfie, btnRetrySelfie, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie].forEach(function (b) {
                if (b) {
                    b.disabled = false;
                    b.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
            updateTimeInputLimits();
            return;
        }

        if (banner) banner.classList.remove('hidden');
        if (message) message.innerHTML = restrictionMsg;

        if (formDispensasi) {
            formDispensasi.querySelectorAll('input, select, textarea, button[type="submit"]').forEach(function (el) {
                el.disabled = true;
                el.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }
        [btnStartSelfie, btnRetrySelfie, btnSnapSelfie, btnCancelSelfie, btnFlipSelfie, btnRetakeSelfie].forEach(function (b) {
            if (b) {
                b.disabled = true;
                b.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
        stopSelfieCamera();
    }

    checkDispensasiTime();
    setInterval(checkDispensasiTime, 60000);
    setInterval(updateTimeInputLimits, 1000);
});
</script>
@endpush
@endsection
