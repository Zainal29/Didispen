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

{{-- ============ PERINGATAN TERLAMBAT ============ --}}
@if(isset($isTerlambat) && $isTerlambat && $dispensasiAktif)
<div class="bg-red-50 border border-red-200 rounded-xl p-4 sm:p-5 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
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
        <div class="mt-3 pt-3 border-t border-sky-200">
            <a href="{{ route('siswa.pengajuan.show', $dispensasiAktif) }}"
               class="w-full inline-flex items-center justify-center px-3 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <i class="fas fa-qrcode mr-1.5"></i>Lihat QR Code (Untuk Scan Kembali)
            </a>
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

{{-- ============ STATISTIK ============ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
    @php
    $cards = [
        ['Total',     $stats['total'] ?? 0,     'fa-file-alt',       'text-gray-600'],
        ['Menunggu',  $stats['menunggu'] ?? 0,  'fa-clock',          'text-amber-600'],
        ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle',   'text-emerald-600'],
        ['Ditolak',   $stats['ditolak'] ?? 0,   'fa-times-circle',   'text-red-600'],
        ['Selesai',   $stats['selesai'] ?? 0,   'fa-flag-checkered', 'text-blue-600'],
    ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $color])
    <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
            <i class="fas {{ $icon }} {{ $color }} text-sm"></i>
        </div>
        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $value }}</h3>
    </div>
    @endforeach
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

@endsection
