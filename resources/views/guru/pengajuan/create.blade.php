@extends('guru.layouts.app')

@section('title', 'Buat Dispensasi Manual')
@section('page-title', 'Buat Dispensasi (Manual)')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-4 sm:px-6 sm:py-5 border-b border-gray-100 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 text-white flex items-center justify-center shadow-md shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-file-circle-plus text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-gray-900">Form Pengajuan Dispensasi</h3>
                <p class="text-[11px] text-gray-500">Lengkapi data dengan benar dan jujur.</p>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            @if($errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                    <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i>Periksa kembali formulir Anda:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ✅ INFO JAM PENGAJUAN --}}
            <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <p class="text-blue-800 text-xs">
                        <strong>Jam Pengajuan:</strong> Senin - Kamis (08:00 - 15:00 WIB) | Jumat (08:00 - 14:00 WIB)
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('guru.pengajuan.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- DATA SISWA --}}
                <!--<div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 sm:p-4">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Data Siswa</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <div>
                            <span class="text-gray-500 block mb-0.5">Nama Lengkap</span>
                            <span class="font-bold text-gray-800" id="siswa_nama">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-0.5">NIS / NISN</span>
                            <span class="font-mono font-bold text-gray-800" id="siswa_nis">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-0.5">Kelas</span>
                            <span class="font-bold text-gray-800" id="siswa_kelas">-</span>
                        </div>
                    </div>
                </div>-->

                {{-- GURU PIKET INFO --}}
                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm shadow-md shadow-blue-500/30 flex-shrink-0">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Guru Piket</p>
                        <p class="text-sm font-bold text-blue-900">Tercatat otomatis saat pengajuan disetujui</p>
                    </div>
                </div>

                {{-- Pilih Siswa dengan Search --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Siswa <span class="text-red-500">*</span></label>
                    <select name="siswa_id" id="siswa_select" class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all" required>
                        <option value="">Ketik NIS atau nama siswa untuk mencari...</option>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Ketik NIS (contoh: 4717) atau nama siswa, lalu pilih dari daftar.
                    </p>
                    @error('siswa_id')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('kategori') border-red-500 @enderror">
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

                {{-- Alasan --}}
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-bold text-gray-700">Alasan <span class="text-red-500">*</span></label>
                        <span class="text-[11px] text-gray-400 font-normal">Minimal 10 karakter</span>
                    </div>
                    <textarea name="alasan" id="alasan" required minlength="10" rows="3" placeholder="Jelaskan alasan pengajuan Anda dengan detail..."
                              class="w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('alasan') border-red-500 @enderror">{{ old('alasan') }}</textarea>
                    <div class="flex justify-between items-center mt-1">
                        @error('alasan')
                            <p class="text-red-500 text-xs"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-400 text-xs">Jelaskan secara jelas dan detail</p>
                        @enderror
                        <p class="text-xs font-semibold text-gray-500" id="charCounter">
                            <span id="charCount">0</span> / 10 karakter minimum
                        </p>
                    </div>
                </div>

                {{-- Foto Verifikasi --}}
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                    <label class="block text-sm font-bold text-blue-900 mb-2">
                        <i class="fas fa-camera mr-1"></i> Foto Verifikasi (Wajib)
                    </label>
                    <p class="text-xs text-blue-700 mb-3">
                        Upload foto selfie siswa untuk verifikasi oleh Satpam. Foto akan dihapus otomatis setelah siswa kembali.
                    </p>
                    <input type="file" name="foto_verifikasi" accept="image/*" required
                           class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                    @error('foto_verifikasi')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Format: JPG/PNG, Max: 2MB
                    </p>
                </div>

                {{-- Tujuan & Lokasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tujuan <span class="text-red-500">*</span></label>
                        <input type="text" name="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah Sakit"
                               class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('tujuan') border-red-500 @enderror">
                        @error('tujuan')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1"
                               class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('lokasi') border-red-500 @enderror">
                        @error('lokasi')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Jam Keluar & Jam Kembali --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Keluar <span class="text-red-500">*</span></label>
                        <select name="jam_keluar" id="jam_keluar" required class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('jam_keluar') border-red-500 @enderror">
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
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Kembali <span class="text-red-500">*</span></label>
                        <select name="jam_kembali" id="jam_kembali" required disabled class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('jam_kembali') border-red-500 @enderror">
                            <option value="">-- Pilih Jam Keluar Dulu --</option>
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

                {{-- Aksi --}}
                <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                    <button type="submit" id="submitBtn"
                            class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 active:scale-[0.98] transition-all">
                        <i class="fas fa-check mr-2"></i>Buat & Setujui
                    </button>
                    <a href="{{ route('guru.dashboard') }}"
                       class="inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // ==========================================
    // 1. SELECT2: Pencarian Siswa (NIS/Nama)
    // ==========================================
    $('#siswa_select').select2({
        placeholder: 'Ketik NIS atau nama siswa...',
        allowClear: true,
        ajax: {
            url: '{{ route("guru.pengajuan.search-siswa") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: function (data) {
            if (!data.id) return data.text;
            return $('<div class="py-1">' + data.text + '</div>');
        },
        templateSelection: function (data) {
            if (!data.id) return data.text;
            // Update data siswa yang ditampilkan di card atas
            $('#siswa_nama').text(data.nama || '-');
            $('#siswa_nis').text(data.nis || '-');
            $('#siswa_kelas').text(data.kelas || '-');
            return data.text;
        }
    });

    // ==========================================
    // 2. CHARACTER COUNTER: Alasan
    // ==========================================
    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');

    function updateCharCounter() {
        if (!alasan) return;
        const length = alasan.value.length;
        if (charCount) charCount.textContent = length;

        if (charCounter) {
            if (length < 10) {
                charCounter.classList.remove('text-emerald-600');
                charCounter.classList.add('text-red-500');
            } else {
                charCounter.classList.remove('text-red-500');
                charCounter.classList.add('text-emerald-600');
            }
        }
    }

    if (alasan) {
        alasan.addEventListener('input', updateCharCounter);
        updateCharCounter(); // Jalankan saat load jika ada old value
    }

    // ==========================================
    // 3. DISABLE JAM PELAJARAN YANG SUDAH LEWAT
    // ==========================================
    const jamKeluarSelect = document.getElementById('jam_keluar');

    function getCurrentLessonHour() {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const currentTime = currentHour * 60 + currentMinute; // Konversi ke menit

        // Jadwal pelajaran (dalam menit)
        const jadwal = [
            { jam: 1, start: 7 * 60 + 0, end: 7 * 60 + 45 },    // 07:00 - 07:45
            { jam: 2, start: 7 * 60 + 45, end: 8 * 60 + 30 },   // 07:45 - 08:30
            { jam: 3, start: 8 * 60 + 30, end: 9 * 60 + 15 },   // 08:30 - 09:15
            { jam: 4, start: 9 * 60 + 30, end: 10 * 60 + 15 },  // 09:30 - 10:15
            { jam: 5, start: 10 * 60 + 15, end: 11 * 60 + 0 },  // 10:15 - 11:00
            { jam: 6, start: 11 * 60 + 0, end: 11 * 60 + 45 },  // 11:00 - 11:45
            { jam: 7, start: 12 * 60 + 15, end: 13 * 60 + 0 },  // 12:15 - 13:00
            { jam: 8, start: 13 * 60 + 0, end: 13 * 60 + 45 },  // 13:00 - 13:45
            { jam: 9, start: 13 * 60 + 45, end: 14 * 60 + 30 }, // 13:45 - 14:30
            { jam: 10, start: 14 * 60 + 30, end: 15 * 60 + 15 } // 14:30 - 15:15
        ];

        let currentLesson = 1;
        for (const item of jadwal) {
            if (currentTime >= item.start) {
                if (currentTime <= item.end) {
                    currentLesson = item.jam; // Masih dalam jam pelajaran
                    break;
                } else {
                    currentLesson = item.jam + 1; // Sudah lewat, jam berikutnya
                }
            } else {
                break;
            }
        }
        return Math.min(currentLesson, 10);
    }

    function disablePastLessons() {
        if (!jamKeluarSelect) return;
        const currentLesson = getCurrentLessonHour();
        const options = jamKeluarSelect.querySelectorAll('option');

        options.forEach(option => {
            const value = parseInt(option.value);
            if (isNaN(value)) return; // Skip option "-- Pilih --"

            if (value < currentLesson) {
                option.disabled = true;
                option.classList.add('text-gray-300');
                option.textContent = `Jam ke-${value} (Sudah Lewat)`;
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-300');
                option.textContent = `Jam ke-${value}`;
            }
        });

        // Reset nilai jika nilai sekarang sudah tidak valid
        const currentValue = parseInt(jamKeluarSelect.value);
        if (!isNaN(currentValue) && currentValue < currentLesson) {
            jamKeluarSelect.value = '';
        }
    }

    // Jalankan saat halaman dimuat
    if (jamKeluarSelect) {
        disablePastLessons();
    }

    // ==========================================
    // 4. VALIDASI JAM KEMBALI > JAM KELUAR
    // ==========================================
    const jamKembaliSelect = document.getElementById('jam_kembali');
    const infoJam = document.getElementById('infoJam');

    function updateJamKembaliOptions() {
        if (!jamKeluarSelect || !jamKembaliSelect) return;

        const keluarValue = parseInt(jamKeluarSelect.value);
        const semuaOption = jamKembaliSelect.querySelectorAll('option');
        let adaOptionAktif = false;

        if (isNaN(keluarValue) || keluarValue <= 0) {
            jamKembaliSelect.disabled = true;
            jamKembaliSelect.value = '';
            jamKembaliSelect.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            jamKembaliSelect.classList.remove('bg-white', 'text-gray-800');
            if (infoJam) infoJam.classList.add('hidden');
            return;
        }

        jamKembaliSelect.disabled = false;
        jamKembaliSelect.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
        jamKembaliSelect.classList.add('bg-white', 'text-gray-800');

        semuaOption.forEach(option => {
            const val = parseInt(option.value);
            if (option.value === '') return;

            if (val <= keluarValue) {
                option.disabled = true;
                option.classList.add('text-gray-300');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-300');
                adaOptionAktif = true;
            }
        });

        if (infoJam) {
            infoJam.classList.remove('hidden');
            const spanInfo = infoJam.querySelector('span');
            if (spanInfo) {
                spanInfo.textContent = 'Jam kembali harus lebih dari Jam Pelajaran ke-' + keluarValue;
                spanInfo.classList.remove('text-red-500');
            }
        }

        const kembaliValue = parseInt(jamKembaliSelect.value);
        if (!isNaN(kembaliValue) && kembaliValue <= keluarValue) {
            jamKembaliSelect.value = '';
        }

        if (!adaOptionAktif && keluarValue >= 10) {
            if (infoJam) {
                infoJam.classList.remove('hidden');
                const spanInfo = infoJam.querySelector('span');
                if (spanInfo) {
                    spanInfo.textContent = 'Tidak ada jam kembali yang tersedia (sudah jam terakhir).';
                    spanInfo.classList.add('text-red-500');
                }
            }
        }
    }

    if (jamKeluarSelect) {
        jamKeluarSelect.addEventListener('change', updateJamKembaliOptions);
        updateJamKembaliOptions(); // Jalankan saat load jika ada old value
    }
});
</script>
@endpush
@endsection
