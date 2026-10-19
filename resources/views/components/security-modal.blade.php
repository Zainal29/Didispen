@php
    $user = auth()->user();
    $needsUpdate = $user && $user->needsSecurityUpdate();
    $isDefaultPassword = $user && $user->isDefaultPassword();
    $phone = $user ? $user->getPhoneNumber() : null;
    $hasPhone = !empty($phone);
    $sipintuUrl = config('services.sipintu.url') ?: config('services.sipintu.base_url') ?: 'https://sipintu.smkn1bangsri.sch.id';
@endphp

@if($needsUpdate)
<div x-data="{ 
    showBanner: true,
    openModal: {{ $errors->has('no_wa') ? 'true' : 'false' }},
    dismiss() {
        this.openModal = false;
    },
    dismissBanner() {
        this.showBanner = false;
    }
}" x-show="showBanner" class="w-full mb-4">

    {{-- ========================================================= --}}
    {{-- 1. BANNER PERINGATAN (Selalu tampil di dashboard jika akun belum aman) --}}
    {{-- ========================================================= --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-amber-600 to-orange-500 p-4 sm:p-4.5 text-white shadow-xs border border-amber-400/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center flex-shrink-0 text-white shadow-xs">
                    <i class="ti ti-shield-lock text-2xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-extrabold uppercase tracking-wider">
                            Peringatan Keamanan Akun
                        </span>
                        <h4 class="text-sm sm:text-base font-bold text-white tracking-tight">
                            @if($isDefaultPassword && !$hasPhone)
                                Ganti Password di SiPintu & Daftarkan No. WhatsApp!
                            @elseif($isDefaultPassword)
                                Akun Anda Masih Menggunakan Password Bawaan ("password")!
                            @else
                                Daftarkan Nomor WhatsApp Aktif Anda
                            @endif
                        </h4>
                    </div>
                    <p class="text-xs text-amber-50/95 leading-relaxed">
                        @if($isDefaultPassword)
                            Demi keamanan akun di seluruh ekosistem sekolah, harap segera ganti password di <strong class="underline font-semibold">Portal SiPintu</strong> dan lengkapi nomor WhatsApp aktif Anda.
                        @else
                            Daftarkan nomor WhatsApp aktif Anda untuk menerima notifikasi persetujuan dispensasi dan pemulihan akun.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0 sm:self-center">
                <button type="button" 
                        @click="openModal = true"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-white text-amber-800 hover:bg-amber-50 active:scale-95 text-xs font-bold shadow-xs transition-all cursor-pointer">
                    <i class="ti ti-shield-check"></i>
                    <span>Amankan Akun Sekarang</span>
                </button>
                <!-- <button type="button"
                        @click="dismissBanner()"
                        title="Tutup Peringatan"
                        class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <i class="ti ti-x text-base"></i>
                </button> -->
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- 2. MODAL POP-UP (Otomatis Muncul & Bisa Dibuka dari Banner) --}}
    {{-- ========================================================= --}}
    <div x-show="openModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="openModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="dismiss()"></div>

        {{-- Posisi Tengah Layar --}}
        <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-slate-100">
                
                {{-- Header Modal --}}
                <div class="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-5 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white shadow-xs">
                                <i class="ti ti-shield-lock text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold leading-tight">Amankan Akun Anda</h3>
                                <p class="text-xs text-blue-100 mt-0.5">Integrasi Keamanan SiPintu & Digital Dispensasi</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="dismiss()"
                                title="Tutup Modal"
                                class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                            <i class="ti ti-x text-lg"></i>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-5">

                    {{-- Flash / Error Messages --}}
                    @if($errors->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <i class="ti ti-alert-circle text-base text-rose-600"></i>
                            <span>Mohon periksa data berikut:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- ======================================================== --}}
                    {{-- LANGKAH 1: GANTI PASSWORD DI PORTAL SIPINTU              --}}
                    {{-- ======================================================== --}}
                    <div class="rounded-2xl border p-4 transition-all {{ $isDefaultPassword ? 'border-amber-200 bg-amber-50/60' : 'border-emerald-200 bg-emerald-50/50' }}">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center {{ $isDefaultPassword ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white' }}">1</span>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-800">Ganti Password di Portal SiPintu</h4>
                            </div>
                            @if($isDefaultPassword)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900 text-[10px] font-bold">
                                    <i class="ti ti-alert-triangle text-xs"></i> Default ("password")
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <i class="ti ti-check text-xs"></i> Sudah Diganti
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            @if($isDefaultPassword)
                                Password Anda masih bawaan sekolah. Mengganti password di <strong>SiPintu</strong> akan otomatis mengamankan akun Anda di <strong>seluruh ekosistem aplikasi sekolah</strong>.
                            @else
                                Password Anda sudah aman dan bukan password default.
                            @endif
                        </p>

                        @if($isDefaultPassword)
                        <div class="flex items-center gap-2 pt-1">
                            {{-- Tombol Buka SiPintu --}}
                            <a href="{{ $sipintuUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold shadow-xs transition-all">
                                <i class="ti ti-external-link"></i>
                                <span>Buka Portal SiPintu</span>
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- ======================================================== --}}
                    {{-- LANGKAH 2: NOMOR WHATSAPP AKTIF (DISIMPAN DI SINI)       --}}
                    {{-- ======================================================== --}}
                    <div class="rounded-2xl border p-4 transition-all {{ !$hasPhone ? 'border-amber-200 bg-amber-50/60' : 'border-emerald-200 bg-emerald-50/50' }}">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center {{ !$hasPhone ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white' }}">2</span>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-800">Nomor WhatsApp Aktif</h4>
                            </div>
                            @if($hasPhone)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <i class="ti ti-check text-xs"></i> Terdaftar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">
                                    <i class="ti ti-alert-circle text-xs"></i> Wajib Diisi
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            Nomor WhatsApp aktif digunakan oleh sistem DIDISPEN untuk pemberitahuan izin, konfirmasi guru piket, dan keamanan akun.
                        </p>

                        <form action="{{ route('security.update') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="ti ti-brand-whatsapp text-lg text-emerald-600"></i>
                                    </div>
                                    <input type="tel" 
                                           id="sec_modal_no_wa" 
                                           name="no_wa" 
                                           value="{{ old('no_wa', $phone) }}" 
                                           required
                                           placeholder="Contoh: 081234567890" 
                                           class="w-full pl-10 pr-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                                </div>
                                @error('no_wa')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="submit" 
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                                    <i class="ti ti-device-floppy"></i>
                                    <span>Simpan Nomor WhatsApp</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                {{-- Footer Modal --}}
                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[11px] text-slate-500">
                        Status akun akan otomatis aman setelah kedua langkah selesai.
                    </p>
                    <button type="button" 
                            @click="dismiss()"
                            class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors cursor-pointer">
                        Nanti Saja
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endif