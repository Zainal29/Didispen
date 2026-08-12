@extends('siswa.layouts.app')

@section('title', 'Buat Pengajuan')
@section('page-title', 'Buat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Form Pengajuan Dispensasi</h3>

        {{-- ✅ Error Global (jika ada) --}}
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
            <p class="text-red-800 font-medium mb-2">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Terdapat kesalahan pada form:
            </p>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!isset($guruPiketHariIni))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                <p class="text-red-800 font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Maaf, tidak ada guru piket yang bertugas hari ini. Silakan hubungi Admin.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('siswa.pengajuan.store') }}" id="formDispensasi">
                @csrf

                <div class="space-y-4">
                    {{-- Data Siswa (Read-Only) --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Data Siswa</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block">Nama Lengkap</span>
                                <span class="font-semibold text-gray-800">{{ $siswa->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">NIS / NISN</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $siswa->user->nis_nip ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Kelas</span>
                                <span class="font-semibold text-gray-800">{{ $siswa->kelas->nama_kelas }} - {{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Guru Piket Otomatis (Read-Only) --}}
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-bold text-blue-800 mb-2">
                            <i class="fas fa-user-tie mr-1"></i> Guru Piket Hari Ini
                        </h4>
                        <p class="text-lg font-bold text-blue-900">GURU PIKET</p>
                        <p class="text-xs text-blue-700 mt-1">Pengajuan akan otomatis diteruskan ke guru ini.</p>
                    </div>

                    {{-- ✅ Kategori (dengan old value) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('kategori') border-red-500 @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="sakit" {{ old('kategori') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ old('kategori') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="keperluan_sekolah" {{ old('kategori') == 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah</option>
                            <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('kategori')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ✅ Alasan (dengan old value, minlength, dan counter) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan <span class="text-red-500">*</span>
                            <span class="text-xs text-gray-500 font-normal ml-2">(Minimal 10 karakter)</span>
                        </label>
                        <textarea 
                            name="alasan" 
                            id="alasan"
                            required 
                            minlength="10"
                            rows="3" 
                            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('alasan') border-red-500 @enderror" 
                            placeholder="Jelaskan alasan pengajuan dengan detail (minimal 10 karakter)...">{{ old('alasan') }}</textarea>
                        <div class="flex justify-between items-center mt-1">
                            @error('alasan')
                                <p class="text-red-500 text-xs"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @else
                                <p class="text-gray-400 text-xs">Ketik alasan yang jelas dan detail</p>
                            @enderror
                            <p class="text-xs font-medium" id="charCounter">
                                <span id="charCount">0</span> / 10 karakter minimum
                            </p>
                        </div>
                    </div>

                    {{-- Tujuan & Lokasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="tujuan" 
                                value="{{ old('tujuan') }}"
                                required 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('tujuan') border-red-500 @enderror" 
                                placeholder="Contoh: Rumah Sakit">
                            @error('tujuan')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Opsional)</label>
                            <input 
                                type="text" 
                                name="lokasi" 
                                value="{{ old('lokasi') }}"
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('lokasi') border-red-500 @enderror" 
                                placeholder="Contoh: Jl. Merdeka No. 1">
                            @error('lokasi')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ✅ Jam Keluar & Jam Kembali (dengan old value) --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Keluar <span class="text-red-500">*</span></label>
                            <select name="jam_keluar" id="jamKeluar" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('jam_keluar') border-red-500 @enderror">
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('jam_keluar') == $i ? 'selected' : '' }}>Jam Pelajaran ke-{{ $i }}</option>
                                @endfor
                            </select>
                            @error('jam_keluar')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kembali <span class="text-red-500">*</span></label>
                            <select name="jam_kembali" id="jamKembali" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none @error('jam_kembali') border-red-500 @enderror">
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('jam_kembali') == $i ? 'selected' : '' }}>Jam Pelajaran ke-{{ $i }}</option>
                                @endfor
                            </select>
                            @error('jam_kembali')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p id="infoJam" class="text-xs text-gray-500 mt-1 hidden">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span></span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                    </button>
                    <a href="{{ route('siswa.pengajuan.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                        Batal
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- ✅ JAVASCRIPT: Logika Disable Jam Kembali + Counter Karakter --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jamKeluar = document.getElementById('jamKeluar');
    const jamKembali = document.getElementById('jamKembali');
    const infoJam = document.getElementById('infoJam');
    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');

    // ===== LOGIKA JAM KELUAR/KEMBALI =====
    function updateJamKembaliOptions() {
        const keluarValue = parseInt(jamKeluar.value);
        const semuaOption = jamKembali.querySelectorAll('option');
        let adaOptionAktif = false;

        semuaOption.forEach(option => {
            const val = parseInt(option.value);
            if (option.value === '') return;

            if (val <= keluarValue) {
                option.disabled = true;
                option.classList.add('text-gray-400');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
                adaOptionAktif = true;
            }
        });

        if (!isNaN(keluarValue) && keluarValue > 0) {
            infoJam.classList.remove('hidden');
            infoJam.querySelector('span').textContent = 
                `Jam kembali harus lebih dari Jam Pelajaran ke-${keluarValue}`;
        } else {
            infoJam.classList.add('hidden');
        }

        const kembaliValue = parseInt(jamKembali.value);
        if (!isNaN(kembaliValue) && kembaliValue <= keluarValue) {
            jamKembali.value = '';
        }

        if (!adaOptionAktif && !isNaN(keluarValue) && keluarValue >= 10) {
            infoJam.classList.remove('hidden');
            infoJam.querySelector('span').textContent = 
                'Tidak ada jam kembali yang tersedia (sudah jam terakhir).';
            infoJam.querySelector('span').classList.add('text-red-500');
        } else {
            infoJam.querySelector('span').classList.remove('text-red-500');
        }
    }

    jamKeluar.addEventListener('change', updateJamKembaliOptions);

    // Jalankan sekali saat load (untuk restore old value)
    if (jamKeluar.value) {
        updateJamKembaliOptions();
    }

    // ===== LOGIKA COUNTER KARAKTER ALASAN =====
    function updateCharCounter() {
        const length = alasan.value.length;
        charCount.textContent = length;
        
        if (length < 10) {
            charCounter.classList.remove('text-green-600');
            charCounter.classList.add('text-red-500');
        } else {
            charCounter.classList.remove('text-red-500');
            charCounter.classList.add('text-green-600');
        }
    }

    alasan.addEventListener('input', updateCharCounter);
    alasan.addEventListener('keyup', updateCharCounter);
    
    // Jalankan sekali saat load (untuk restore old value)
    if (alasan.value) {
        updateCharCounter();
    }

    // ===== VALIDASI SEBELUM SUBMIT =====
    document.getElementById('formDispensasi').addEventListener('submit', function(e) {
        const keluar = parseInt(jamKeluar.value);
        const kembali = parseInt(jamKembali.value);

        if (kembali <= keluar) {
            e.preventDefault();
            alert('Jam Kembali harus lebih besar dari Jam Keluar!');
            jamKembali.focus();
            return false;
        }

        if (alasan.value.length < 10) {
            e.preventDefault();
            alert('Alasan harus minimal 10 karakter!');
            alasan.focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection