@extends('admin.layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')

@section('content')
@include('components.alert')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center shrink-0">
                <i class="ti ti-settings text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Konfigurasi Operasional & Jam KBM</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola jam cetak dispensasi, batas kuota, jadwal KBM, integrasi pos gerbang, dan fallback notifikasi.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="submit" form="formSettings" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-sm shadow-blue-500/25">
                <i class="ti ti-device-floppy text-base"></i>
                <span>Simpan Pengaturan</span>
            </button>
        </div>
    </div>

    <form id="formSettings" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ========================================================= --}}
        {{-- 1. JAM OPERASIONAL CETAK & BATAS KUOTA                    --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-printer text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Jam Operasional Cetak & Kuota</h4>
                        <p class="text-xs text-slate-500">Batas waktu cetak surat dispensasi dan jumlah cetak per siswa / guru.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Mulai Cetak</label>
                        <div class="relative">
                            <input type="time" name="print_start_time" value="{{ old('print_start_time', $print_start_time ?? '06:00') }}" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Akhir Cetak</label>
                        <div class="relative">
                            <input type="time" name="print_end_time" value="{{ old('print_end_time', $print_end_time ?? '17:00') }}" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="ti ti-school text-blue-600"></i> Batas Cetak Siswa
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500">Maksimal / Surat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="number" name="student_print_limit" value="{{ old('student_print_limit', $student_print_limit ?? 3) }}" min="1" max="20" required
                                   class="w-28 px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <span class="text-xs font-semibold text-slate-600">kali cetak ulang</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="ti ti-user-check text-blue-600"></i> Batas Cetak Guru
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500">Maksimal / Surat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="number" name="teacher_print_limit" value="{{ old('teacher_print_limit', $teacher_print_limit ?? 10) }}" min="1" max="50" required
                                   class="w-28 px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <span class="text-xs font-semibold text-slate-600">kali cetak ulang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 2. JAM OPERASIONAL PENGAJUAN DISPENSASI                   --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-calendar-time text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Jam Operasional Pengajuan Dispensasi</h4>
                        <p class="text-xs text-slate-500">Tentukan rentang jam dan hari siswa diizinkan membuat permohonan baru.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mulai Buka Pengajuan</label>
                        <input type="time" name="dispensasi_start_time" value="{{ old('dispensasi_start_time', $dispensasi_start_time ?? '07:00') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tutup (Senin - Kamis)</label>
                        <input type="time" name="dispensasi_end_time" value="{{ old('dispensasi_end_time', $dispensasi_end_time ?? '15:00') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tutup (Khusus Jumat)</label>
                        <input type="time" name="dispensasi_end_time_friday" value="{{ old('dispensasi_end_time_friday', $dispensasi_end_time_friday ?? '14:00') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-800 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Hari yang Diizinkan Mengajukan</label>
                    <div class="flex flex-wrap gap-2.5">
                        @php
                            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
                            $savedDays = $dispensasi_days ?? [1, 2, 3, 4, 5];
                        @endphp
                        @foreach($days as $num => $name)
                            <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-blue-300 transition-all cursor-pointer shadow-2xs">
                                <input type="checkbox" name="dispensasi_days[]" value="{{ $num }}"
                                       {{ in_array($num, $savedDays) ? 'checked' : '' }}
                                       class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300 w-4 h-4">
                                <span class="text-xs font-bold text-slate-700">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. GURU PIKET FALLBACK WHATSAPP                          --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-brand-whatsapp text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Guru Piket Fallback WhatsApp</h4>
                        <p class="text-xs text-slate-500">Guru tujuan darurat jika guru piket pada sesi jadwal tidak merespons atau tidak aktif.</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <label for="fallback_guru_piket_id" class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Guru Fallback</label>
                <select id="fallback_guru_piket_id" name="fallback_guru_piket_id"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-xs sm:text-sm text-slate-800 transition">
                    <option value="">-- Tidak menggunakan fallback (Hanya guru pada sesi jadwal) --</option>
                    @foreach($guruFallback as $guru)
                        <option value="{{ $guru->id }}"
                                {{ (string) old('fallback_guru_piket_id', $fallback_guru_piket_id ?? '') === (string) $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama_lengkap }} ({{ $guru->no_telepon ? $guru->no_telepon : 'Nomor WhatsApp belum tersedia' }})
                        </option>
                    @endforeach
                </select>
                @error('fallback_guru_piket_id')
                    <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 4. MODE POS GERBANG (SATPAM EXPRESS) & AUTENTIKASI       --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-shield-check text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Mode Pos Gerbang (Satpam Express) & Autentikasi</h4>
                        <p class="text-xs text-slate-500">Konfigurasi kenyamanan operasional satpam di gerbang sekolah dan guru di ruang piket.</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/70">
                    <i class="ti ti-bolt text-xs"></i> Express Ready
                </span>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- 1. Login Permanen Satpam --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 bg-slate-50/60 hover:bg-white transition-all cursor-pointer shadow-2xs group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="satpam_auto_remember" value="1" {{ $satpam_auto_remember ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 group-hover:text-blue-700 transition-colors">
                                <i class="ti ti-key text-blue-600 text-sm"></i>
                                Login Permanen Satpam (Auto-Remember)
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 leading-relaxed block mt-1">
                                Akun satpam di HP pos gerbang otomatis diingat permanen. Tidak akan pernah logout sendiri meski HP dimatikan atau browser ditutup.
                            </span>
                        </div>
                    </label>

                    {{-- 2. Simpan Sesi / Ingat Akun Guru --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 bg-slate-50/60 hover:bg-white transition-all cursor-pointer shadow-2xs group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="guru_auto_remember" value="1" {{ $guru_auto_remember ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 group-hover:text-blue-700 transition-colors">
                                <i class="ti ti-user-check text-blue-600 text-sm"></i>
                                Simpan Akun / Sesi Guru (Auto-Remember)
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 leading-relaxed block mt-1">
                                Akun guru otomatis disetel ingat login (Remember Me) saat masuk agar tidak sering ter-logout saat membuka web di kelas/ruang piket.
                            </span>
                        </div>
                    </label>

                    {{-- 3. Mode Satpam Express (Auto-Confirm) --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 bg-slate-50/60 hover:bg-white transition-all cursor-pointer shadow-2xs group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="satpam_express_mode" value="1" {{ $satpam_express_mode ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 group-hover:text-blue-700 transition-colors">
                                <i class="ti ti-bolt text-blue-600 text-sm"></i>
                                Default Mode Cepat Scanner (Auto-Confirm)
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 leading-relaxed block mt-1">
                                Scanner otomatis menyetujui siswa keluar/kembali tanpa satpam harus memencet layar. Cocok untuk antrean gerbang yang ramai.
                            </span>
                        </div>
                    </label>

                    {{-- 4. Suara Beep Scanner --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 bg-slate-50/60 hover:bg-white transition-all cursor-pointer shadow-2xs group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="satpam_beep_sound" value="1" {{ $satpam_beep_sound ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 group-hover:text-blue-700 transition-colors">
                                <i class="ti ti-volume text-blue-600 text-sm"></i>
                                Audio Beep Scanner Pos
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 leading-relaxed block mt-1">
                                Scanner membunyikan nada BEEP sukses secara instan melalui Web Audio API setiap kali QR code siswa berhasil terbaca.
                            </span>
                        </div>
                    </label>

                    {{-- 5. Anti Layar Mati (Screen Wake Lock) --}}
                    <label class="relative flex items-start gap-3 p-4 rounded-xl border border-slate-200 hover:border-blue-300 bg-slate-50/60 hover:bg-white transition-all cursor-pointer shadow-2xs group md:col-span-2">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="satpam_wake_lock" value="1" {{ $satpam_wake_lock ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 group-hover:text-blue-700 transition-colors">
                                <i class="ti ti-sun text-blue-600 text-sm"></i>
                                Anti Layar Mati Scanner (Screen Wake Lock)
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 leading-relaxed block mt-1">
                                Layar scanner pos tidak akan redup atau terkunci otomatis saat ditaruh di meja/stand pos gerbang. Satpam juga dapat mematikan toggle ini di halaman scanner untuk menghemat baterai saat HP tidak dicas.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 5. JADWAL JAM PELAJARAN (SENIN-MINGGU)                    --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="ti ti-clock text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Jadwal Jam Pelajaran & Istirahat</h4>
                        <p class="text-xs text-slate-500">Atur jam mulai dan selesai setiap jam pelajaran KBM untuk semua pola hari.</p>
                    </div>
                </div>
            </div>

            @php
                $jadwalData = is_array($jam_pelajaran) ? $jam_pelajaran : (json_decode($jam_pelajaran, true) ?? []);
                $seninSelasa = $jadwalData['senin_selasa'] ?? [];
                $rabuKamis = $jadwalData['rabu_kamis'] ?? [];
                $jumat = $jadwalData['jumat'] ?? [];
                $sabtu = $jadwalData['sabtu'] ?? [];
                $minggu = $jadwalData['minggu'] ?? [];

                $istirahatData = is_array($jam_istirahat ?? null) ? $jam_istirahat : (json_decode($jam_istirahat ?? '[]', true) ?? []);
                $defIstirahat = \App\Helpers\TimeHelper::getDefaultIstirahat();
                $istirahatSeninSelasa = $istirahatData['senin_selasa'] ?? $defIstirahat['senin_selasa'];
                $istirahatRabuKamis = $istirahatData['rabu_kamis'] ?? $defIstirahat['rabu_kamis'];
                $istirahatJumat = $istirahatData['jumat'] ?? $defIstirahat['jumat'];
                $istirahatSabtu = $istirahatData['sabtu'] ?? $defIstirahat['sabtu'];
                $istirahatMinggu = $istirahatData['minggu'] ?? $defIstirahat['minggu'];
            @endphp

            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    {{-- 1. SENIN & SELASA --}}
                    <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="ti ti-calendar text-blue-600"></i> Senin & Selasa
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200/60">
                                10 Jam + 2 Istirahat
                            </span>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 sidebar-scroll">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.start', $seninSelasa[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.end', $seninSelasa[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 1 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 1
                                </span>
                                <input type="time" name="jam_istirahat_senin_selasa[1][start]"
                                       value="{{ old('jam_istirahat_senin_selasa.1.start', $istirahatSeninSelasa[1]['start'] ?? '09:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_senin_selasa[1][end]"
                                       value="{{ old('jam_istirahat_senin_selasa.1.end', $istirahatSeninSelasa[1]['end'] ?? '09:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 4; $i <= 6; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.start', $seninSelasa[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.end', $seninSelasa[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 2 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 2
                                </span>
                                <input type="time" name="jam_istirahat_senin_selasa[2][start]"
                                       value="{{ old('jam_istirahat_senin_selasa.2.start', $istirahatSeninSelasa[2]['start'] ?? '11:45') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_senin_selasa[2][end]"
                                       value="{{ old('jam_istirahat_senin_selasa.2.end', $istirahatSeninSelasa[2]['end'] ?? '12:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 7; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.start', $seninSelasa[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_senin_selasa[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_senin_selasa.'.$i.'.end', $seninSelasa[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 2. RABU & KAMIS --}}
                    <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="ti ti-calendar text-blue-600"></i> Rabu & Kamis
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200/60">
                                11 Jam + 2 Istirahat
                            </span>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 sidebar-scroll">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.start', $rabuKamis[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.end', $rabuKamis[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 1 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 1
                                </span>
                                <input type="time" name="jam_istirahat_rabu_kamis[1][start]"
                                       value="{{ old('jam_istirahat_rabu_kamis.1.start', $istirahatRabuKamis[1]['start'] ?? '09:00') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_rabu_kamis[1][end]"
                                       value="{{ old('jam_istirahat_rabu_kamis.1.end', $istirahatRabuKamis[1]['end'] ?? '09:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 4; $i <= 7; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.start', $rabuKamis[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.end', $rabuKamis[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 2 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 2
                                </span>
                                <input type="time" name="jam_istirahat_rabu_kamis[2][start]"
                                       value="{{ old('jam_istirahat_rabu_kamis.2.start', $istirahatRabuKamis[2]['start'] ?? '11:55') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_rabu_kamis[2][end]"
                                       value="{{ old('jam_istirahat_rabu_kamis.2.end', $istirahatRabuKamis[2]['end'] ?? '12:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 8; $i <= 11; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.start', $rabuKamis[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_rabu_kamis[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_rabu_kamis.'.$i.'.end', $rabuKamis[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 3. JUMAT --}}
                    <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="ti ti-calendar text-blue-600"></i> Khusus Jumat
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200/60">
                                Pembiasaan + 8 Jam
                            </span>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 sidebar-scroll">
                            {{-- Pembiasaan --}}
                            <div class="flex items-center gap-2 bg-blue-50/60 p-2 rounded-lg border border-blue-200/70">
                                <span class="text-xs font-bold text-blue-900 w-14 shrink-0 flex items-center gap-1" title="Pembiasaan">
                                    <i class="ti ti-book text-blue-600 text-xs"></i> Biasa
                                </span>
                                <input type="time" name="jam_istirahat_jumat[0][start]"
                                       value="{{ old('jam_istirahat_jumat.0.start', $istirahatJumat[0]['start'] ?? '07:00') }}" required
                                       class="flex-1 text-xs border border-blue-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-blue-500 outline-none">
                                <span class="text-blue-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_jumat[0][end]"
                                       value="{{ old('jam_istirahat_jumat.0.end', $istirahatJumat[0]['end'] ?? '08:00') }}" required
                                       class="flex-1 text-xs border border-blue-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>

                            @for($i = 1; $i <= 2; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.start', $jumat[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.end', $jumat[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 1 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 1
                                </span>
                                <input type="time" name="jam_istirahat_jumat[1][start]"
                                       value="{{ old('jam_istirahat_jumat.1.start', $istirahatJumat[1]['start'] ?? '09:10') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_jumat[1][end]"
                                       value="{{ old('jam_istirahat_jumat.1.end', $istirahatJumat[1]['end'] ?? '09:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 3; $i <= 6; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.start', $jumat[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.end', $jumat[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 2 (Sholat Jumat) --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1" title="Istirahat Sholat Jumat">
                                    <i class="ti ti-building-mosque text-amber-600 text-xs"></i> Sholat
                                </span>
                                <input type="time" name="jam_istirahat_jumat[2][start]"
                                       value="{{ old('jam_istirahat_jumat.2.start', $istirahatJumat[2]['start'] ?? '11:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_jumat[2][end]"
                                       value="{{ old('jam_istirahat_jumat.2.end', $istirahatJumat[2]['end'] ?? '12:40') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 7; $i <= 8; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.start', $jumat[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_jumat[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_jumat.'.$i.'.end', $jumat[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 4. SABTU (Testing) --}}
                    <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="ti ti-calendar text-blue-600"></i> Sabtu (Pengujian)
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700">
                                10 Jam + 2 Ist.
                            </span>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 sidebar-scroll">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.start', $sabtu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.end', $sabtu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 1 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 1
                                </span>
                                <input type="time" name="jam_istirahat_sabtu[1][start]"
                                       value="{{ old('jam_istirahat_sabtu.1.start', $istirahatSabtu[1]['start'] ?? '09:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_sabtu[1][end]"
                                       value="{{ old('jam_istirahat_sabtu.1.end', $istirahatSabtu[1]['end'] ?? '09:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 4; $i <= 6; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.start', $sabtu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.end', $sabtu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 2 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 2
                                </span>
                                <input type="time" name="jam_istirahat_sabtu[2][start]"
                                       value="{{ old('jam_istirahat_sabtu.2.start', $istirahatSabtu[2]['start'] ?? '11:45') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_sabtu[2][end]"
                                       value="{{ old('jam_istirahat_sabtu.2.end', $istirahatSabtu[2]['end'] ?? '12:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 7; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.start', $sabtu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_sabtu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_sabtu.'.$i.'.end', $sabtu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 5. MINGGU (Testing) --}}
                    <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/90 flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="ti ti-calendar text-blue-600"></i> Minggu (Pengujian)
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700">
                                10 Jam + 2 Ist.
                            </span>
                        </div>
                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1 sidebar-scroll">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.start', $minggu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.end', $minggu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 1 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 1
                                </span>
                                <input type="time" name="jam_istirahat_minggu[1][start]"
                                       value="{{ old('jam_istirahat_minggu.1.start', $istirahatMinggu[1]['start'] ?? '09:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_minggu[1][end]"
                                       value="{{ old('jam_istirahat_minggu.1.end', $istirahatMinggu[1]['end'] ?? '09:30') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 4; $i <= 6; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.start', $minggu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.end', $minggu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor

                            {{-- Istirahat 2 --}}
                            <div class="flex items-center gap-2 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-xs font-bold text-amber-900 w-14 shrink-0 flex items-center gap-1">
                                    <i class="ti ti-cup text-amber-600 text-xs"></i> Ist. 2
                                </span>
                                <input type="time" name="jam_istirahat_minggu[2][start]"
                                       value="{{ old('jam_istirahat_minggu.2.start', $istirahatMinggu[2]['start'] ?? '11:45') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                                <span class="text-amber-500 font-bold text-xs">-</span>
                                <input type="time" name="jam_istirahat_minggu[2][end]"
                                       value="{{ old('jam_istirahat_minggu.2.end', $istirahatMinggu[2]['end'] ?? '12:15') }}" required
                                       class="flex-1 text-xs border border-amber-200 rounded-md py-1.5 px-2 bg-white font-medium focus:ring-1 focus:ring-amber-500 outline-none">
                            </div>

                            @for($i = 7; $i <= 10; $i++)
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors">
                                    <span class="text-xs font-bold text-slate-600 w-14 shrink-0">Jam {{ $i }}</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][start]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.start', $minggu[$i]['start'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <span class="text-slate-400 font-bold text-xs">-</span>
                                    <input type="time" name="jam_pelajaran_minggu[{{ $i }}][end]"
                                           value="{{ old('jam_pelajaran_minggu.'.$i.'.end', $minggu[$i]['end'] ?? '') }}" required
                                           class="flex-1 text-xs border border-slate-200 rounded-md py-1.5 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                </div>
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- BOTTOM SAVE ACTION BAR --}}
        <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500">Pastikan memeriksa kembali format jam sebelum menyimpan agar KBM berjalan sesuai jadwal.</p>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-sm shadow-blue-500/25">
                <i class="ti ti-device-floppy text-base"></i>
                <span>Simpan Semua Pengaturan</span>
            </button>
        </div>

    </form>
</div>
@endsection
