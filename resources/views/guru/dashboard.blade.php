@extends('guru.layouts.app')
@section('title', 'Dashboard Guru Piket')
@section('page-title', 'Dashboard')
@section('content')

<style>
.stat-card-btn { transition: all 0.2s ease; }
.stat-card-btn:hover { transform: translateY(-1px); }
.stat-card-btn.active { transform: scale(1.01); }
#content-area { transition: opacity 0.2s ease; }
.fade-out { opacity: 0; }
.fade-in { opacity: 1; }
/* Dialog dispensasi: ringkas di desktop, nyaman dipakai di mobile. */
.swal2-popup.dispensasi-alert { border-radius: 1rem; padding: 1.5rem; }
.swal2-popup.dispensasi-alert .swal2-actions { width: 100%; gap: .625rem; margin-top: 1.5rem; }
.swal2-popup.dispensasi-alert .swal2-styled { min-height: 2.75rem; padding: .7rem 1rem; border-radius: .75rem; font-size: .875rem; font-weight: 700; box-shadow: none; }
.swal2-popup.dispensasi-alert .swal2-html-container { margin-top: .5rem; }
.swal2-popup.dispensasi-alert .swal2-textarea { box-sizing: border-box; min-height: 6.5rem; margin: 1rem 0 0; border-radius: .75rem; border-color: #d1d5db; font-size: .875rem; }
.swal2-popup.dispensasi-alert .swal2-textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
@media (max-width: 639px) {
    .swal2-popup.dispensasi-alert { width: calc(100% - 2rem) !important; padding: 1.25rem; }
    .swal2-popup.dispensasi-alert .swal2-actions { flex-direction: column-reverse; }
    .swal2-popup.dispensasi-alert .swal2-styled { width: 100%; margin: 0; }
}
</style>

{{-- HERO SECTION - Format modern, responsive seperti siswa --}}
<div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 rounded-2xl p-4 sm:p-6 mb-5 shadow-lg shadow-blue-500/15 text-white">
    <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute right-12 top-2 w-24 h-24 bg-indigo-400/20 rounded-full blur-xl pointer-events-none"></div>
    
    <div class="relative z-10">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-sm text-[11px] font-semibold text-blue-100 uppercase tracking-wide mb-2">
            <i class="far fa-calendar-alt text-blue-200"></i>
            {{ now()->isoFormat('dddd, D MMMM Y') }}
        </div>
        <h2 class="text-lg sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
            Halo, {{ auth()->user()->name }}!
        </h2>
        <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-xl">
            Panel Guru Piket DIDISPEN. Verifikasi permohonan dispensasi dan pantau aktivitas izin siswa hari ini secara real-time.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.pengajuan.create') }}"
               class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-xs font-bold shadow-sm active:scale-95 transition-all">
                <i class="fas fa-plus mr-1.5"></i>Buat Dispensasi
            </a>
            @if(!empty($incomingSwapCount) && $incomingSwapCount > 0)
                <a href="{{ route('guru.piket.swap.incoming') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white text-xs font-semibold border border-white/20 transition-all">
                    <i class="fas fa-inbox mr-1.5 text-yellow-300"></i>{{ $incomingSwapCount }} Permintaan Tukar
                </a>
            @endif
        </div>
    </div>
</div>

{{-- SECTION GURU PIKET HARI INI --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 mb-4 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5 pb-3 border-b border-gray-100">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center whitespace-nowrap">
                    <i class="fas fa-user-shield text-blue-600 mr-2"></i>
                    <span>{{ isset($infoPiket['status_sesi']) && $infoPiket['status_sesi'] === 'Akan Datang' ? 'Guru Piket Berikutnya' : 'Guru Piket Hari Ini' }}</span>
                </h3>
                @if(!empty($infoPiket['jadwal']))
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-800 border border-blue-100 whitespace-nowrap">
                        {{ substr($infoPiket['jadwal']->jam_mulai, 0, 5) }} - {{ substr($infoPiket['jadwal']->jam_selesai, 0, 5) }} WIB
                    </span>
                    @if($infoPiket['status_sesi'] === 'Berlangsung')
                        <span class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5 animate-pulse"></span>
                            Sedang Berlangsung
                        </span>
                    @elseif($infoPiket['status_sesi'] === 'Akan Datang')
                        <span class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 border border-indigo-200 whitespace-nowrap">
                            <i class="far fa-clock mr-1 text-[11px]"></i>
                            Akan Datang
                        </span>
                    @endif
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Daftar petugas piket aktual yang bertugas pada sesi ini.</p>
        </div>

        {{-- Status Keikutsertaan Guru Login & Tombol Aksi --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if(!empty($incomingSwapCount) && $incomingSwapCount > 0)
                <a href="{{ route('guru.piket.swap.incoming') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 text-xs font-bold transition-colors">
                    <i class="fas fa-inbox mr-1.5"></i>Permintaan Masuk
                    <span class="ml-1.5 px-1.5 py-0.5 bg-indigo-600 text-white rounded-full text-[10px] leading-none">{{ $incomingSwapCount }}</span>
                </a>
            @endif

            @if($isPetugasAktual)
                <span class="text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg inline-flex items-center">
                    <i class="fas fa-check-circle mr-1.5 text-emerald-600"></i>Anda bertugas pada sesi ini
                </span>
                <a href="{{ route('guru.piket.swap.create', ['jadwal_id' => $infoPiket['jadwal']?->id]) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                    <i class="fas fa-exchange-alt mr-1.5"></i>Tukar Jadwal
                </a>
                @if(isset($infoPiket['status_sesi']) && $infoPiket['status_sesi'] === 'Berlangsung')
                    <a href="{{ route('guru.checklog.index') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition-colors">
                        <i class="fas fa-sign-out-alt mr-1.5"></i>Catat Keluar
                    </a>
                @endif
            @else
                <span class="text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg inline-flex items-center shadow-2xs">
                    <i class="fas fa-times-circle text-red-500 mr-1.5 text-sm"></i>Anda tidak bertugas pada sesi ini/hari ini.
                </span>
                <a href="{{ route('guru.piket.swap.incoming') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition-colors">
                    <i class="fas fa-handshake mr-1.5 text-gray-400"></i>Jadwal Tukar
                </a>
            @endif
        </div>
    </div>

    {{-- KONDISI 1: CONFLICT REPLACEMENT --}}
    @if(!empty($infoPiket['conflict']))
        <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 flex items-center gap-2.5">
            <i class="fas fa-exclamation-triangle text-base text-red-600 flex-shrink-0"></i>
            <span class="font-medium">Data jadwal membutuhkan pemeriksaan admin.</span>
        </div>

    {{-- KONDISI 2: TIDAK ADA JADWAL SESI --}}
    @elseif(empty($infoPiket['jadwal']))
        <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-500 flex items-center gap-2">
            <i class="fas fa-info-circle text-base text-gray-400 flex-shrink-0"></i>
            <span>{{ empty($adaJadwalHariIni) ? 'Belum ada jadwal Guru Piket untuk hari ini.' : 'Tidak ada sesi Guru Piket berikutnya hari ini.' }}</span>
        </div>

    {{-- KONDISI 3: ADA SESI & PETUGAS AKTUAL --}}
    @else
        @if(empty($infoPiket['petugas']))
            <p class="text-xs text-gray-500 italic">Belum ada petugas piket yang ditentukan untuk sesi ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($infoPiket['petugas'] as $p)
                    @php
                        $statusClass = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'Sedang Keluar'   => 'bg-amber-50 text-amber-800 border-amber-200',
                            'Sudah Kembali'   => 'bg-sky-50 text-sky-800 border-sky-200',
                            'Akan Bertugas'   => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                            default           => 'bg-gray-50 text-gray-700 border-gray-200',
                        };
                        $dotColor = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-500',
                            'Sedang Keluar'   => 'bg-amber-500',
                            'Sudah Kembali'   => 'bg-sky-500',
                            'Akan Bertugas'   => 'bg-indigo-500',
                            default           => 'bg-gray-400',
                        };
                    @endphp
                    <div class="p-3.5 rounded-xl border border-gray-200 bg-white hover:border-gray-300 transition-all flex flex-col justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-gray-900 break-words">{{ $p['guru']->nama_lengkap }}</p>
                            @if(!empty($p['is_pengganti']) && !empty($p['guru_resmi']))
                                <p class="text-[11px] text-amber-700 font-medium mt-0.5 flex items-center gap-1">
                                    <i class="fas fa-exchange-alt text-[10px]"></i>
                                    <span class="break-words">Menggantikan: {{ $p['guru_resmi']->nama_lengkap }}</span>
                                </p>
                            @endif
                        </div>
                    	<div class="pt-1">
    <div class="flex flex-col items-start gap-1">

        {{-- Status utama --}}
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusClass }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
            {{ $p['status'] }}
        </span>

   {{-- Setelah kembali, guru kembali menjalankan tugas --}}
@if($p['status'] === 'Sudah Kembali' && ($infoPiket['status_sesi'] ?? null) === 'Berlangsung')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border bg-emerald-50 text-emerald-800 border-emerald-200">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Sedang Bertugas
    </span>
@endif

    </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

{{-- KOLOM PENCARIAN SISWA --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-4">
    <form method="GET" action="{{ route('guru.dashboard') }}" class="flex gap-2">
        <input type="hidden" name="filter" value="{{ $filter ?? 'semua' }}">
        <div class="flex-1 relative min-w-0">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Cari nama siswa, NIS, atau nomor surat..."
                   class="w-full pl-10 pr-10 py-2.5 h-11 border border-gray-300 rounded-lg text-base sm:text-sm focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all"
                   autofocus>
            @if($search)
                <a href="{{ route('guru.dashboard', ['filter' => $filter]) }}"
                   class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors" title="Hapus pencarian">
                    <i class="fas fa-times-circle text-lg"></i>
                </a>
            @endif
        </div>
        <button type="submit" class="px-5 py-2.5 min-h-[44px] bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center flex-shrink-0">
            <i class="fas fa-search mr-1.5 hidden sm:inline"></i>Cari
        </button>
    </form>
    @if($search)
        <div class="mt-2 text-xs text-gray-500 flex items-center flex-wrap">
            <i class="fas fa-info-circle mr-1"></i>
            Menampilkan hasil pencarian untuk: <strong class="text-gray-800 mx-1">"{{ $search }}"</strong>
            <span class="mx-1">•</span>
            <span>{{ $displayData->count() }} data ditemukan</span>
        </div>
    @endif
</div>

{{-- STATISTIK SEBAGAI FILTER UTAMA --}}
@php
$cards = [
    'menunggu' => ['Menunggu', $stats['menunggu'] ?? 0, 'fa-clock', 'amber'],
    'disetujui' => ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle', 'emerald'],
    'keluar'    => ['Sedang Keluar', $stats['keluar'] ?? 0, 'fa-walking', 'sky'],
    'selesai'   => ['Selesai', $stats['selesai'] ?? 0, 'fa-check-double', 'gray'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-3">
    @foreach($cards as $key => $card)
        @php
            $isActive = $filter === $key;
            $color = $card[3];
        @endphp
        <button type="button"
                onclick="switchFilter('{{ $key }}', '{{ $color }}', event)"
                data-filter="{{ $key }}"
                class="stat-card-btn text-left rounded-2xl border p-3.5 sm:p-4 min-h-[44px] transition-all w-full
                {{ $isActive
                    ? 'active bg-white border-' . $color . '-500 ring-2 ring-' . $color . '-500/20 shadow-sm'
                    : 'bg-white border-gray-200/90 shadow-xs hover:border-gray-300 hover:shadow-md' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors
                    {{ $isActive ? 'bg-' . $color . '-500 text-white' : 'bg-' . $color . '-100 text-' . $color . '-600' }}">
                    <i class="fas {{ $card[2] }} text-sm"></i>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-gray-900">{{ $card[1] }}</span>
            </div>
            <p class="text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $card[0] }}</p>
        </button>
    @endforeach
</div>

{{-- SECONDARY FILTER BUTTONS --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
    <button type="button"
            onclick="switchFilter('semua', 'blue', event)"
            data-filter="semua"
            class="filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-lg text-xs font-semibold border transition-all text-center
            {{ $filter === 'semua'
                ? 'active bg-blue-600 text-white shadow-sm border-transparent hover:bg-blue-700'
                : 'bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-gray-200' }}">
        <i class="fas fa-layer-group mr-1.5 {{ $filter === 'semua' ? 'text-white' : 'text-blue-500' }}"></i>Tampilkan Semua ({{ $stats['total'] ?? 0 }})
    </button>
    <button type="button"
            onclick="switchFilter('terlambat', 'red', event)"
            data-filter="terlambat"
            class="filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-lg text-xs font-semibold border transition-all text-center
            {{ $filter === 'terlambat'
                ? 'active bg-red-600 text-white shadow-sm border-transparent hover:bg-red-700'
                : 'bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-gray-200' }}">
        <i class="fas fa-exclamation-triangle mr-1.5 {{ $filter === 'terlambat' ? 'text-white' : 'text-red-500' }}"></i>Terlambat ({{ $stats['terlambat'] ?? count($terlambat ?? []) }})
    </button>
    <button type="button"
            onclick="switchFilter('dihubungi', 'purple', event)"
            data-filter="dihubungi"
            class="filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-lg text-xs font-semibold border transition-all text-center col-span-2 sm:col-span-1
            {{ $filter === 'dihubungi'
                ? 'active bg-purple-600 text-white shadow-sm border-transparent hover:bg-purple-700'
                : 'bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-gray-200' }}">
        <i class="fas fa-phone-alt mr-1.5 {{ $filter === 'dihubungi' ? 'text-white' : 'text-purple-500' }}"></i>Dihubungi ({{ count($dihubungi ?? []) }})
    </button>
</div>

{{-- DAFTAR DISPENSASI --}}
<div id="content-area" class="fade-in bg-white border border-gray-200 rounded-xl overflow-hidden mb-16 sm:mb-0">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
        <h3 class="text-sm font-bold text-gray-900">
            @php
            $titleMap = [
                'semua' => 'Semua Dispensasi Hari Ini',
                'menunggu' => 'Pengajuan Menunggu Persetujuan',
                'disetujui' => 'Disetujui (Menunggu Scan Satpam)',
                'keluar' => 'Siswa Sedang Keluar',
                'selesai' => 'Siswa Sudah Kembali',
                'terlambat' => 'Siswa Terlambat Kembali',
                'dihubungi' => 'Siswa Sudah Dihubungi',
            ];
            @endphp
            {{ $titleMap[$filter] ?? 'Daftar Dispensasi' }}
        </h3>
        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-2.5 py-1 rounded-lg">{{ count($displayData) }} data</span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($displayData as $item)
            @php
                $highlightName = $search ?
                    preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">$1</mark>', $item->siswa->nama_lengkap) :
                    $item->siswa->nama_lengkap;
                $isLate = $item->status === 'keluar' && $item->batas_waktu_kembali && now()->greaterThan($item->batas_waktu_kembali);
                $lateMinutes = $isLate ? \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($item->batas_waktu_kembali) : 0;
                $lateText = $isLate ? \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat($lateMinutes, short: true) : '';
                $waLink = '';
                if ($isLate && !empty($item->siswa->no_telepon)) {
                    $hp = preg_replace('/[^0-9]/', '', $item->siswa->no_telepon);
                    $hp = str_starts_with($hp, '0') ? '62' . substr($hp, 1) : $hp;
                    $waLink = "https://wa.me/{$hp}?text=" . urlencode("*PERINGATAN DISPENSASI*\n\nYth. {$item->siswa->nama_lengkap},\nAnda telah melewati batas waktu kembali dispensasi (Terlambat {$lateText}).\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n\nTerima kasih.");
                }
            @endphp
            <div class="p-4 transition-colors {{ $isLate ? 'bg-red-50/60 hover:bg-red-100/60 border-l-4 border-l-red-500' : 'hover:bg-gray-50/80' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">{{ $item->nomor_surat }}</span>
                            @php
                                $badge = $item->status_badge;
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide {{ $badge['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $badge['text'] }}
                            </span>
                            @if($item->is_warned)
                                <span class="warned-badge px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide bg-purple-100 text-purple-800 border-purple-200">
                                    <i class="fas fa-phone-alt mr-1"></i>Sudah Dihubungi
                                </span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-900 text-sm break-words mt-1">
                            {!! $highlightName !!}
                        </p>
                        <p class="text-xs font-medium text-gray-600 mb-1">
                            {{ $item->siswa->kelas?->nama_kelas ?? '-' }} • {{ $item->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-700 line-clamp-2">
                            <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $item->kategori)) }}:</span>
                            {{ Str::limit($item->alasan, 70) }}
                        </p>
                        @if($isLate)
                            <p class="text-xs text-red-600 font-semibold mt-1.5 flex items-center">
                                <i class="far fa-clock mr-1.5"></i>
                                Batas kembali: {{ \Carbon\Carbon::parse($item->batas_waktu_kembali)->format('H:i') }} WIB
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-200 sm:border-gray-100">
                        @if($item->is_warned)
                            <button type="button"
                                    class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] bg-green-600 text-white text-xs font-semibold rounded-lg opacity-50 cursor-not-allowed"
                                    disabled>
                                <i class="fab fa-whatsapp mr-1.5"></i>
                                <span class="wa-text">Sudah Dihubungi</span>
                            </button>
                        @elseif($isLate && $waLink)
                            <button onclick="handleGuruWaContacted({{ $item->id }}, '{{ $waLink }}', this)"
                                    class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                <i class="fab fa-whatsapp mr-1.5"></i>
                                <span class="wa-text">Hubungi</span>
                            </button>
                        @endif
                        <a href="{{ route('guru.pengajuan.show', $item) }}"
                           class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200"
                           title="Lihat Detail">
                            <i class="fas fa-eye mr-1.5"></i>Detail
                        </a>

                            @if($item->status === 'menunggu')
                                <form method="POST" action="{{ route('guru.pengajuan.approve', $item) }}" id="form-approve-{{ $item->id }}" class="inline">
                                    @csrf
                                    <button type="button"
                                            onclick="showApproveModal({{ $item->id }}, '{{ addslashes($item->siswa->nama_lengkap) }}', '{{ $item->nomor_surat }}')"
                                            class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors"
                                            title="Setujui">
                                        <i class="fas fa-check mr-1.5"></i>Setuju
                                    </button>
                                </form>
                            <button type="button"
                                    onclick="rejectDispensasi({{ $item->id }}, '{{ addslashes($item->siswa->nama_lengkap) }}')"
                                    class="inline-flex items-center justify-center px-3.5 py-2.5 min-h-[44px] bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors"
                                    title="Tolak">
                                <i class="fas fa-times mr-1.5"></i>Tolak
                            </button>
                       @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                    <i class="fas fa-search"></i>
                </div>
                <p class="text-gray-800 font-semibold text-sm">
                    @if($search)
                        Tidak ada hasil pencarian untuk "{{ $search }}"
                    @else
                        Tidak ada data dispensasi untuk filter ini
                    @endif
                </p>
                <p class="text-gray-600 text-xs mt-1">
                    @if($search)
                        Coba ubah kata kunci pencarian atau hapus filter
                    @else
                        Data akan muncul ketika siswa mengajukan dispensasi
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>

{{-- Loading Overlay --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-4 shadow-lg flex items-center space-x-3 border border-gray-200">
        <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div>
        <span class="text-xs font-semibold text-gray-800">Memuat data...</span>
    </div>
</div>


@push('scripts')
<script>
function showApproveModal(id, studentName, nomorSurat) {
    const approveForm = document.getElementById('form-approve-' + id);
    if (!approveForm) {
        console.error('Form tidak ditemukan untuk ID:', id);
        Swal.fire({ icon: 'error', title: 'Form tidak ditemukan', text: 'Silakan muat ulang halaman lalu coba kembali.' });
        return;
    }

    Swal.fire({
        icon: 'question', title: 'Setujui dispensasi?',
        html: `<div class="text-left rounded-xl border border-blue-100 bg-blue-50 p-3 text-sm text-gray-700"><p class="font-semibold text-gray-900">${escapeAlertHtml(studentName)}</p><p class="mt-0.5 font-mono text-xs text-gray-500">${escapeAlertHtml(nomorSurat)}</p><p class="mt-2 text-xs">Siswa akan dapat melanjutkan proses dispensasi.</p></div>`,
        showCancelButton: true, confirmButtonText: '<i class="fas fa-check mr-1"></i> Setujui', cancelButtonText: 'Kembali',
        focusCancel: true, reverseButtons: true,
        customClass: { popup: 'dispensasi-alert', confirmButton: 'bg-emerald-600 hover:bg-emerald-700', cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700' },
        confirmButtonColor: '#059669', cancelButtonColor: '#f3f4f6'
    }).then((result) => { if (result.isConfirmed) approveForm.submit(); });
}

function escapeAlertHtml(value) {
    const element = document.createElement('div');
    element.textContent = value ?? '';
    return element.innerHTML;
}

let currentFilter = '{{ $filter }}';
let isFilterFetching = false;

const statCardColors = {
    'menunggu': 'amber',
    'disetujui': 'emerald',
    'keluar': 'sky',
    'selesai': 'gray'
};

function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter || isFilterFetching) return;

    isFilterFetching = true;

    // Reset tombol secondary filter
    const filterColorClasses = [
        'active', 'text-white', 'shadow-sm', 'border-transparent',
        'bg-blue-600', 'bg-red-600', 'bg-purple-600', 'bg-amber-600', 'bg-emerald-600', 'bg-sky-600', 'bg-gray-600',
        'hover:bg-blue-700', 'hover:bg-red-700', 'hover:bg-purple-700', 'hover:bg-amber-700', 'hover:bg-emerald-700', 'hover:bg-sky-700', 'hover:bg-gray-700'
    ];

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove(...filterColorClasses);
        btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-50', 'hover:text-gray-900', 'border-gray-200');

        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('text-white');
            const btnFilter = btn.getAttribute('data-filter');
            if (btnFilter === 'semua') icon.classList.add('text-blue-500');
            else if (btnFilter === 'terlambat') icon.classList.add('text-red-500');
            else if (btnFilter === 'dihubungi') icon.classList.add('text-purple-500');
        }
    });

    // Reset kartu statistik
    document.querySelectorAll('.stat-card-btn').forEach(card => {
        const key = card.getAttribute('data-filter');
        const c = statCardColors[key] || 'gray';
        card.classList.remove('active', 'border-amber-500', 'border-emerald-500', 'border-sky-500', 'border-gray-500', 'ring-2', 'ring-amber-500/20', 'ring-emerald-500/20', 'ring-sky-500/20', 'ring-gray-500/20', 'shadow-sm');
        card.classList.add('bg-white', 'border-gray-200', 'hover:border-gray-300');
        const iconContainer = card.querySelector('div > div');
        if (iconContainer) {
            iconContainer.className = `w-9 h-9 rounded-lg flex items-center justify-center transition-colors bg-${c}-100 text-${c}-600`;
        }
    });

    // Aktifkan secondary filter jika ada
    const activeBtn = document.querySelector(`button.filter-btn[data-filter="${filterKey}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-50', 'hover:text-gray-900', 'border-gray-200');
        activeBtn.classList.add('active', `bg-${color}-600`, 'text-white', 'shadow-sm', 'border-transparent', `hover:bg-${color}-700`);
        const activeIcon = activeBtn.querySelector('i');
        if (activeIcon) {
            activeIcon.classList.remove('text-blue-500', 'text-red-500', 'text-purple-500');
            activeIcon.classList.add('text-white');
        }
    }

    // Aktifkan kartu statistik jika ada
    const activeStatCard = document.querySelector(`button.stat-card-btn[data-filter="${filterKey}"]`);
    if (activeStatCard && statCardColors[filterKey]) {
        const c = statCardColors[filterKey];
        activeStatCard.classList.remove('border-gray-200', 'hover:border-gray-300');
        activeStatCard.classList.add('active', `border-${c}-500`, 'ring-2', `ring-${c}-500/20`, 'shadow-sm');
        const iconContainer = activeStatCard.querySelector('div > div');
        if (iconContainer) {
            iconContainer.className = `w-9 h-9 rounded-lg flex items-center justify-center transition-colors bg-${c}-500 text-white`;
        }
    }

    const contentArea = document.getElementById('content-area');
    const loading = document.getElementById('loading-overlay');
    if (contentArea) {
        contentArea.classList.remove('fade-in');
        contentArea.classList.add('fade-out');
    }
    if (loading) {
        loading.classList.remove('hidden');
    }

    const targetUrl = new URL(window.location.href);
    targetUrl.searchParams.set('filter', filterKey);

    fetch(targetUrl.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.text();
    })
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('content-area');
        if (newContent && contentArea) {
            setTimeout(() => {
                contentArea.innerHTML = newContent.innerHTML;
                contentArea.classList.remove('fade-out');
                contentArea.classList.add('fade-in');
                if (loading) loading.classList.add('hidden');
                currentFilter = filterKey;
                isFilterFetching = false;
                window.history.pushState({ filter: filterKey }, '', targetUrl.toString());
            }, 150);
        } else {
            isFilterFetching = false;
            window.location.href = targetUrl.toString();
        }
    })
    .catch(error => {
        console.error('Error switching filter:', error);
        if (loading) loading.classList.add('hidden');
        isFilterFetching = false;
        window.location.href = targetUrl.toString();
    });
}


window.addEventListener('popstate', function(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'semua';
    if (filter !== currentFilter) {
        let color = 'blue';
        if(filter === 'menunggu') color = 'amber';
        if(filter === 'disetujui') color = 'emerald';
        if(filter === 'keluar') color = 'sky';
        if(filter === 'selesai') color = 'gray';
        if(filter === 'terlambat') color = 'red';
        if(filter === 'dihubungi') color = 'purple';
        switchFilter(filter, color, null);
    }
});

function rejectDispensasi(id, namaSiswa) {
    Swal.fire({
        icon: 'warning', title: 'Tolak dispensasi?',
        html: `<p class="text-sm text-gray-600">Tulis alasan penolakan untuk <strong class="text-gray-900">${escapeAlertHtml(namaSiswa)}</strong>.</p>`,
        input: 'textarea',
        inputPlaceholder: 'Contoh: data atau alasan pengajuan belum lengkap',
        inputAttributes: { rows: 4, 'aria-label': 'Alasan penolakan' },
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-times mr-1"></i> Tolak dispensasi', cancelButtonText: 'Kembali',
        reverseButtons: true, focusCancel: true,
        customClass: { popup: 'dispensasi-alert', confirmButton: 'bg-red-600 hover:bg-red-700', cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700' },
        confirmButtonColor: '#dc2626', cancelButtonColor: '#f3f4f6',
        inputValidator: (value) => {
            if (!value || value.trim() === '') return 'Alasan penolakan wajib diisi.';
        }
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/guru/pengajuan/${id}/reject`;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            const reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'catatan_admin';
            reason.value = result.value;
            form.append(csrf, reason);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function handleGuruWaContacted(dispensasiId, waLink, button) {
    if (button.disabled) {
        window.open(waLink, '_blank');
        return;
    }

    fetch(`/guru/dispensasi/${dispensasiId}/wa-contacted`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        keepalive: true
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            const waText = button.querySelector('.wa-text');
            if (waText) waText.textContent = 'Sudah Dihubungi';

            const card = button.closest('.p-4');
            if (card) {
                const badgeRow = card.querySelector('.flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend',
                        `<span class="warned-badge px-2 py-0.5 rounded border text-[10px] font-semibold uppercase tracking-wide bg-purple-100 text-purple-800 border-purple-200 ml-2">
                            <i class="fas fa-phone-alt mr-1"></i>Dihubungi
                        </span>`
                    );
                }
            }
            window.open(waLink, '_blank');
        } else {
            alert('Gagal menandai status: ' + data.message);
            window.open(waLink, '_blank');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.open(waLink, '_blank');
    });
}

</script>

@endpush
@endsection
