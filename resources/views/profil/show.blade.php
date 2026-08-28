@php
    $user = auth()->user();
    $layout = match($user->role) {
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

    {{-- ===== KARTU IDENTITAS UTAMA (READ-ONLY) ===== --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 h-fit lg:sticky lg:top-6">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-blue-500/30 mb-3">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h3 class="font-bold text-gray-900 text-lg">{{ $user->name }}</h3>
            <span class="mt-1 px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-wider border border-blue-100">
                {{ $user->role }}
            </span>
        </div>

        <dl class="text-sm space-y-3 divide-y divide-gray-100">
            <div class="pt-2 flex justify-between items-center">
                <dt class="text-gray-500 font-medium">NIS / NIP</dt>
                <dd class="font-mono font-bold text-gray-800">{{ $user->nis_nip ?? '-' }}</dd>
            </div>
            <div class="pt-3 flex justify-between items-center">
                <dt class="text-gray-500 font-medium">Email Sekolah</dt>
                <dd class="text-gray-800 truncate max-w-[180px]" title="{{ $user->email }}">{{ $user->email }}</dd>
            </div>
            <div class="pt-3 flex justify-between items-center">
                <dt class="text-gray-500 font-medium">Terdaftar Sejak</dt>
                <dd class="text-gray-800">{{ $user->created_at->format('d M Y') }}</dd>
            </div>
            
            {{-- ✅ TAMPILAN NO. TELEPON SISWA --}}
            @if($user->role === 'siswa' && $user->siswa)
            <div class="pt-3">
                <dt class="text-gray-500 font-medium mb-1.5">No. Telepon / WA</dt>
                <dd class="flex items-center justify-end gap-2 text-gray-800 font-medium">
                    @if($user->siswa->no_telepon)
                        <a href="tel:{{ $user->siswa->no_telepon }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors border border-emerald-100">
                            <i class="fas fa-phone-alt text-xs"></i> 
                            {{ str_replace('+62', '0', $user->siswa->no_telepon) }}
                        </a>
                    @else
                        <span class="text-gray-400 italic text-xs">Belum tersedia</span>
                    @endif
                </dd>
            </div>
            @endif
        </dl>

        <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-gray-400 flex items-start gap-2">
            <i class="fas fa-info-circle mt-0.5"></i>
            <span>Data identitas utama dikelola oleh sistem pusat SiPintu/Sijuna.</span>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        
        {{-- ===== FORM DATA TAMBAHAN (KHUSUS SISWA) ===== --}}
        @if($user->role === 'siswa' && $user->siswa)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fas fa-user-edit text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Data Kontak & Tambahan</h3>
                    <p class="text-xs text-gray-500">Lengkapi no. WhatsApp dan data administratif untuk kelengkapan surat dispensasi.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profil.update-additional') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- No. Telepon / WhatsApp --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                            <i class="fas fa-phone-alt mr-1 text-emerald-600"></i> No. Telepon / WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 text-gray-600 font-semibold text-sm">
                                🇮🇩+62
                            </span>
                            <input type="tel" name="no_telepon" id="phone_input"
                                   value="{{ old('no_telepon', preg_replace('/^\+62/', '', $user->siswa->no_telepon ?? '')) }}"
                                   placeholder="81234567890"
                                   class="flex-1 px-4 py-3 border border-gray-200 rounded-r-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium @error('no_telepon') border-red-500 bg-red-50 @enderror">
                        </div>
                        @error('no_telepon')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                                <i class="fas fa-info-circle"></i>Masukkan nomor tanpa angka 0 di depan (contoh: 81234567890). Nomor ini digunakan untuk konfirmasi WhatsApp dispensasi.
                            </p>
                        @enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                            <i class="fas fa-calendar-alt mr-1 text-pink-500"></i> Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" 
                               value="{{ old('tanggal_lahir', $user->siswa->tanggal_lahir ? \Carbon\Carbon::parse($user->siswa->tanggal_lahir)->format('Y-m-d') : '') }}"
                               max="{{ now()->subYears(7)->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium @error('tanggal_lahir') border-red-500 bg-red-50 @enderror">
                        @error('tanggal_lahir')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-info-circle"></i>Opsional. Usia minimal 7 tahun.</p>
                        @enderror
                    </div>

                    <div class="hidden md:block"></div>

                    {{-- Alamat Lengkap --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">
                            <i class="fas fa-map-marker-alt mr-1 text-red-500"></i> Alamat Lengkap
                        </label>
                        <textarea name="alamat" rows="3" 
                                  placeholder="Masukkan alamat domisili saat ini (RT/RW, Desa, Kecamatan)..."
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-600 focus:ring-4 focus:ring-purple-50 outline-none transition-all text-sm font-medium resize-none @error('alamat') border-red-500 bg-red-50 @enderror">{{ old('alamat', $user->siswa->alamat) }}</textarea>
                        @error('alamat')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                                <i class="fas fa-info-circle"></i>
                                @if($user->siswa->alamat)
                                    Data saat ini: "{{ Str::limit($user->siswa->alamat, 50) }}". Perbarui jika kurang spesifik.
                                @else
                                    Mohon lengkapi alamat detail Anda.
                                @endif
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-[0.98] flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Data Profil
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ===== INFO KEAMANAN PASSWORD (SEMUA ROLE) ===== --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-lg"></i>
                </div>
                <h3 class="font-bold text-gray-900">Keamanan Akun & Password</h3>
            </div>
            
            <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 text-sm text-blue-800 leading-relaxed space-y-3">
                <div class="flex gap-3">
                    <i class="fas fa-sync-alt text-blue-500 mt-0.5"></i>
                    <p>Password akun Anda dikelola terpusat oleh sistem <strong>SiPintu/Sijuna</strong> dan disimpan dalam bentuk terenkripsi (hash). Demi keamanan integrasi, password <strong>tidak dapat dilihat, direset, atau diubah</strong> melalui aplikasi DIDISPEN.</p>
                </div>
                <div class="flex gap-3">
                    <i class="fas fa-headset text-blue-500 mt-0.5"></i>
                    <p>Jika Anda <strong>lupa password</strong> atau mengalami masalah login, silakan menghubungi <strong>admin pusat SiPintu/Sijuna</strong> atau operator sekolah untuk bantuan reset kredensial.</p>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
const phoneInput = document.getElementById('phone_input');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.startsWith('0')) value = value.substring(1);
        if (value.startsWith('62')) value = value.substring(2);
        e.target.value = value;
    });
}
</script>
@endpush
@endsection