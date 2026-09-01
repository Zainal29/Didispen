    @extends('siswa.layouts.app')

    @section('title', 'Buat Pengajuan')
    @section('page-title', 'Buat Pengajuan Dispensasi')

    @section('content')
    @include('components.alert')

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

                {{-- ✅ BANNER PERINGATAN WAKTU (Real-time check) --}}
                <div id="timeWarningBanner" class="hidden mb-4 p-4 rounded-xl bg-amber-50 border-2 border-amber-300">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-amber-600 text-xl mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="font-bold text-amber-900 text-sm mb-1">Pengajuan Dispensasi Tidak Tersedia</h4>
                            <p class="text-amber-800 text-xs" id="timeWarningMessage">
                                Pengajuan hanya dapat dilakukan pada hari <strong>Senin - Jumat</strong>, pukul <strong>08:00 - 15:00 WIB</strong>.
                            </p>
                            <p class="text-amber-700 text-xs mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Waktu saat ini: <span id="currentTimeDisplay" class="font-mono font-bold"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Banner Info Jam Operasional (selalu tampil) --}}
                <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <p class="text-blue-800 text-xs">
                            <strong>Jam Pengajuan:</strong> Senin - Kamis (08:00 - 15:00 WIB) | Jumat (08:00 - 14:00 WIB)
                        </p>
                    </div>
                </div>

               <form method="POST" action="{{ route('siswa.pengajuan.store') }}" enctype="multipart/form-data" id="formDispensasi" class="space-y-4">
                    @csrf

                    {{-- Data Siswa (Read-only) --}}
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 sm:p-4">
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Data Siswa</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <div>
                                <span class="text-gray-500 block mb-0.5">Nama Lengkap</span>
                                <span class="font-bold text-gray-800">{{ $siswa->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-0.5">NIS / NISN</span>
                                <span class="font-mono font-bold text-gray-800">{{ $siswa->user->nis_nip ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-0.5">Kelas</span>
                                <span class="font-bold text-gray-800">{{ $siswa->kelas?->nama_kelas ?? '-' }} — {{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Guru Piket --}}
                    <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm shadow-md shadow-blue-500/30 flex-shrink-0">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">Guru Piket</p>
                            <p class="text-sm font-bold text-blue-900">Tercatat otomatis saat pengajuan disetujui</p>
                        </div>
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

                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
        <label class="block text-sm font-bold text-blue-900 mb-2">
            <i class="fas fa-camera mr-1"></i> Foto Verifikasi (Wajib)
        </label>
        <p class="text-xs text-blue-700 mb-3">
            Upload foto selfie Anda untuk verifikasi oleh Satpam. Foto akan dihapus otomatis setelah Anda kembali.
        </p>
        <input type="file" name="foto_verifikasi" accept="image/*" required
            class="w-full px-3 py-2 border-2 border-blue-300 rounded-lg text-sm focus:outline-none focus:border-blue-600">
        @error('foto_verifikasi')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
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

                    {{-- No. Telepon --}}
                    <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-3.5">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <i class="fas fa-phone-alt text-amber-500 mr-1"></i>
                            No. Telepon / WhatsApp Anda <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="no_telepon" value="{{ old('no_telepon', $siswa->no_telepon) }}" required
                            placeholder="08xxxxxxxxxx"
                            class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('no_telepon') border-red-500 @enderror">
                        @error('no_telepon')
                            <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-500 text-xs mt-1">
                                <i class="fas fa-info-circle mr-1 text-amber-500"></i>
                                Nomor ini menjadi kontak darurat & tercatat di surat dispensasi. Pastikan nomor ini aktif.
                            </p>
                        @enderror
                    </div>

                    {{-- Jam Keluar & Jam Kembali --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Keluar <span class="text-red-500">*</span></label>
                            <select name="jam_keluar" id="jamKeluar" required class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-white text-sm font-medium text-gray-800 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('jam_keluar') border-red-500 @enderror">
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('jam_keluar') == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
                                @endfor
                            </select>
                            @error('jam_keluar')
                                <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Kembali <span class="text-red-500">*</span></label>
                            <select name="jam_kembali" id="jamKembali" required disabled class="w-full h-11 px-3.5 rounded-xl border-2 border-gray-200 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 transition-all @error('jam_kembali') border-red-500 @enderror">
                                <option value="">-- Pilih Jam Keluar Dulu --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('jam_kembali') == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
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
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                        </button>
                        <a href="{{ route('siswa.pengajuan.index') }}"
                        class="inline-flex justify-center items-center px-5 py-3 rounded-xl text-sm font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. CEK WAKTU REAL-TIME ---
        function checkDispensasiTime() {
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const wib = new Date(utc + (3600000 * 7));

            const dayOfWeek = wib.getDay(); // 0=Minggu, 1=Senin, ..., 6=Sabtu
            const hours = wib.getHours();
            const minutes = wib.getMinutes();
            const currentTime = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

            const banner = document.getElementById('timeWarningBanner');
            const message = document.getElementById('timeWarningMessage');
            const timeDisplay = document.getElementById('currentTimeDisplay');
            const form = document.getElementById('formDispensasi');
            const submitBtn = document.getElementById('submitBtn');

            if (timeDisplay) {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                timeDisplay.textContent = `${days[dayOfWeek]}, ${currentTime} WIB`;
            }

            let isAllowed = true;
            let restrictionMsg = '';

            if (dayOfWeek === 0 || dayOfWeek === 6) {
                isAllowed = false;
                restrictionMsg = 'Pengajuan dispensasi hanya dapat dilakukan pada hari <strong>Senin sampai Jumat</strong>.';
            } else {
                const jamTutup = (dayOfWeek === 5) ? 14 : 15;
                const currentHour = hours + (minutes / 60);

                if (currentHour < 8 || currentHour > jamTutup) {
                    isAllowed = false;
                    restrictionMsg = `Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>08:00 - ${jamTutup}:00 WIB</strong>.`;
                }
            }

            if (!isAllowed) {
                if (banner) banner.classList.remove('hidden');
                if (message) message.innerHTML = restrictionMsg;

                if (form) {
                    form.querySelectorAll('input, select, textarea, button').forEach(el => {
                        el.disabled = true;
                        el.classList.add('opacity-50', 'cursor-not-allowed');
                    });
                }
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pengajuan Ditutup';
                    submitBtn.classList.remove('from-blue-600', 'to-blue-700');
                    submitBtn.classList.add('from-gray-400', 'to-gray-500');
                }
            } else {
                if (banner) banner.classList.add('hidden');

                if (form) {
                    form.querySelectorAll('input, select, textarea, button').forEach(el => {
                        // Jangan enable submitBtn di sini, biarkan dikontrol oleh logika normal
                        if (el.id !== 'submitBtn') {
                            el.disabled = false;
                            el.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    });
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan';
                    submitBtn.classList.remove('from-gray-400', 'to-gray-500');
                    submitBtn.classList.add('from-blue-600', 'to-blue-700');
                }
            }
        }

        // Jalankan cek waktu saat load dan setiap 60 detik
        checkDispensasiTime();
        setInterval(checkDispensasiTime, 60000);


    // --- 2. LOGIKA JAM KELUAR & KEMBALI ---
    const jamKeluar = document.getElementById('jamKeluar');
    const jamKembali = document.getElementById('jamKembali');
    const infoJam = document.getElementById('infoJam');

    // ✅ BARU: Disable jam yang sudah lewat berdasarkan waktu saat ini
    function disablePastLessons() {
        if (!jamKeluar) return;

        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const currentTime = currentHour * 60 + currentMinute;

        // Jadwal KBM (sama dengan di server)
        const jadwal = [
            { jam: 1, start: 7 * 60 + 0, end: 7 * 60 + 45 },   // 07:00 - 07:45
            { jam: 2, start: 7 * 60 + 45, end: 8 * 60 + 30 },  // 07:45 - 08:30
            { jam: 3, start: 8 * 60 + 30, end: 9 * 60 + 15 },  // 08:30 - 09:15
            { jam: 4, start: 9 * 60 + 30, end: 10 * 60 + 15 }, // 09:30 - 10:15
            { jam: 5, start: 10 * 60 + 15, end: 11 * 60 + 0 }, // 10:15 - 11:00
            { jam: 6, start: 11 * 60 + 0, end: 11 * 60 + 45 }, // 11:00 - 11:45
            { jam: 7, start: 12 * 60 + 15, end: 13 * 60 + 0 }, // 12:15 - 13:00
            { jam: 8, start: 13 * 60 + 0, end: 13 * 60 + 45 }, // 13:00 - 13:45
            { jam: 9, start: 13 * 60 + 45, end: 14 * 60 + 30 },// 13:45 - 14:30
            { jam: 10, start: 14 * 60 + 30, end: 15 * 60 + 15 },// 14:30 - 15:15
        ];

        // Cari jam pelajaran yang sedang berjalan
        let currentLesson = 1;
        for (const item of jadwal) {
            if (currentTime >= item.start) {
                if (currentTime <= item.end) {
                    currentLesson = item.jam; // Masih dalam jam pelajaran
                } else {
                    currentLesson = item.jam + 1; // Sudah lewat, jam berikutnya
                }
            }
        }

        // Disable semua jam yang sudah lewat
        const options = jamKeluar.querySelectorAll('option');
        options.forEach(option => {
            const val = parseInt(option.value);
            if (option.value === '') return;

            if (val < currentLesson) {
                option.disabled = true;
                option.classList.add('text-gray-300', 'cursor-not-allowed');
                option.textContent = `Jam ke-${val} (Sudah Lewat)`;
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-300', 'cursor-not-allowed');
                option.textContent = `Jam ke-${val}`;
            }
        });

        // Jika jam keluar yang dipilih sudah disabled, reset
        const selectedValue = parseInt(jamKeluar.value);
        if (!isNaN(selectedValue) && selectedValue < currentLesson) {
            jamKeluar.value = '';
        }
    }

    function updateJamKembaliOptions() {
        if (!jamKeluar || !jamKembali) return;

        const keluarValue = parseInt(jamKeluar.value);
        const semuaOption = jamKembali.querySelectorAll('option');
        let adaOptionAktif = false;

        if (isNaN(keluarValue) || keluarValue <= 0) {
            jamKembali.disabled = true;
            jamKembali.value = '';
            jamKembali.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
            jamKembali.classList.remove('bg-white', 'text-gray-800');
            if (infoJam) infoJam.classList.add('hidden');
            return;
        }

        jamKembali.disabled = false;
        jamKembali.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
        jamKembali.classList.add('bg-white', 'text-gray-800');

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

        const kembaliValue = parseInt(jamKembali.value);
        if (!isNaN(kembaliValue) && kembaliValue <= keluarValue) {
            jamKembali.value = '';
        }

        if (!adaOptionAktif && keluarValue >=10) {
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

    if (jamKeluar) {
        jamKeluar.addEventListener('change', updateJamKembaliOptions);
        disablePastLessons(); // ✅ Jalankan saat load
        updateJamKembaliOptions(); // Jalankan saat load jika ada nilai old()
    }


        // --- 3. CHARACTER COUNTER ---
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
            updateCharCounter(); // Jalankan saat load jika ada nilai old()
        }


        // --- 4. FORM SUBMIT VALIDATION ---
        const formDisp = document.getElementById('formDispensasi');
        if (formDisp) {
            formDisp.addEventListener('submit', function (e) {
                const keluar = parseInt(jamKeluar.value);
                const kembali = parseInt(jamKembali.value);

                if (!keluar || isNaN(keluar)) {
                    e.preventDefault();
                    alert('Silakan pilih Jam Keluar terlebih dahulu!');
                    jamKeluar.focus();
                    return false;
                }

                if (!kembali || isNaN(kembali) || kembali <= keluar) {
                    e.preventDefault();
                    alert('Jam Kembali harus dipilih dan bernilai lebih besar dari Jam Keluar!');
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
        }
    });
    </script>
    @endpush
    @endsection
