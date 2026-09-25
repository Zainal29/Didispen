@extends('siswa.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Siswa')
@section('content')

{{-- ============ HERO (compact, solid color) ============ --}}
<div class="bg-blue-600 rounded-xl p-4 sm:p-6 mb-4">
    <p class="text-blue-100 text-xs font-medium uppercase tracking-wide">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    <h2 class="text-lg sm:text-xl font-bold text-white mt-1">Halo, {{ auth()->user()->name }}!</h2>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-700 text-white text-xs font-medium">
            <i class="fas fa-bell mr-1.5 text-yellow-300"></i>{{ $notifikasiBelumDibaca ?? 0 }} Notifikasi Baru
        </span>
        <a href="{{ route('siswa.pengajuan.create') }}"
           class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-white text-blue-700 text-xs font-semibold hover:bg-blue-50 transition-colors">
            <i class="fas fa-plus mr-1.5"></i>Buat Pengajuan
        </a>
    </div>
</div>

{{-- ✅ TAMBAHKAN INI: Banner Terlambat (Hanya muncul jika terlambat) --}}
@if($isTerlambat) {{-- ✅ BENAR: Sesuai dengan variabel di DashboardController --}}
<div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 sm:p-5 mb-4 animate-pulse">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-red-900 font-bold text-sm mb-1">Peringatan: Anda Terlambat!</h3>
            <p class="text-red-700 text-sm mb-2">
                Anda telah melewati batas waktu kembali dispensasi
                <strong class="text-red-900">({{ $dispensasiAktif->nomor_surat }})</strong>
                selama
                <span class="bg-red-100 px-2 py-0.5 rounded font-semibold">
                    @if($terlambatJam > 0)
                        {{ $terlambatJam }} jam {{ $terlambatMenit }} menit
                    @else
                        {{ $terlambatMenit }} menit
                    @endif
                </span>
            </p>
            <p class="text-xs text-red-600 mb-3">
                <i class="far fa-clock mr-1"></i>
                Batas kembali: <strong>{{ \Carbon\Carbon::parse($dispensasiAktif->batas_waktu_kembali)->format('H:i') }} WIB</strong>
            </p>
            <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
               class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <i class="fas fa-qrcode mr-1.5"></i>Lihat QR Code
            </a>
        </div>
    </div>
</div>
@endif

{{-- ============ STATUS DISPENSASI AKTIF ============ --}}
@if(isset($dispensasiAktif))
    {{-- KONDISI 1: DISETUJUI --}}
    @if($dispensasiAktif->status === 'disetujui')
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                    Aktif
                </span>
                <h3 class="text-sm font-semibold text-gray-900">
                    <i class="fas fa-check-circle text-emerald-600 mr-1.5"></i>Dispensasi Disetujui
                </h3>
            </div>
        </div>
        <div class="space-y-2 text-xs">
            <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">No. Surat</p>
                <p class="font-mono font-semibold text-gray-900 truncate">{{ $dispensasiAktif->nomor_surat }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Waktu</p>
                <p class="font-semibold text-gray-900">{{ $dispensasiAktif->jam_keluar }} – {{ $dispensasiAktif->jam_kembali }}</p>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
               class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <i class="fas fa-qrcode mr-1.5"></i>Lihat QR Code
            </a>
            @if($dispensasiAktif->student_print_count < 15)
            <a href="{{ route('siswa.cetak', $dispensasiAktif) }}"
               target="_blank"
               class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <i class="fas fa-print mr-1.5"></i>Cetak
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- KONDISI 2: SUDAH KELUAR --}}
    @if($dispensasiAktif->status === 'keluar')
    <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-sky-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-walking text-sky-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-sky-900 text-sm">Anda Sedang di Luar Sekolah</h3>
                <p class="text-sm text-sky-700 mt-1">
                    Keluar pukul: {{ $dispensasiAktif->waktu_keluar_aktual ? \Carbon\Carbon::parse($dispensasiAktif->waktu_keluar_aktual)->format('H:i') : '-' }} WIB
                </p>
                <p class="text-xs text-sky-600 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Harap kembali sebelum pukul {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasiAktif->jam_kembali) }}
                </p>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- BAGIAN FOTO BUKTI (DENGAN KAMERA LANGSUNG) --}}
        {{-- ========================================== --}}
        <div class="mt-4 pt-4 border-t border-sky-200">
            <p class="text-xs font-bold text-sky-900 mb-2 flex items-center gap-1.5">
                <i class="fas fa-camera"></i> Foto Bukti Kegiatan
            </p>

            @if($dispensasiAktif->foto_bukti)
                {{-- ✅ FOTO SUDAH DIUPLOAD --}}
                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/' . $dispensasiAktif->foto_bukti) }}"
                             class="w-12 h-12 object-cover rounded-lg border border-gray-200 cursor-pointer hover:scale-105 transition-transform"
                             onclick="showPreview('{{ asset('storage/' . $dispensasiAktif->foto_bukti) }}', '{{ $dispensasiAktif->nomor_surat }}')">
                        <div>
                            <p class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Foto Bukti Terkirim
                            </p>
                            <p class="text-[10px] text-gray-500">
                                Diupload: {{ $dispensasiAktif->foto_bukti_uploaded_at ? \Carbon\Carbon::parse($dispensasiAktif->foto_bukti_uploaded_at)->format('H:i') : '-' }} WIB
                            </p>
                        </div>
                    </div>
                    <button onclick="hapusFoto({{ $dispensasiAktif->id }})"
                            class="text-red-500 hover:text-red-700 p-1.5 rounded-md hover:bg-red-50 transition-colors"
                            title="Hapus Foto">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            @else
                {{-- ❌ FOTO BELUM DIUPLOAD --}}
                <div class="bg-white rounded-lg p-3 border {{ $isTerlambat ? 'border-red-300 bg-red-50' : 'border-amber-200 bg-amber-50/50' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold {{ $isTerlambat ? 'text-red-700' : 'text-amber-700' }} flex items-center gap-1.5">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $isTerlambat ? 'Wajib Upload Foto Bukti!' : 'Belum ada foto bukti' }}
                            </p>
                            <p class="text-[10px] text-gray-500 mt-0.5">
                                Ambil foto langsung menggunakan kamera sebagai bukti
                            </p>
                        </div>
                        <button onclick="openUploadModal({{ $dispensasiAktif->id }})"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5 {{ $isTerlambat ? 'animate-pulse' : '' }}">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                    </div>
                </div>
            @endif

            {{-- Tombol QR Code --}}
            <div class="mt-3">
                <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
                   class="w-full inline-flex items-center justify-center px-3 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                    <i class="fas fa-qrcode mr-1.5"></i>Lihat QR Code (Untuk Scan Kembali)
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- KONDISI 3: SUDAH SELESAI --}}
    @if($dispensasiAktif->status === 'selesai')
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-emerald-900 text-sm">Dispensasi Selesai</h3>
                <p class="text-sm text-emerald-700 mt-1">
                    Anda telah kembali ke sekolah pukul {{ $dispensasiAktif->waktu_kembali_aktual ? \Carbon\Carbon::parse($dispensasiAktif->waktu_kembali_aktual)->format('H:i') : '-' }} WIB.
                </p>
            </div>
        </div>
    </div>
    @endif
@endif

{{-- ============ STATISTIK UTAMA ============ --}}
@php
$cards = [
    'menunggu'  => ['Menunggu',  $stats['menunggu'] ?? 0,  'fa-clock',          'amber'],
    'disetujui' => ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle',   'emerald'],
    'ditolak'   => ['Ditolak',   $stats['ditolak'] ?? 0,   'fa-times-circle',   'red'],
    'selesai'   => ['Selesai',   $stats['selesai'] ?? 0,   'fa-check-double',   'blue'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-3">
    @foreach($cards as $key => [$label, $value, $icon, $color])
    <a href="{{ route('siswa.pengajuan.index', ['status' => $key]) }}"
       class="stat-card-btn text-left rounded-xl border border-gray-200 bg-white p-3 sm:p-4 min-h-[44px] hover:border-gray-300 hover:shadow-sm transition-all w-full block">
        <div class="flex items-center justify-between mb-2">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-{{ $color }}-100 text-{{ $color }}-600">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
            <span class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $value }}</span>
        </div>
        <p class="text-[11px] sm:text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ $label }}</p>
    </a>
    @endforeach
</div>

{{-- SECONDARY BUTTON: SEMUA PENGAJUAN --}}
<div class="mb-4">
    <a href="{{ route('siswa.pengajuan.index') }}"
       class="filter-btn w-full px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-lg text-xs font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all text-center">
        <i class="fas fa-layer-group mr-1.5 text-blue-600"></i>Tampilkan Semua Pengajuan ({{ $stats['total'] ?? 0 }})
    </a>
</div>

{{-- ============ PENGAJUAN TERBARU ============ --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-4 py-3 sm:px-5 sm:py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-900">
            <i class="fas fa-history mr-1.5 text-gray-400"></i>Pengajuan Terbaru
        </h3>
        <a href="{{ route('siswa.pengajuan.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    @if(isset($pengajuanTerbaru) && $pengajuanTerbaru->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($pengajuanTerbaru as $pengajuan)
                @php
                    $badges = [
                        'menunggu'  => 'bg-amber-100 text-amber-700',
                        'disetujui' => 'bg-emerald-100 text-emerald-700',
                        'ditolak'   => 'bg-red-100 text-red-700',
                        'keluar'    => 'bg-sky-100 text-sky-700',
                        'selesai'   => 'bg-gray-100 text-gray-700',
                    ];
                    $maxPrint = \App\Helpers\PrintHelper::maxStudentLimit();
                    $canPrint = in_array($pengajuan->status, \App\Helpers\PrintHelper::PRINTABLE_STATUSES) &&
                                $pengajuan->print_count < $maxPrint;
                    $currentTime = \App\Helpers\PrintHelper::currentTime();
                    $startTime = \App\Helpers\PrintHelper::startTime();
                    $endTime = \App\Helpers\PrintHelper::endTime();
                    $isWithinTime = \App\Helpers\PrintHelper::isWithinOperatingHours($currentTime);
                    $canPrint = $canPrint && $isWithinTime;
                @endphp
                <div class="p-4 sm:p-5 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start gap-3 mb-2">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-mono font-semibold text-gray-900 text-xs truncate">{{ $pengajuan->nomor_surat }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $pengajuan->created_at->format('d M Y, H:i') }} •
                                <span class="capitalize">{{ str_replace('_', ' ', $pengajuan->kategori) }}</span>
                            </p>
                            <p class="text-xs text-gray-600 mt-1 truncate">
                                <span class="font-medium text-gray-700">Tujuan:</span> {{ $pengajuan->tujuan }}
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold flex-shrink-0 {{ $badges[$pengajuan->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($pengajuan->status) }}
                        </span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('siswa.pengajuan.show', $pengajuan) }}"
                           class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-1.5"></i>Detail
                        </a>
                        @if(in_array($pengajuan->status, ['disetujui', 'keluar']))
                            @if($pengajuan->print_count < $maxPrint && $isWithinTime)
                                <a href="{{ route('siswa.cetak', $pengajuan) }}" target="_blank"
                                   class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-print mr-1.5"></i>Cetak
                                </a>
                            @else
                                <button disabled
                                        class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-400 bg-gray-100 cursor-not-allowed"
                                        title="{{ $pengajuan->print_count >= $maxPrint ? 'Batas cetak tercapai (' . $maxPrint . ' kali)' : 'Di luar jam cetak (' . $startTime . ' - ' . $endTime . ' WIB)' }}">
                                    <i class="fas fa-lock mr-1.5"></i>Cetak
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8 sm:p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                <i class="fas fa-inbox"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm mb-1">Belum ada pengajuan dispensasi</p>
            <p class="text-xs text-gray-500 mb-4">Buat pengajuan pertama Anda untuk memulai.</p>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-1.5"></i>Buat Pengajuan Pertama
            </a>
        </div>
    @endif
</div>


{{-- ========================================== --}}
{{-- MODAL AMBIL FOTO BUKTI LANGSUNG KAMERA    --}}
{{-- ========================================== --}}
<div id="uploadModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Ambil Foto Bukti</h3>
                    <p class="text-[11px] text-gray-500">Kamera langsung aktif saat ini</p>
                </div>
            </div>
            <button type="button" onclick="closeUploadModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="uploadForm" class="p-5 flex flex-col gap-4">
            @csrf
            <input type="hidden" id="dispensasiId">

            <div class="relative w-full rounded-xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center border border-gray-300 shadow-inner">
                <video id="videoBukti" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <img id="previewImgBukti" class="w-full h-full object-cover hidden" alt="Preview Foto Bukti">
                <canvas id="canvasBukti" class="hidden"></canvas>

                <div id="liveBadgeBukti" class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span> LIVE KAMERA
                </div>

                <button type="button" id="btnFlipBukti" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xs transition-colors shadow" title="Putar Kamera">
                    <i class="fas fa-camera-rotate"></i>
                </button>

                <div id="cameraLoadingBukti" class="absolute inset-0 bg-gray-900 flex flex-col items-center justify-center text-white hidden">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 text-emerald-400"></i>
                    <p class="text-xs font-semibold">Mengaktifkan kamera...</p>
                </div>

                <div id="cameraErrorBukti" class="absolute inset-0 bg-gray-950/95 p-5 flex flex-col items-center justify-center text-center text-white hidden">
                    <div class="w-12 h-12 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-2">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <p class="text-xs font-bold mb-1">Kamera Tidak Dapat Diakses</p>
                    <p class="text-[11px] text-gray-300 mb-3 max-w-xs leading-relaxed">Pastikan izin akses kamera telah diizinkan pada peramban Anda.</p>
                    <button type="button" onclick="retryBuktiCamera()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1">
                        <i class="fas fa-rotate-right"></i> Coba Lagi
                    </button>
                </div>
            </div>

            <div class="text-[11px] text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-2.5 flex items-center gap-2">
                <i class="fas fa-shield-halved text-emerald-600 text-sm flex-shrink-0"></i>
                <span>Foto diambil seketika dengan stempel waktu otomatis untuk mencegah penggunaan foto lama.</span>
            </div>

            <div id="controlsCameraBukti" class="flex gap-2">
                <button type="button" onclick="closeUploadModal()" class="w-1/3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs transition-colors">Batal</button>
                <button type="button" id="btnSnapBukti" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow flex items-center justify-center gap-2 transition-all">
                    <i class="fas fa-camera text-sm"></i> Jepret Foto Bukti
                </button>
            </div>

            <div id="controlsPreviewBukti" class="hidden flex gap-2">
                <button type="button" id="btnRetakeBukti" class="w-1/2 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-rotate-left"></i> Foto Ulang
                </button>
                <button type="submit" id="btnSubmitBukti" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-xs shadow flex items-center justify-center gap-1.5 transition-colors">
                    <i class="fas fa-cloud-arrow-up"></i> Upload Foto
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>

let buktiStream = null;
let currentFacingModeBukti = 'environment';
let capturedBuktiBlob = null;

const videoBukti = document.getElementById('videoBukti');
const previewImgBukti = document.getElementById('previewImgBukti');
const canvasBukti = document.getElementById('canvasBukti');
const liveBadgeBukti = document.getElementById('liveBadgeBukti');
const btnFlipBukti = document.getElementById('btnFlipBukti');
const cameraLoadingBukti = document.getElementById('cameraLoadingBukti');
const cameraErrorBukti = document.getElementById('cameraErrorBukti');
const controlsCameraBukti = document.getElementById('controlsCameraBukti');
const controlsPreviewBukti = document.getElementById('controlsPreviewBukti');
const btnSnapBukti = document.getElementById('btnSnapBukti');
const btnRetakeBukti = document.getElementById('btnRetakeBukti');
const btnSubmitBukti = document.getElementById('btnSubmitBukti');

function stopBuktiCamera() {
    if (buktiStream) { buktiStream.getTracks().forEach(track => track.stop()); buktiStream = null; }
}

async function startBuktiCamera(facingMode = 'environment') {
    stopBuktiCamera();
    cameraErrorBukti.classList.add('hidden');
    previewImgBukti.classList.add('hidden');
    videoBukti.classList.remove('hidden');
    liveBadgeBukti.classList.remove('hidden');
    controlsPreviewBukti.classList.add('hidden');
    controlsCameraBukti.classList.remove('hidden');
    cameraLoadingBukti.classList.remove('hidden');

    try {
        buktiStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
        videoBukti.srcObject = buktiStream;
        await videoBukti.play();
        cameraLoadingBukti.classList.add('hidden');
    } catch (err) {
        try {
            buktiStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            videoBukti.srcObject = buktiStream;
            await videoBukti.play();
            cameraLoadingBukti.classList.add('hidden');
        } catch (fallbackErr) {
            cameraLoadingBukti.classList.add('hidden');
            cameraErrorBukti.classList.remove('hidden');
        }
    }
}

function retryBuktiCamera() { startBuktiCamera(currentFacingModeBukti); }

function openUploadModal(id) {
    document.getElementById('dispensasiId').value = id;
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.remove('hidden');
    startBuktiCamera(currentFacingModeBukti);
}

function closeUploadModal() {
    stopBuktiCamera();
    capturedBuktiBlob = null;
    document.getElementById('uploadModal').classList.add('hidden');
}

btnFlipBukti.addEventListener('click', function() {
    currentFacingModeBukti = (currentFacingModeBukti === 'environment') ? 'user' : 'environment';
    startBuktiCamera(currentFacingModeBukti);
});

btnSnapBukti.addEventListener('click', function() {
    if (!videoBukti.videoWidth) return;
    const width = videoBukti.videoWidth, height = videoBukti.videoHeight;
    canvasBukti.width = width; canvasBukti.height = height;
    const ctx = canvasBukti.getContext('2d');

    if (currentFacingModeBukti === 'user') {
        ctx.translate(width, 0); ctx.scale(-1, 1); ctx.drawImage(videoBukti, 0, 0, width, height); ctx.setTransform(1, 0, 0, 1, 0, 0);
    } else { ctx.drawImage(videoBukti, 0, 0, width, height); }

    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
    const stampText = `DIGIPEN • BUKTI • ${dateStr}`;
    const fontSize = Math.max(13, Math.round(width / 42));
    ctx.font = `bold ${fontSize}px sans-serif`;
    const textWidth = ctx.measureText(stampText).width;
    const boxWidth = textWidth + 24, boxHeight = fontSize + 16;
    const posX = width - boxWidth - 14, posY = height - boxHeight - 14;

    ctx.fillStyle = 'rgba(0, 0, 0, 0.72)'; ctx.beginPath(); ctx.roundRect(posX, posY, boxWidth, boxHeight, 6); ctx.fill();
    ctx.fillStyle = '#10b981'; ctx.beginPath(); ctx.arc(posX + 12, posY + boxHeight / 2, 4, 0, Math.PI * 2); ctx.fill();
    ctx.fillStyle = '#ffffff'; ctx.fillText(stampText, posX + 22, posY + boxHeight / 2 + fontSize / 3);

    canvasBukti.toBlob(function(blob) {
        capturedBuktiBlob = blob;
        previewImgBukti.src = URL.createObjectURL(blob);
        previewImgBukti.classList.remove('hidden');
        videoBukti.classList.add('hidden');
        liveBadgeBukti.classList.add('hidden');
        controlsCameraBukti.classList.add('hidden');
        controlsPreviewBukti.classList.remove('hidden');
        stopBuktiCamera();
    }, 'image/jpeg', 0.85);
});

btnRetakeBukti.addEventListener('click', function() { capturedBuktiBlob = null; startBuktiCamera(currentFacingModeBukti); });

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('dispensasiId').value;
    if (!capturedBuktiBlob) return;

    const formData = new FormData();
    formData.append('foto_bukti', capturedBuktiBlob, `bukti-${Date.now()}.jpg`);
    btnSubmitBukti.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload...';
    btnSubmitBukti.disabled = true;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/upload-foto-bukti`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (data.success) { closeUploadModal(); location.reload(); }
        else { alert('Gagal: ' + (data.message || 'Terjadi kesalahan')); }
    } catch (err) { alert('Error jaringan'); }
    finally { btnSubmitBukti.innerHTML = '<i class="fas fa-cloud-arrow-up"></i> Upload Foto'; btnSubmitBukti.disabled = false; }
});

async function hapusFoto(id) {
    const result = await Swal.fire({
        title: 'Hapus Foto Bukti?',
        text: 'Foto bukti akan dihapus permanen. Anda harus upload ulang sebelum kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-l-lg',
            cancelButton: 'rounded-r-lg'
        }
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/siswa/pengajuan/${id}/hapus-foto-bukti`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Foto bukti berhasil dihapus.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat menghapus foto.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal menghapus foto. Periksa koneksi Anda.', 'error');
    }
}

function showPreview(url, nomorSurat) {
    // Jika SweetAlert (Swal) tersedia, gunakan modal gambar, jika tidak buka di tab baru
    if (typeof Swal !== 'undefined') {
        Swal.fire({ imageUrl: url, imageAlt: 'Foto Bukti ' + nomorSurat, showConfirmButton: false, background: '#fff', padding: '1rem' });
    } else {
        window.open(url, '_blank');
    }
}
</script>
@endpush


@endsection
