    @extends('guru.layouts.app')
    @php
        $maxJam = $maxJam ?? 10;
    @endphp
    @section('title', 'Buat Pengajuan Dispensasi')
    @section('content')
    @include('components.alert')

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            {{-- Header Card --}}
            <div class="p-5 border-b border-gray-200 bg-gray-50/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold flex-shrink-0">
                    <i class="fas fa-file-signature text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Buat Pengajuan Dispensasi (Guru Piket)</h3>
                    <p class="text-xs text-gray-500">Buat dispensasi atas nama siswa yang bersangkutan.</p>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                {{-- Pesan Error Validasi --}}
                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200">
                        <p class="text-xs font-bold text-red-800 mb-1 flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan:</p>
                        <ul class="text-xs text-red-700 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Peringatan Batas Waktu Pengajuan Dispensasi --}}
                <div id="timeWarningBanner" class="hidden mb-5 p-4 rounded-lg bg-red-50 border border-red-200">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-clock text-red-600 text-lg mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-bold text-red-900">Pengajuan Dispensasi Sedang Ditutup</h4>
                            <p class="text-xs text-red-700 mt-1" id="timeWarningMessage"></p>
                            <p class="text-[11px] text-red-600 mt-2 font-medium">
                                <i class="fas fa-info-circle mr-1"></i>Waktu saat ini: <span id="currentTimeDisplay" class="font-bold"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Alert Informasi --}}
                <div class="mb-6 p-4 rounded-lg bg-blue-50/60 border border-blue-100 flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-500 text-sm mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-blue-800 leading-relaxed">
                        Pengajuan yang dibuat oleh <strong>Guru Piket</strong> akan langsung berstatus <strong>Disetujui</strong>.
                    </p>
                </div>

                <!--<form method="POST"
                action="{{ route('guru.pengajuan.store') }}" enctype="multipart/form-data" id="formDispensasi" class="space-y-5">
                    @csrf-->
                    <!--<form x-data="{ loading: false }"
                          @submit="loading = true"
                          method="POST"
                          action="{{ route('guru.pengajuan.store') }}"
                          enctype="multipart/form-data"
                          id="formDispensasi"
                          class="space-y-5">
                        @csrf-->
                        <form
                            x-data="{ loading: false }"
                            @submit="loading = true"
                            method="POST"
                            action="{{ route('guru.pengajuan.store') }}"
                            enctype="multipart/form-data"
                            id="formDispensasi"
                            class="space-y-5">
                            @csrf

                    {{-- Info Guru Piket --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-blue-900">Guru Piket Pembuat</p>
                            <p class="text-xs text-blue-700">{{ auth()->user()->name }}</p>
                        </div>
                    </div>

                    {{-- Pilih Siswa (Select2 Ajax Search) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Siswa <span class="text-red-500">*</span></label>
                        <select name="siswa_id" id="siswa_select" required class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('siswa_id') border-red-500 @enderror">
                            <option value="">-- Ketik NIS atau Nama Siswa --</option>
                        </select>
                        @error('siswa_id')
                            <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @else
                            <p class="text-gray-400 text-xs mt-1"><i class="fas fa-info-circle mr-1"></i>Ketik minimal 1 karakter untuk mencari siswa berdasarkan NIS atau Nama</p>
                        @enderror
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori" required class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('kategori') border-red-500 @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="sakit" {{ old('kategori') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ old('kategori') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="keperluan_sekolah" {{ old('kategori') == 'keperluan_sekolah' ? 'selected' : '' }}>Keperluan Sekolah</option>
                            <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('kategori') <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-bold text-gray-700">Alasan <span class="text-red-500">*</span></label>
                            <span class="text-[10px] text-gray-500">Minimal 10 karakter</span>
                        </div>
                        <textarea name="alasan" id="alasan" required minlength="10" rows="3" placeholder="Jelaskan alasan pengajuan dispensasi siswa dengan detail..."
                                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('alasan') border-red-500 @enderror">{{ old('alasan') }}</textarea>
                        <div class="flex justify-between items-center mt-1">
                            @error('alasan')
                                <p class="text-red-500 text-xs flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @else
                                <p class="text-gray-400 text-xs">Jelaskan secara jelas dan detail</p>
                            @enderror
                            <p class="text-xs font-semibold text-gray-500" id="charCounter">
                                <span id="charCount">0</span> / 10 karakter minimum
                            </p>
                        </div>
                    </div>

                    {{-- Foto Verifikasi --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-bold text-blue-900 flex items-center">
                                <i class="fas fa-camera mr-1.5"></i> Foto Verifikasi (Wajib)
                            </label>
                            <button type="button" id="btnBukaKameraGuru" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold inline-flex items-center gap-1 transition-colors">
                                <i class="fas fa-video"></i> Ambil via Kamera
                            </button>
                        </div>
                        <p class="text-xs text-blue-700 mb-3">
                            Upload atau ambil foto selfie siswa langsung dengan kamera untuk verifikasi Satpam.
                        </p>

                        <div id="guruPreviewWrapper" class="hidden mb-3">
                            <div class="relative inline-block">
                                <img id="guruPreviewImg" class="h-32 rounded-lg border border-blue-300 shadow-sm object-cover">
                                <span class="absolute top-1 right-1 bg-emerald-600 text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Terpilih</span>
                            </div>
                        </div>

                        <input type="file" name="foto_verifikasi" id="guruFotoInput" accept="image/*" required
                            class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white file:cursor-pointer hover:file:bg-blue-700">
                        @error('foto_verifikasi')
                            <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1.5">
                            <i class="fas fa-info-circle mr-1"></i>Format: JPG/PNG, Max: 2MB (Bisa upload file atau ambil langsung dari kamera)
                        </p>
                    </div>

                    {{-- Tujuan & Lokasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tujuan <span class="text-red-500">*</span></label>
                            <input type="text" name="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah Sakit"
                                class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('tujuan') border-red-500 @enderror">
                            @error('tujuan')
                                <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                            <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1"
                                class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('lokasi') border-red-500 @enderror">
                            @error('lokasi')
                                <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Jam Keluar & Jam Kembali --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Keluar <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="jam_keluar" id="jam_keluar" required class="w-full h-11 px-3.5 pr-10 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all appearance-none @error('jam_keluar') border-red-500 @enderror">
                                    <option value="">-- Pilih Jam Keluar --</option>
                                    @for($i = 1; $i <= $maxJam; $i++)
                                        <option value="{{ $i }}" {{ old('jam_keluar') == $i ? 'selected' : '' }}>Jam Pelajaran ke-{{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('jam_keluar')
                                <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Kembali <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="jam_kembali" id="jam_kembali" required disabled
                                        class="w-full h-11 px-3.5 pr-10 rounded-lg border border-gray-300 bg-gray-100 text-sm text-gray-400 cursor-not-allowed focus:outline-none transition-all appearance-none opacity-60 @error('jam_kembali') border-red-500 @enderror">
                                    <option value="">-- Pilih Jam Keluar Dulu --</option>
                                    @for($i = 1; $i <= $maxJam; $i++)
                                        <option value="{{ $i }}" {{ old('jam_kembali') == $i ? 'selected' : '' }}>Jam Pelajaran ke-{{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('jam_kembali')
                                <p class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p id="infoJam" class="text-xs text-gray-500 mt-1.5 hidden">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span></span>
                            </p>
                        </div>
                    </div>

                    <!--{{-- Aksi --}}
                    <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100">
                        <button type="submit" id="submitBtn" class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm">
                            <i class="fas fa-paper-plane mr-2"></i>Buat Dispensasi

                            <button type="submit"
                                        :disabled="loading"
                                        class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed transition-all gap-2 shadow-sm">
                                    <i x-show="loading" class="fas fa-spinner fa-spin mr-1.5"></i>
                                    <span x-text="loading ? 'Sedang Memproses...' : 'Buat Dispensasi'"></span>
                                </button>
                        </button>



                        <a href="{{ route('guru.pengajuan.index') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                    </div>-->
                    <!--{{-- Aksi --}}
                    <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100">

                        <button
                            type="submit"
                            id="submitBtn"
                            :disabled="loading"
                            class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed transition-all gap-2 shadow-sm">

                            {{-- Spinner saat proses --}}
                            <i
                                x-show="loading"
                                class="fas fa-spinner fa-spin mr-1.5">
                            </i>

                            {{-- Icon pesawat saat normal --}}
                            <i
                                x-show="!loading"
                                class="fas fa-paper-plane mr-1.5">
                            </i>

                            {{-- Teks tombol --}}
                            <span x-text="loading ? 'Sedang Memproses...' : 'Buat Dispensasi'">
                                Buat Dispensasi
                            </span>
                        </button>

                        <a
                            href="{{ route('guru.pengajuan.index') }}"
                            :class="{ 'pointer-events-none opacity-50': loading }"
                            class="inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>

                    </div>-->

                    {{-- Aksi --}}
                    <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100">
                        <button
                            type="submit"
                            id="submitBtn"
                            x-data="{ loading: false }"
                            @click="loading = true"
                            :disabled="loading"
                            class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-70 disabled:cursor-not-allowed transition-all gap-2">

                            {{-- Spinner (muncul saat loading) --}}
                            <i x-show="loading" class="fas fa-spinner fa-spin text-base" aria-hidden="true"></i>

                            {{-- Ikon pesawat (muncul saat tidak loading) --}}
                            <i x-show="!loading" class="fas fa-paper-plane text-base" aria-hidden="true"></i>

                            {{-- Teks tombol --}}
                            <span x-text="loading ? 'Sedang Mengirim Pengajuan...' : 'Kirim Pengajuan'">
                                Kirim Pengajuan
                            </span>
                        </button>

                        <a
                            href="{{ route('guru.pengajuan.index') }}"
                            id="btnBatal"
                            class="inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL KAMERA LANGSUNG GURU --}}
    <div id="guruCamModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-camera text-blue-600"></i> Ambil Foto Siswa
                </h3>
                <button type="button" onclick="closeGuruCamModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-5 flex flex-col gap-3">
                <div class="relative w-full rounded-xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center border border-gray-300">
                    <video id="guruCamVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <img id="guruCamPreview" class="w-full h-full object-cover hidden">
                    <canvas id="guruCamCanvas" class="hidden"></canvas>

                    <div id="guruCamBadge" class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
                    </div>
                    <button type="button" id="guruCamFlip" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center text-xs" title="Putar Kamera">
                        <i class="fas fa-camera-rotate"></i>
                    </button>
                </div>
                <div id="guruCamControls" class="flex gap-2">
                    <button type="button" onclick="closeGuruCamModal()" class="w-1/3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold">Batal</button>
                    <button type="button" id="guruCamSnap" class="w-2/3 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow flex items-center justify-center gap-1.5">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>
                </div>
                <div id="guruCamPreviewControls" class="hidden flex gap-2">
                    <button type="button" id="guruCamRetake" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold">Foto Ulang</button>
                    <button type="button" id="guruCamUse" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow">Gunakan Foto</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- Library Auto-Compress Client-Side --}}
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>

    <script>
    let guruStream = null;
    let guruFacingMode = 'user';
    let guruTempBlob = null;

    function closeGuruCamModal() {
        if (guruStream) {
            guruStream.getTracks().forEach(t => t.stop());
            guruStream = null;
        }
        document.getElementById('guruCamModal').classList.add('hidden');
    }

    async function startGuruCamera(facingMode = 'user') {
        if (guruStream) {
            guruStream.getTracks().forEach(t => t.stop());
        }
        const video = document.getElementById('guruCamVideo');
        const preview = document.getElementById('guruCamPreview');
        preview.classList.add('hidden');
        video.classList.remove('hidden');
        document.getElementById('guruCamControls').classList.remove('hidden');
        document.getElementById('guruCamPreviewControls').classList.add('hidden');

        try {
            guruStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
            video.srcObject = guruStream;
            await video.play();
        } catch (err) {
            try {
                guruStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = guruStream;
                await video.play();
            } catch (e) {
                Swal.fire('Error', 'Tidak dapat mengakses kamera: ' + e.message, 'error');
                closeGuruCamModal();
            }
        }
    }

    $(document).ready(function() {
        // ==========================================
        // 1. AUTO-COMPRESS FOTO VERIFIKASI DARI FILE
        // ==========================================
        const fotoInput = document.getElementById('guruFotoInput');
        const previewWrapper = document.getElementById('guruPreviewWrapper');
        const previewImg = document.getElementById('guruPreviewImg');

        if (fotoInput) {
            fotoInput.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;
                Swal.fire({ title: 'Mengompres foto...', text: 'Mohon tunggu sebentar', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                try {
                    const options = { maxSizeMB: 0.5, maxWidthOrHeight: 1024, useWebWorker: true, initialQuality: 0.8 };
                    const compressedBlob = await imageCompression(file, options);
                    const validFile = new File([compressedBlob], file.name, { type: compressedBlob.type, lastModified: Date.now() });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(validFile);
                    fotoInput.files = dataTransfer.files;

                    previewImg.src = URL.createObjectURL(compressedBlob);
                    previewWrapper.classList.remove('hidden');

                    const sizeBefore = (file.size / 1024 / 1024).toFixed(2);
                    const sizeAfter = (validFile.size / 1024 / 1024).toFixed(2);
                    Swal.fire({ icon: 'success', title: 'Foto Berhasil Dimuat!', text: `Ukuran: ${sizeBefore}MB → ${sizeAfter}MB`, timer: 1500, showConfirmButton: false });
                } catch (error) {
                    previewImg.src = URL.createObjectURL(file);
                    previewWrapper.classList.remove('hidden');
                    Swal.close();
                }
            });
        }

        // ==========================================
        // 2. KAMERA LANGSUNG UNTUK GURU
        // ==========================================
        document.getElementById('btnBukaKameraGuru').addEventListener('click', function() {
            document.getElementById('guruCamModal').classList.remove('hidden');
            startGuruCamera(guruFacingMode);
        });

        document.getElementById('guruCamFlip').addEventListener('click', function() {
            guruFacingMode = (guruFacingMode === 'user') ? 'environment' : 'user';
            startGuruCamera(guruFacingMode);
        });

        document.getElementById('guruCamSnap').addEventListener('click', function() {
            const video = document.getElementById('guruCamVideo');
            if (!video.videoWidth) return;

            const canvas = document.getElementById('guruCamCanvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            if (guruFacingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                ctx.setTransform(1, 0, 0, 1, 0, 0);
            } else {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            }

            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const stampText = `GURU VERIFIKASI • ${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
            ctx.font = 'bold 16px sans-serif';
            const tw = ctx.measureText(stampText).width;
            ctx.fillStyle = 'rgba(0,0,0,0.7)';
            ctx.beginPath();
            ctx.roundRect(canvas.width - tw - 30, canvas.height - 35, tw + 20, 26, 4);
            ctx.fill();
            ctx.fillStyle = '#ffffff';
            ctx.fillText(stampText, canvas.width - tw - 20, canvas.height - 17);

            canvas.toBlob(function(blob) {
                guruTempBlob = blob;
                const preview = document.getElementById('guruCamPreview');
                preview.src = URL.createObjectURL(blob);
                preview.classList.remove('hidden');
                video.classList.add('hidden');
                document.getElementById('guruCamControls').classList.add('hidden');
                document.getElementById('guruCamPreviewControls').classList.remove('hidden');
                if (guruStream) {
                    guruStream.getTracks().forEach(t => t.stop());
                    guruStream = null;
                }
            }, 'image/jpeg', 0.85);
        });

        document.getElementById('guruCamRetake').addEventListener('click', function() {
            guruTempBlob = null;
            startGuruCamera(guruFacingMode);
        });

        document.getElementById('guruCamUse').addEventListener('click', function() {
            if (!guruTempBlob) return;
            const file = new File([guruTempBlob], `guru-foto-${Date.now()}.jpg`, { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            fotoInput.files = dt.files;

            previewImg.src = URL.createObjectURL(guruTempBlob);
            previewWrapper.classList.remove('hidden');
            closeGuruCamModal();
            Swal.fire({ icon: 'success', title: 'Foto Siap Digunakan', timer: 1200, showConfirmButton: false });
        });

        // ==========================================
        // 3. SELECT2: Pencarian Siswa (NIS/Nama)
        // ==========================================
        $('#siswa_select').select2({
            placeholder: 'Ketik NIS atau nama siswa contoh : 4717',
            allowClear: true,
            ajax: {
                url: '{{ route("guru.pengajuan.search-siswa") }}',
                dataType: 'json',
                delay: 300,
                data: function (params) { return { q: params.term || '' }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: function(item) { return item.text; },
            templateSelection: function(item) {
                if (!item.id) return item.text;
                if (item.nama) {
                    return item.nama + ' | NIS: ' + (item.nis || '-') + ' | ' + (item.kelas || '-');
                }
                return item.text;
            }
        });

        // ==========================================
        // 4. COUNTER KARAKTER ALASAN
        // ==========================================
        const alasan = document.getElementById('alasan');
        const charCount = document.getElementById('charCount');
        const charCounter = document.getElementById('charCounter');

        function updateCharCounter() {
            if (!alasan || !charCount || !charCounter) return;
            const length = alasan.value.length;
            charCount.textContent = length;
            if (length < 10) {
                charCounter.classList.remove('text-emerald-600');
                charCounter.classList.add('text-red-500');
            } else {
                charCounter.classList.remove('text-red-500');
                charCounter.classList.add('text-emerald-600');
            }
        }

        if (alasan) {
            alasan.addEventListener('input', updateCharCounter);
            updateCharCounter();
        }

        // ==========================================
        // 5. BATASI JAM KELUAR BERDASARKAN JADWAL DINAMIS
        // ==========================================
        const jamKeluarSelect = document.getElementById('jam_keluar');
        const dayOfWeek = {{ now()->dayOfWeek }};
        const maxJam = {{ $maxJam ?? 10 }};
        const jadwalPelajaran = @json($jadwalPelajaran ?? ['regular' => [], 'friday' => []]);

        function getCurrentLessonHour() {
            const now = new Date();
            const currentTime = now.getHours() * 60 + now.getMinutes();

            // Gunakan jadwal dinamis berdasarkan hari (Jumat atau Regular)
            const jadwalHariIni = (dayOfWeek === 5) ? jadwalPelajaran.friday : jadwalPelajaran.regular;

            let currentLesson = 1;
            for (const [jam, waktu] of Object.entries(jadwalHariIni)) {
                if (!waktu || !waktu.start || !waktu.end) continue;

                const [startH, startM] = waktu.start.split(':').map(Number);
                const [endH, endM] = waktu.end.split(':').map(Number);
                const startMinutes = startH * 60 + startM;
                const endMinutes = endH * 60 + endM;

                if (currentTime >= startMinutes) {
                    if (currentTime <= endMinutes) {
                        currentLesson = parseInt(jam);
                    } else {
                        currentLesson = parseInt(jam) + 1;
                    }
                }
            }
            return Math.min(currentLesson, maxJam + 1);
        }

        function disablePastLessons() {
            if (!jamKeluarSelect) return;
            const currentLesson = getCurrentLessonHour();

            jamKeluarSelect.querySelectorAll('option').forEach(option => {
                const value = parseInt(option.value);
                if (option.value === '') return;

                const isPast = value < currentLesson;
                const isLimit = value > maxJam;

                if (isPast || isLimit) {
                    option.disabled = true;
                    option.classList.add('text-gray-400');
                    option.textContent = `Jam Pelajaran ke-${value} (${isLimit ? 'Tidak Tersedia' : 'Sudah Lewat'})`;
                } else {
                    option.disabled = false;
                    option.classList.remove('text-gray-400');
                    option.textContent = `Jam Pelajaran ke-${value}`;
                }
            });

            const currentValue = parseInt(jamKeluarSelect.value);
            if (!isNaN(currentValue) && (currentValue < currentLesson || currentValue > maxJam)) {
                jamKeluarSelect.value = '';
            }
        }

        // ==========================================
        // 6. DINAMISASI JAM KEMBALI
        // ==========================================
        const jamKembaliSelect = document.getElementById('jam_kembali');
        const infoJam = document.getElementById('infoJam');

        function updateJamKembaliOptions() {
            if (!jamKeluarSelect || !jamKembaliSelect) return;
            const keluarValue = parseInt(jamKeluarSelect.value);
            const defaultOption = jamKembaliSelect.querySelector('option[value=""]');

            if (isNaN(keluarValue) || keluarValue <= 0) {
                jamKembaliSelect.disabled = true;
                jamKembaliSelect.value = '';
                jamKembaliSelect.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed', 'opacity-60');
                jamKembaliSelect.classList.remove('bg-white', 'text-gray-900');
                if (defaultOption) defaultOption.textContent = '-- Pilih Jam Keluar Dulu --';
                if (infoJam) infoJam.classList.add('hidden');
                return;
            }

            jamKembaliSelect.disabled = false;
            jamKembaliSelect.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed', 'opacity-60');
            jamKembaliSelect.classList.add('bg-white', 'text-gray-900');
            if (defaultOption) defaultOption.textContent = '-- Pilih Jam Kembali --';

            let adaOptionAktif = false;
            jamKembaliSelect.querySelectorAll('option').forEach(option => {
                const val = parseInt(option.value);
                if (option.value === '') return;

                // ✅ PERBAIKAN: Gunakan maxJam dinamis, JANGAN hardcode angka 5
                const isInvalid = (val <= keluarValue) || (val > maxJam);

                if (isInvalid) {
                    option.disabled = true;
                    option.classList.add('text-gray-400');
                } else {
                    option.disabled = false;
                    option.classList.remove('text-gray-400');
                    adaOptionAktif = true;
                }
            });

            const kembaliValue = parseInt(jamKembaliSelect.value);
            if (!isNaN(kembaliValue) && kembaliValue <= keluarValue) {
                jamKembaliSelect.value = '';
            }

            if (infoJam) {
                infoJam.classList.remove('hidden');
                const spanInfo = infoJam.querySelector('span');
                // ✅ PERBAIKAN: Tampilkan maxJam dinamis di pesan info
                if (dayOfWeek === 5) {
                    spanInfo.textContent = `Jam kembali harus lebih dari Jam ke-${keluarValue} (Maksimal Jam ke-${maxJam} untuk hari Jumat)`;
                } else {
                    spanInfo.textContent = `Jam kembali harus lebih dari Jam Pelajaran ke-${keluarValue}`;
                }
            }
        }

        if (jamKeluarSelect) {
            jamKeluarSelect.addEventListener('change', updateJamKembaliOptions);
            jamKeluarSelect.addEventListener('input', updateJamKembaliOptions);
            disablePastLessons();
            updateJamKembaliOptions();
        }

        // ==========================================
        // 7. TIME RESTRICTION CHECKER (REALTIME)
        // ==========================================
        function checkDispensasiTime() {
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const wib = new Date(utc + (3600000 * 7));
            const dayOfWeek = wib.getDay();
            const hours = wib.getHours();
            const minutes = wib.getMinutes();
            const currentTime = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            const currentMinutes = hours * 60 + minutes; // ✅ Parse ke menit

            const banner = document.getElementById('timeWarningBanner');
            const message = document.getElementById('timeWarningMessage');
            const timeDisplay = document.getElementById('currentTimeDisplay');
            const form = document.getElementById('formDispensasi');
            const submitBtn = document.getElementById('submitBtn');

            if (timeDisplay) {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                timeDisplay.textContent = `${days[dayOfWeek]}, ${currentTime} WIB`;
            }

            const settings = @json($settings);
            let isAllowed = true;
            let restrictionMsg = '';

            // Helper: parse "HH:MM" ke menit
            function timeToMinutes(timeStr) {
                if (!timeStr) return 0;
                const parts = timeStr.split(':');
                if (parts.length !== 2) return 0;
                return parseInt(parts[0]) * 60 + parseInt(parts[1]);
            }

            // 1. Cek Hari
            if (!settings.allowed_days || !settings.allowed_days.includes(dayOfWeek)) {
                isAllowed = false;
                restrictionMsg = 'Pengajuan dispensasi tidak diizinkan pada hari ini berdasarkan pengaturan sekolah.';
            } else {
                // 2. Cek Jam (dengan perbandingan MENIT!)
                const startMinutes = timeToMinutes(settings.start_time);
                const endMinutes = timeToMinutes((dayOfWeek === 5) ? settings.end_time_friday : settings.end_time);

                if (currentMinutes < startMinutes || currentMinutes > endMinutes) {
                    isAllowed = false;
                    restrictionMsg = `Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>${settings.start_time} - ${(dayOfWeek === 5) ? settings.end_time_friday : settings.end_time} WIB</strong>.`;
                }
            }

            if (!isAllowed) {
                if (banner) banner.classList.remove('hidden');
                if (message) message.innerHTML = restrictionMsg;

                if (form) {
                    form.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = true;
                        el.classList.add('opacity-50', 'cursor-not-allowed');
                    });
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    // ✅ JANGAN gunakan innerHTML di sini, biarkan Alpine.js mengaturnya
                    submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                    submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                }

                if (document.getElementById('btnBatal')) {
                    document.getElementById('btnBatal').classList.add('pointer-events-none', 'opacity-50');
                }
            } else {
                if (banner) banner.classList.add('hidden');

                if (form) {
                    form.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = false;
                        el.classList.remove('opacity-50', 'cursor-not-allowed');
                    });
                    updateJamKembaliOptions();
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    // ✅ JANGAN gunakan innerHTML di sini, biarkan Alpine.js mengaturnya
                    submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }

                if (document.getElementById('btnBatal')) {
                    document.getElementById('btnBatal').classList.remove('pointer-events-none', 'opacity-50');
                }
            }
        }

        checkDispensasiTime();
        setInterval(checkDispensasiTime, 60000);
    });
    </script>
    @endpush
    @endsection
