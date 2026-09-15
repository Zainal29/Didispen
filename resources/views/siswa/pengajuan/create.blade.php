@extends('siswa.layouts.app')
@section('title', 'Buat Pengajuan')
@section('page-title', 'Form Pengajuan Dispensasi')
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
                <h3 class="text-base font-bold text-gray-900">Form Pengajuan Dispensasi</h3>
                <p class="text-xs text-gray-500">Isi formulir berikut dengan data yang valid & jujur.</p>
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
                    Pengajuan dispensasi harus disetujui oleh <strong>Guru Piket</strong> sebelum Anda diizinkan meninggalkan area sekolah.
                </p>
            </div>

            <form method="POST" action="{{ route('siswa.pengajuan.store') }}" enctype="multipart/form-data" id="formDispensasi" class="space-y-5">
                @csrf

                {{-- Data Siswa --}}
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-3">Data Siswa</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">Nama Lengkap</span>
                            <span class="font-semibold text-gray-900">{{ $siswa->nama_lengkap }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">NIS / NISN</span>
                            <span class="font-semibold text-gray-900 font-mono">{{ $siswa->user->nis_nip ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">Kelas</span>
                            <span class="font-semibold text-gray-900">
                                {{ $siswa->kelas->nama_kelas ?? '-' }}
                                @if($siswa->kelas && $siswa->kelas->jurusan)
                                    — {{ $siswa->kelas->jurusan->nama_jurusan }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Info Guru Piket --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3.5 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-900">Guru Piket</p>
                        <p class="text-xs text-blue-700">Tercatat otomatis saat pengajuan disetujui</p>
                    </div>
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

                {{-- Foto Verifikasi Selfie (Live Kamera Langsung) --}}
                <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-bold text-blue-900 flex items-center gap-2">
                            <i class="fas fa-camera text-blue-600"></i>Foto Verifikasi Selfie <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span> Kamera Langsung
                        </span>
                    </div>
                    <p class="text-xs text-blue-700 mb-4">
                        Wajib mengambil foto selfie langsung saat ini untuk verifikasi oleh Satpam. Pengambilan dari file/galeri dilarang untuk mencegah penyalahgunaan dispensasi.
                    </p>

                    {{-- Hidden file input for form submission --}}
                    <input type="file" name="foto_verifikasi" id="foto_verifikasi_input" class="hidden" accept="image/*">

                    {{-- State 1: Standby Box --}}
                    <div id="selfieStandbyBox" class="border-2 border-dashed border-blue-300 rounded-xl p-6 text-center bg-white transition-all">
                        <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mb-3 shadow-inner">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <p class="font-bold text-gray-800 text-sm mb-1">Ambil Foto Selfie Sekarang</p>
                        <p class="text-xs text-gray-500 mb-4 max-w-xs mx-auto">
                            Klik tombol di bawah untuk membuka kamera dan mengambil foto wajah Anda secara langsung.
                        </p>
                        <button type="button" id="btnStartSelfie" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-lg text-xs font-bold shadow transition-all">
                            <i class="fas fa-video mr-2"></i> Buka Kamera Selfie
                        </button>
                    </div>

                    {{-- State 2: Live Viewfinder --}}
                    <div id="selfieCameraBox" class="hidden">
                        <div class="relative rounded-xl overflow-hidden bg-black aspect-[4/3] max-h-80 mx-auto shadow-inner border border-gray-300">
                            {{-- Mirrored Video Feed for Selfie --}}
                            <video id="selfieVideo" autoplay playsinline muted class="w-full h-full object-cover -scale-x-100"></video>

                            <div class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE KAMERA
                            </div>

                            <button type="button" id="btnFlipSelfie" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xs transition-colors shadow" title="Putar Kamera">
                                <i class="fas fa-camera-rotate"></i>
                            </button>

                            {{-- Subtle Face Oval Guide --}}
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-44 h-56 border-2 border-dashed border-white/50 rounded-full"></div>
                            </div>

                            {{-- Loading overlay --}}
                            <div id="selfieCameraLoading" class="absolute inset-0 bg-gray-900 flex flex-col items-center justify-center text-white hidden">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-400"></i>
                                <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" id="btnCancelSelfie" class="px-4 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold text-xs transition-colors">
                                Batal
                            </button>
                            <button type="button" id="btnSnapSelfie" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow flex items-center justify-center gap-2 transition-all">
                                <i class="fas fa-circle-dot text-base"></i> Jepret Foto Selfie
                            </button>
                        </div>
                    </div>

                    {{-- State 3: Captured Preview --}}
                    <div id="selfiePreviewBox" class="hidden">
                        <div class="relative rounded-xl overflow-hidden border-2 border-emerald-500 aspect-[4/3] max-h-80 mx-auto bg-gray-900 shadow">
                            <img id="selfiePreviewImg" class="w-full h-full object-cover" alt="Preview Selfie">
                            <div class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow">
                                <i class="fas fa-check-circle"></i> FOTO TERVERIFIKASI
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-900">Selfie Berhasil Diambil</p>
                                    <p class="text-[11px] text-emerald-700" id="selfieTimestampText"></p>
                                </div>
                            </div>
                            <button type="button" id="btnRetakeSelfie" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold shadow-sm transition-colors">
                                <i class="fas fa-rotate-left mr-1.5"></i> Foto Ulang
                            </button>
                        </div>
                    </div>

                    {{-- Camera Error Notice --}}
                    <div id="selfieErrorBox" class="hidden mt-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs">
                        <div class="flex items-start gap-2.5">
                            <i class="fas fa-triangle-exclamation text-red-600 text-base mt-0.5"></i>
                            <div>
                                <p class="font-bold text-red-900 mb-0.5">Kamera Tidak Dapat Diakses</p>
                                <p class="text-[11px] text-red-600 mb-2 leading-relaxed">
                                    Peramban memblokir atau tidak menemukan kamera. Mohon izinkan akses kamera di pengaturan peramban Anda untuk mengambil foto selfie langsung.
                                </p>
                                <button type="button" id="btnRetrySelfie" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-semibold inline-flex items-center gap-1">
                                    <i class="fas fa-rotate-right"></i> Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>

                    @error('foto_verifikasi') <p class="text-red-500 text-xs mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                </div>

                {{-- Tujuan & Lokasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tujuan <span class="text-red-500">*</span></label>
                        <input type="text" name="tujuan" value="{{ old('tujuan') }}" required placeholder="Contoh: Rumah Sakit" class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('tujuan') border-red-500 @enderror">
                        @error('tujuan') <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Jl. Merdeka No. 1" class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('lokasi') border-red-500 @enderror">
                        @error('lokasi') <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Keluar <span class="text-red-500">*</span></label>
                        <select name="jam_keluar" id="jamKeluar" required class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all @error('jam_keluar') border-red-500 @enderror">
                            <option value="">-- Pilih --</option>
                            @for($i = 1; $i <= $maxJam; $i++)
                                <option value="{{ $i }}" {{ old('jam_keluar') == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
                            @endfor
                        </select>
                        @error('jam_keluar') <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jam Kembali <span class="text-red-500">*</span></label>
                        <select name="jam_kembali" id="jamKembali" required disabled
                            class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-gray-100 text-sm text-gray-400 cursor-not-allowed focus:outline-none transition-all opacity-60 @error('jam_kembali') border-red-500 @enderror">
                            <option value="">-- Pilih Jam Keluar Dulu --</option>
                            @for($i = 1; $i <= $maxJam; $i++)
                                <option value="{{ $i }}" {{ old('jam_kembali') == $i ? 'selected' : '' }}>Jam ke-{{ $i }}</option>
                            @endfor
                        </select>
                        @error('jam_kembali') <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                       <p id="infoJam" class="text-xs text-gray-500 mt-1.5 hidden"><i class="fas fa-info-circle mr-1"></i><span></span></p>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="pt-4 flex flex-col sm:flex-row gap-3 border-t border-gray-100">
                    <button type="submit" id="submitBtn" class="flex-1 inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                    </button>
                    <a href="{{ route('siswa.pengajuan.index') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
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
    // ==========================================
    // LOGIKA LIVE KAMERA SELFIE VERIFIKASI
    // ==========================================
    let selfieStream = null;
    let currentFacingModeSelfie = 'user';

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
    const formDispensasi = document.getElementById('formDispensasi');

    function stopSelfieCamera() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
            selfieStream = null;
        }
    }

    async function startSelfieCamera(facingMode = 'user') {
        stopSelfieCamera();
        selfieErrorBox.classList.add('hidden');
        selfieStandbyBox.classList.add('hidden');
        selfiePreviewBox.classList.add('hidden');
        selfieCameraBox.classList.remove('hidden');
        selfieCameraLoading.classList.remove('hidden');

        try {
            const constraints = {
                video: {
                    facingMode: { ideal: facingMode },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };
            selfieStream = await navigator.mediaDevices.getUserMedia(constraints);
            selfieVideo.srcObject = selfieStream;
            await selfieVideo.play();
            selfieCameraLoading.classList.add('hidden');
        } catch (err) {
            console.warn('Gagal membuka facingMode ideal, mencoba default:', err);
            try {
                selfieStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                selfieVideo.srcObject = selfieStream;
                await selfieVideo.play();
                selfieCameraLoading.classList.add('hidden');
            } catch (fallbackErr) {
                console.error('Kamera selfie gagal dibuka:', fallbackErr);
                selfieCameraLoading.classList.add('hidden');
                selfieCameraBox.classList.add('hidden');
                selfieErrorBox.classList.remove('hidden');
            }
        }
    }

    btnStartSelfie.addEventListener('click', function() {
        startSelfieCamera(currentFacingModeSelfie);
    });

    if (btnRetrySelfie) {
        btnRetrySelfie.addEventListener('click', function() {
            startSelfieCamera(currentFacingModeSelfie);
        });
    }

    btnCancelSelfie.addEventListener('click', function() {
        stopSelfieCamera();
        selfieCameraBox.classList.add('hidden');
        selfieStandbyBox.classList.remove('hidden');
    });

    btnFlipSelfie.addEventListener('click', function() {
        currentFacingModeSelfie = (currentFacingModeSelfie === 'user') ? 'environment' : 'user';
        if (currentFacingModeSelfie === 'environment') {
            selfieVideo.classList.remove('-scale-x-100');
        } else {
            selfieVideo.classList.add('-scale-x-100');
        }
        startSelfieCamera(currentFacingModeSelfie);
    });

    btnSnapSelfie.addEventListener('click', function() {
        if (!selfieVideo.videoWidth) {
            Swal.fire('Mohon Tunggu', 'Kamera sedang menginisialisasi...', 'info');
            return;
        }

        const width = selfieVideo.videoWidth;
        const height = selfieVideo.videoHeight;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // Mirror jika menghadap ke pengguna
        if (currentFacingModeSelfie === 'user') {
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(selfieVideo, 0, 0, width, height);
            ctx.setTransform(1, 0, 0, 1, 0, 0);
        } else {
            ctx.drawImage(selfieVideo, 0, 0, width, height);
        }

        // Stempel Waktu Real-Time
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
        const stampText = `SELFIE VERIFIKASI • ${dateStr}`;

        const fontSize = Math.max(13, Math.round(width / 40));
        ctx.font = `bold ${fontSize}px sans-serif`;
        const textWidth = ctx.measureText(stampText).width;
        const boxWidth = textWidth + 24;
        const boxHeight = fontSize + 16;
        const posX = width - boxWidth - 14;
        const posY = height - boxHeight - 14;

        ctx.fillStyle = 'rgba(0, 0, 0, 0.72)';
        ctx.beginPath();
        ctx.roundRect(posX, posY, boxWidth, boxHeight, 6);
        ctx.fill();

        ctx.fillStyle = '#3b82f6';
        ctx.beginPath();
        ctx.arc(posX + 12, posY + boxHeight / 2, 4, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.fillText(stampText, posX + 22, posY + boxHeight / 2 + fontSize / 3);

        canvas.toBlob(function(blob) {
            const file = new File([blob], `selfie-verifikasi-${Date.now()}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fotoInput.files = dataTransfer.files;

            selfiePreviewImg.src = URL.createObjectURL(blob);
            selfieTimestampText.textContent = dateStr;
            selfieCameraBox.classList.add('hidden');
            selfiePreviewBox.classList.remove('hidden');
            stopSelfieCamera();
        }, 'image/jpeg', 0.85);
    });

    btnRetakeSelfie.addEventListener('click', function() {
        fotoInput.value = '';
        selfiePreviewBox.classList.add('hidden');
        startSelfieCamera(currentFacingModeSelfie);
    });

    // Validasi form saat submit: pastikan foto selfie sudah diambil
    formDispensasi.addEventListener('submit', function(e) {
        if (!fotoInput.files || fotoInput.files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Foto Selfie Diperlukan',
                text: 'Harap ambil foto selfie verifikasi langsung menggunakan kamera terlebih dahulu.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Buka Kamera'
            }).then(() => {
                btnStartSelfie.click();
                selfieStandbyBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
            return false;
        }
    });

    window.addEventListener('beforeunload', stopSelfieCamera);

    // ==========================================
    // LOGIKA JAM PELAJARAN & FORM DISPENSASI (DISINKRONKAN DENGAN GURU)
    // ==========================================
    // Perhatikan: ID di HTML siswa adalah 'jamKeluar' dan 'jamKembali' (tanpa underscore)
    const jamKeluarSelect = document.getElementById('jamKeluar');
    const jamKembaliSelect = document.getElementById('jamKembali');
    const infoJam = document.getElementById('infoJam');

    const dayOfWeek = new Date().getDay();
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
                option.textContent = `Jam ke-${value} (${isLimit ? 'Tidak Tersedia' : 'Sudah Lewat'})`;
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
                option.textContent = `Jam ke-${value}`;
            }
        });

        const currentValue = parseInt(jamKeluarSelect.value);
        if (!isNaN(currentValue) && (currentValue < currentLesson || currentValue > maxJam)) {
            jamKeluarSelect.value = '';
        }
    }

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

        jamKembaliSelect.querySelectorAll('option').forEach(option => {
            const val = parseInt(option.value);
            if (option.value === '') return;

            // ✅ SINKRON: Gunakan maxJam dinamis dari Admin, bukan hardcode
            const isInvalid = (val <= keluarValue) || (val > maxJam);
            if (isInvalid) {
                option.disabled = true;
                option.classList.add('text-gray-400');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
            }
        });

        const kembaliValue = parseInt(jamKembaliSelect.value);
        if (!isNaN(kembaliValue) && kembaliValue <= keluarValue) {
            jamKembaliSelect.value = '';
        }

        if (infoJam) {
            infoJam.classList.remove('hidden');
            infoJam.querySelector('span').textContent = `Jam kembali harus lebih dari Jam ke-${keluarValue}`;
        }
    }

    // Jalankan saat halaman dimuat
    if (jamKeluarSelect) {
        jamKeluarSelect.addEventListener('change', updateJamKembaliOptions);
        jamKeluarSelect.addEventListener('input', updateJamKembaliOptions);
        disablePastLessons();
        updateJamKembaliOptions();
    }

    const alasan = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    const charCounter = document.getElementById('charCounter');
    if (alasan) {
        alasan.addEventListener('input', function() {
            const length = alasan.value.length;
            if (charCount) charCount.textContent = length;
            if (charCounter) {
                if (length < 10) { charCounter.classList.remove('text-emerald-600'); charCounter.classList.add('text-red-500'); }
                else { charCounter.classList.remove('text-red-500'); charCounter.classList.add('text-emerald-600'); }
            }
        });
    }

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
            // 2. Cek Jam (dengan perbandingan MENIT, bukan string!)
            const startMinutes = timeToMinutes(settings.start_time);
            const endMinutes = timeToMinutes((dayOfWeek === 5) ? settings.end_time_friday : settings.end_time);

            if (currentMinutes < startMinutes || currentMinutes > endMinutes) {
                isAllowed = false;
                const jamTutup = (dayOfWeek === 5) ? settings.end_time_friday : settings.start_time;
                restrictionMsg = `Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>${settings.start_time} - ${(dayOfWeek === 5) ? settings.end_time_friday : settings.end_time} WIB</strong>.`;
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
                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            }
        } else {
            if (banner) banner.classList.add('hidden');
            if (form) {
                form.querySelectorAll('input, select, textarea, button').forEach(el => {
                    if (el.id !== 'submitBtn' && el.id !== 'jamKembali') {
                        el.disabled = false;
                        el.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                });
                updateJamKembaliOptions();
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan';
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }
        }
    }
    checkDispensasiTime();
    setInterval(checkDispensasiTime, 60000);
});
</script>
@endpush
@endsection
