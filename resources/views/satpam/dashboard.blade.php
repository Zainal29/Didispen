@extends('satpam.layouts.app')

@section('title', 'Dashboard Satpam')
@section('page-title', 'Dashboard Satpam')

@section('content')

<style>
    .filter-btn { transition: all 0.2s ease-in-out; }
    .filter-btn:hover { transform: translateY(-1px); }
    .filter-btn.active { transform: scale(1.02); }
    #content-area { transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out; }
    .fade-out { opacity: 0; transform: translateY(8px); }
    .fade-in { opacity: 1; transform: translateY(0); }
</style>

@php
    $currentFilter = $filter ?? 'semua';
@endphp

{{-- ============ HERO HEADER (CLEAN WHITE CARD MINIMALIS) ============ --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-2xs">
                <i class="ti ti-shield-check text-xl sm:text-2xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 border border-rose-100 text-[10px] sm:text-[11px] font-bold text-rose-700">
                        <i class="ti ti-map-pin text-xs"></i> Pos Gerbang Utama
                    </span>
                    <span class="text-[11px] sm:text-xs text-slate-500 font-medium">
                        {{ now()->isoFormat('dddd, D MMMM Y') }}
                    </span>
                </div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug break-words">
                    Halo, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                    Verifikasi barcode keluar-masuk siswa dispensasi hari ini secara realtime di pos keamanan gerbang.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-stretch sm:self-center shrink-0">
            <a href="{{ route('satpam.scan') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold shadow-xs active:scale-[0.98] transition-all">
                <i class="ti ti-qrcode text-base"></i>
                <span>Scan QR Dispensasi</span>
            </a>
        </div>
    </div>
</div>

{{-- MODAL & BANNER DISPENSASI MANUAL GURU PIKET (SISWA TANPA HP) --}}
@include('satpam._modal_manual_guru')

{{-- ============ GURU PIKET HARI INI ============ --}}
<div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 mb-4 shadow-xs">
    <div class="border-b border-slate-100 pb-3 mb-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4">
            <div class="flex items-start gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 text-sm mt-0.5 sm:mt-0 shadow-2xs">
                    <i class="ti ti-user-shield text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h3 class="text-sm font-bold text-slate-900 whitespace-nowrap">Guru Piket Hari Ini</h3>
                        @if(!empty($infoPiket['jadwal']))
                            @if(!empty($infoPiket['jadwal']->nama_sesi))
                                <span class="text-[10px] sm:text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100 whitespace-nowrap">
                                    {{ $infoPiket['jadwal']->nama_sesi }}
                                </span>
                            @endif
                            <span class="text-[11px] sm:text-xs font-mono font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md whitespace-nowrap">
                                <i class="ti ti-clock text-[11px] text-slate-400 mr-1"></i>{{ substr($infoPiket['jadwal']->jam_mulai, 0, 5) }} – {{ substr($infoPiket['jadwal']->jam_selesai, 0, 5) }} WIB
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 sm:mt-1">Daftar Bapak/Ibu Guru yang bertugas piket pada sesi ini untuk koordinasi gerbang.</p>
                </div>
            </div>
            @if(!empty($infoPiket['jadwal']))
                <div class="flex items-center sm:self-center self-start pl-10 sm:pl-0 flex-shrink-0">
                    @if($infoPiket['status_sesi'] === 'Berlangsung')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Sedang Berlangsung
                        </span>
                    @elseif($infoPiket['status_sesi'] === 'Akan Datang')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 border border-slate-200 shadow-2xs whitespace-nowrap">
                            <i class="ti ti-clock mr-1 text-[11px]"></i>
                            Akan Datang
                        </span>
                    @elseif($infoPiket['status_sesi'] === 'Sesi Selesai')
                        <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">
                            Sesi Selesai
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- KONDISI 1: CONFLICT REPLACEMENT --}}
    @if(!empty($infoPiket['conflict']))
        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2.5">
            <i class="ti ti-alert-triangle text-base text-amber-600 flex-shrink-0"></i>
            <span class="font-medium">Data Guru Piket sedang diperiksa.</span>
        </div>

    {{-- KONDISI 2: TIDAK ADA JADWAL SESI --}}
    @elseif(empty($infoPiket['jadwal']))
        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 flex items-center gap-2">
            <i class="ti ti-info-circle text-base text-slate-400 flex-shrink-0"></i>
            <span>{{ empty($adaJadwalHariIni) ? 'Belum ada jadwal Guru Piket untuk hari ini.' : 'Tidak ada sesi Guru Piket berikutnya hari ini.' }}</span>
        </div>

     {{-- KONDISI 3: ADA SESI & PETUGAS AKTUAL --}}
    @else
        @if(empty($infoPiket['petugas']))
            <p class="text-xs text-slate-500 italic">Belum ada petugas piket yang ditentukan untuk sesi ini.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
                @foreach($infoPiket['petugas'] as $p)
                    @php
                        $statusClass = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'Sedang Keluar'   => 'bg-amber-50 text-amber-800 border-amber-200',
                            'Sudah Kembali'   => 'bg-blue-50 text-blue-800 border-blue-200',
                            'Akan Bertugas'   => 'bg-slate-100 text-slate-800 border-slate-200',
                            default           => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                        $dotColor = match($p['status']) {
                            'Sedang Bertugas' => 'bg-emerald-500',
                            'Sedang Keluar'   => 'bg-amber-500',
                            'Sudah Kembali'   => 'bg-blue-500',
                            'Akan Bertugas'   => 'bg-slate-500',
                            default           => 'bg-slate-400',
                        };
                        $initials = collect(explode(' ', $p['guru']->nama_lengkap ?? 'G'))
                            ->filter()
                            ->take(2)
                            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                            ->join('');
                        $rawPhone = preg_replace('/[^0-9]/', '', $p['guru']->no_telepon ?? '');
                        $waPhone = str_starts_with($rawPhone, '0') ? '62' . substr($rawPhone, 1) : $rawPhone;
                    @endphp
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-all flex flex-col justify-between gap-2.5 shadow-2xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-2xs">
                                    {{ $initials ?: 'GP' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-sm text-slate-900 break-words" title="{{ $p['guru']->nama_lengkap }}">
                                        {{ $p['guru']->nama_lengkap }}
                                    </p>
                                    @if(!empty($p['is_pengganti']) && !empty($p['guru_resmi']))
                                        <p class="text-[11px] text-amber-700 font-medium mt-0.5 flex items-center gap-1">
                                            <i class="ti ti-arrows-exchange text-xs"></i>
                                            <span class="break-words">Menggantikan: {{ $p['guru_resmi']->nama_lengkap }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Halo Bapak/Ibu Guru Piket, ada koordinasi terkait perizinan siswa dari Pos Satpam SMKN 1 Bangsri.') }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   title="Chat WhatsApp {{ $p['guru']->nama_lengkap }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors text-sm active:scale-95 flex-shrink-0">
                                    <i class="ti ti-brand-whatsapp text-base"></i>      
                                </a>
                            @endif -->
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} {{ $p['status'] === 'Sedang Bertugas' ? 'animate-pulse' : '' }}"></span>
                                {{ $p['status'] }}
                            </span>

                            @if($p['status'] === 'Sudah Kembali' && ($infoPiket['status_sesi'] ?? null) === 'Berlangsung')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border bg-emerald-50 text-emerald-800 border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Sedang Bertugas
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

{{-- STATISTIK SEBAGAI FILTER UTAMA --}}
@php
$cards = [
    'menunggu'  => [
        'title' => 'Menunggu Scan',
        'count' => $stats['menunggu_keluar'] ?? $menungguKeluar->count(),
        'icon'  => 'ti-clock-pause',
        'color' => 'amber',
        'active_btn'    => 'active bg-white border-amber-500 ring-2 ring-amber-500/20 shadow-sm',
        'inactive_btn'  => 'bg-white border-slate-200 shadow-2xs hover:border-slate-300 hover:shadow-xs',
        'active_icon'   => 'bg-amber-100 text-amber-800 border border-amber-300',
        'inactive_icon' => 'bg-amber-50 text-amber-600 border border-amber-200/80',
    ],
    'keluar'    => [
        'title' => 'Sedang Keluar',
        'count' => $stats['keluar'] ?? $siswaKeluar->count(),
        'icon'  => 'ti-walk',
        'color' => 'sky',
        'active_btn'    => 'active bg-white border-sky-500 ring-2 ring-sky-500/20 shadow-sm',
        'inactive_btn'  => 'bg-white border-slate-200 shadow-2xs hover:border-slate-300 hover:shadow-xs',
        'active_icon'   => 'bg-sky-100 text-sky-800 border border-sky-300',
        'inactive_icon' => 'bg-sky-50 text-sky-600 border border-sky-200/80',
    ],
    'terlambat' => [
        'title' => 'Terlambat',
        'count' => $stats['terlambat'] ?? $siswaTerlambat->count(),
        'icon'  => 'ti-alert-triangle',
        'color' => 'rose',
        'active_btn'    => 'active bg-white border-rose-500 ring-2 ring-rose-500/20 shadow-sm',
        'inactive_btn'  => 'bg-white border-slate-200 shadow-2xs hover:border-slate-300 hover:shadow-xs',
        'active_icon'   => 'bg-rose-100 text-rose-800 border border-rose-300',
        'inactive_icon' => 'bg-rose-50 text-rose-600 border border-rose-200/80',
    ],
    'selesai'   => [
        'title' => 'Selesai',
        'count' => $stats['selesai'] ?? $selesai->count(),
        'icon'  => 'ti-checks',
        'color' => 'emerald',
        'active_btn'    => 'active bg-white border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm',
        'inactive_btn'  => 'bg-white border-slate-200 shadow-2xs hover:border-slate-300 hover:shadow-xs',
        'active_icon'   => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
        'inactive_icon' => 'bg-emerald-50 text-emerald-600 border border-emerald-200/80',
    ],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-3">
    @foreach($cards as $key => $card)
        @php
            $isActive = $currentFilter === $key;
        @endphp
        <button type="button"
                onclick="switchFilter('{{ $key }}', '{{ $card['color'] }}', event)"
                data-filter="{{ $key }}"
                data-color="{{ $card['color'] }}"
                data-active-btn="{{ $card['active_btn'] }}"
                data-inactive-btn="{{ $card['inactive_btn'] }}"
                data-active-icon="{{ $card['active_icon'] }}"
                data-inactive-icon="{{ $card['inactive_icon'] }}"
                class="stat-card-btn text-left rounded-2xl border p-3.5 sm:p-4 min-h-[48px] transition-all w-full active:scale-[0.99] {{ $isActive ? $card['active_btn'] : $card['inactive_btn'] }}">
            <div class="flex items-center justify-between mb-2">
                <div class="icon-box w-9 h-9 rounded-xl flex items-center justify-center transition-colors {{ $isActive ? $card['active_icon'] : $card['inactive_icon'] }}">
                    <i class="ti {{ $card['icon'] }} text-base"></i>
                </div>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $card['count'] }}</span>
            </div>
            <p class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $card['title'] }}</p>
        </button>
    @endforeach
</div>

{{-- SECONDARY FILTER BUTTONS --}}
<div class="grid grid-cols-2 gap-2 mb-4">
    <button type="button"
            onclick="switchFilter('semua', 'rose', event)"
            data-filter="semua"
            class="filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-bold border transition-all text-center
            {{ $currentFilter === 'semua'
                ? 'active bg-rose-600 text-white shadow-xs border-transparent hover:bg-rose-700'
                : 'bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-2xs' }}">
        <i class="ti ti-layout-grid mr-1.5 text-sm"></i>Tampilkan Semua ({{ $stats['total'] ?? 0 }})
    </button>
    <button type="button"
            onclick="switchFilter('dihubungi', 'purple', event)"
            data-filter="dihubungi"
            class="filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-bold border transition-all text-center
            {{ $currentFilter === 'dihubungi'
                ? 'active bg-purple-600 text-white shadow-xs border-transparent hover:bg-purple-700'
                : 'bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-2xs' }}">
        <i class="ti ti-brand-whatsapp mr-1.5 text-base"></i>Dihubungi ({{ $dihubungi->count() }})
    </button>
</div>

{{-- ============ CONTENT SECTIONS ============ --}}
<div id="content-area" class="fade-in">

    {{-- SECTION: MENUNGGU --}}
    <div id="section-menunggu" class="space-y-3 {{ $currentFilter !== 'menunggu' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-clock-pause text-amber-500 mr-2 text-base"></i>Menunggu Scan Keluar</h3>
        @if($menungguKeluar->count() > 0)
            @foreach($menungguKeluar as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'menunggu', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-clock-pause"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Belum ada siswa yang menunggu scan keluar hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Dispensasi yang telah disetujui Guru Piket akan muncul di sini untuk verifikasi gerbang</p>
            </div>
        @endif
    </div>

    {{-- SECTION: KELUAR --}}
    <div id="section-keluar" class="space-y-3 {{ $currentFilter !== 'keluar' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-walk text-sky-500 mr-2 text-base"></i>Sedang Keluar</h3>
        @if($siswaKeluar->count() > 0)
            @foreach($siswaKeluar as $dispensasi)
                @php $isOverdue = $dispensasi->isOverdue(); @endphp
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'keluar', 'isOverdue' => $isOverdue])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-walk"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Belum ada siswa yang sedang keluar hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Siswa yang sudah di-scan keluar oleh satpam akan muncul di sini</p>
            </div>
        @endif
    </div>

    {{-- SECTION: TERLAMBAT --}}
    <div id="section-terlambat" class="space-y-3 {{ $currentFilter !== 'terlambat' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-alert-triangle text-rose-500 mr-2 text-base"></i>Siswa Terlambat</h3>
        @if($siswaTerlambat->count() > 0)
            @foreach($siswaTerlambat as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'terlambat', 'isOverdue' => true])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-circle-check"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Tidak ada siswa yang terlambat hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Semua siswa yang keluar telah kembali tepat waktu</p>
            </div>
        @endif
    </div>

    {{-- SECTION: SELESAI --}}
    <div id="section-selesai" class="space-y-3 {{ $currentFilter !== 'selesai' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-circle-check text-emerald-500 mr-2 text-base"></i>Sudah Kembali</h3>
        @if(isset($selesai) && $selesai->count() > 0)
            @foreach($selesai as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'selesai', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-circle-check"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Belum ada siswa yang kembali hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Siswa yang sudah di-scan kembali akan muncul di sini</p>
            </div>
        @endif
    </div>

    {{-- SECTION: DIHUBUNGI --}}
    <div id="section-dihubungi" class="space-y-3 {{ $currentFilter !== 'dihubungi' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-brand-whatsapp text-purple-500 mr-2 text-base"></i>Riwayat Siswa yang Sudah Dihubungi <span class="text-xs font-normal text-slate-500 ml-2">({{ $dihubungi->count() }} siswa)</span></h3>
        @if($dihubungi->count() > 0)
            @foreach($dihubungi as $dispensasi)
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => 'dihubungi', 'isOverdue' => false])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-50 text-purple-500 border border-purple-100 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-phone-off"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Belum ada siswa yang dihubungi hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Gunakan tombol "Hubungi" pada kartu siswa yang terlambat</p>
            </div>
        @endif
    </div>

    {{-- SECTION: SEMUA --}}
    <div id="section-semua" class="space-y-3 {{ $currentFilter !== 'semua' ? 'hidden' : '' }}">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center"><i class="ti ti-layout-grid text-slate-500 mr-2 text-base"></i>Semua Dispensasi Hari Ini</h3>
        @if($semuaDispensasi->count() > 0)
            @foreach($semuaDispensasi as $dispensasi)
                @php
                    $isOverdue = $dispensasi->isOverdue();
                    $cardStatus = match($dispensasi->status) {
                        'disetujui' => 'menunggu',
                        'keluar' => $isOverdue ? 'terlambat' : 'keluar',
                        'selesai' => 'selesai',
                        'kadaluarsa' => 'kadaluarsa',
                        default => $dispensasi->status
                    };
                @endphp
                @include('satpam._dispensasi_card', ['dispensasi' => $dispensasi, 'status' => $cardStatus, 'isOverdue' => $isOverdue])
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center text-2xl mb-3 shadow-2xs"><i class="ti ti-layout-grid"></i></div>
                <p class="text-slate-800 text-sm font-semibold">Belum ada data dispensasi hari ini</p>
                <p class="text-slate-500 text-xs mt-1">Semua aktivitas dispensasi akan muncul di sini</p>
            </div>
        @endif
    </div>

</div>

{{-- Loading Overlay (Diletakkan di luar content-area agar tidak tertutup animasi fade-out) --}}
<div id="loading-overlay" class="hidden fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-[100] flex items-center justify-center transition-all">
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-2xl flex items-center space-x-3.5 border border-slate-200">
        <div class="animate-spin rounded-full h-6 w-6 border-2 border-rose-600 border-t-transparent shrink-0"></div>
        <div>
            <p class="text-xs font-bold text-slate-800">Memuat data...</p>
            <p class="text-[10px] text-slate-500">Menyaring daftar dispensasi</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentFilter = '{{ $currentFilter }}';
let isSwitchingFilter = false;

function switchFilter(filterKey, color, event) {
    if (event) event.preventDefault();
    if (filterKey === currentFilter || isSwitchingFilter) return;

    isSwitchingFilter = true;

    // Reset secondary filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.className = 'filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-bold border transition-all text-center bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-2xs';
    });

    // Reset stat cards & icon boxes cleanly using data attributes
    document.querySelectorAll('.stat-card-btn').forEach(card => {
        card.className = `stat-card-btn text-left rounded-2xl border p-3.5 sm:p-4 min-h-[48px] transition-all w-full active:scale-[0.99] ${card.dataset.inactiveBtn}`;
        const iconBox = card.querySelector('.icon-box');
        if (iconBox) {
            iconBox.className = `icon-box w-9 h-9 rounded-xl flex items-center justify-center transition-colors ${card.dataset.inactiveIcon}`;
        }
    });

    // Set active secondary button (jika filterKey adalah semua / dihubungi)
    const activeBtn = document.querySelector(`button.filter-btn[data-filter="${filterKey}"]`);
    if (activeBtn) {
        const activeColor = filterKey === 'dihubungi' ? 'bg-purple-600 hover:bg-purple-700' : 'bg-rose-600 hover:bg-rose-700';
        activeBtn.className = `filter-btn px-3 py-2.5 min-h-[44px] inline-flex items-center justify-center rounded-xl text-xs font-bold border transition-all text-center active ${activeColor} text-white shadow-xs border-transparent`;
    }

    // Set active stat card & icon box cleanly
    const activeStatCard = document.querySelector(`button.stat-card-btn[data-filter="${filterKey}"]`);
    if (activeStatCard) {
        activeStatCard.className = `stat-card-btn text-left rounded-2xl border p-3.5 sm:p-4 min-h-[48px] transition-all w-full active:scale-[0.99] ${activeStatCard.dataset.activeBtn}`;
        const iconBox = activeStatCard.querySelector('.icon-box');
        if (iconBox) {
            iconBox.className = `icon-box w-9 h-9 rounded-xl flex items-center justify-center transition-colors ${activeStatCard.dataset.activeIcon}`;
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

    const startTime = Date.now();

    fetch(targetUrl.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP Error ${r.status}`);
        return r.text();
    })
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('content-area');

        // Pastikan loading tampil setidaknya 220ms agar user merasakan feedback animasi perpindahan yang jelas
        const elapsed = Date.now() - startTime;
        const delay = Math.max(0, 220 - elapsed);

        setTimeout(() => {
            if (newContent && contentArea) {
                contentArea.innerHTML = newContent.innerHTML;
                contentArea.classList.remove('fade-out');
                contentArea.classList.add('fade-in');
                currentFilter = filterKey;
                window.history.pushState({ filter: filterKey }, '', targetUrl.toString());
            } else {
                window.location.href = targetUrl.toString();
            }
            if (loading) loading.classList.add('hidden');
            isSwitchingFilter = false;
        }, delay);
    })
    .catch(err => {
        console.error('Error switching filter:', err);
        if (loading) loading.classList.add('hidden');
        isSwitchingFilter = false;
        window.location.href = targetUrl.toString();
    });
}

window.addEventListener('popstate', function(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'semua';
    if (filter !== currentFilter) {
        let color = 'slate';
        if (filter === 'menunggu') color = 'amber';
        if (filter === 'keluar') color = 'sky';
        if (filter === 'terlambat') color = 'rose';
        if (filter === 'selesai') color = 'emerald';
        if (filter === 'dihubungi') color = 'purple';
        switchFilter(filter, color, null);
    }
});

function handleWaContacted(dispensasiId, waLink) {
    fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        keepalive: true
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-dispensasi="${dispensasiId}"]`);
            if (card) {
                card.dataset.warned = 'true';
                const waSection = document.getElementById(`wa-section-${dispensasiId}`);
                if (waSection) {
                    waSection.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-emerald-700 uppercase">Kontak Darurat</p>
                                <p class="text-sm font-semibold text-slate-800 font-mono">${waSection.querySelector('.font-mono').textContent}</p>
                            </div>
                            <div class="inline-flex items-center justify-center w-10 h-10 bg-slate-200 text-slate-500 rounded-xl cursor-not-allowed" title="Sudah dihubungi">
                                <i class="ti ti-check text-base"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-green-600"><i class="ti ti-circle-check mr-1 text-xs"></i>Sudah dihubungi via WhatsApp</p>
                    `;
                }
                const badgeRow = card.querySelector('.badge-row, .flex.items-center.gap-1\\.5.mb-1, .flex.items-center.gap-2.mb-1');
                if (badgeRow && !badgeRow.querySelector('.warned-badge')) {
                    badgeRow.insertAdjacentHTML('beforeend', `<span class="warned-badge px-2 py-0.5 rounded-md text-[9px] font-bold bg-purple-100 text-purple-700 uppercase ml-1"><i class="ti ti-phone-call mr-1"></i>DIHUBUNGI</span>`);
                }
            }
            window.open(waLink, '_blank');
        } else {
            window.open(waLink, '_blank');
        }
    })
    .catch(error => { console.error('Error:', error); window.open(waLink, '_blank'); });
}

function tickCountdowns() {
    document.querySelectorAll('.live-countdown[data-deadline]').forEach(el => {
        const deadline = new Date(el.dataset.deadline);
        const diffMs = deadline - new Date();
        if (diffMs <= 0) {
            const lateMin = Math.floor(-diffMs / 60000);
            const hrs = Math.floor(lateMin / 60);
            const mins = lateMin % 60;
            const lateText = hrs > 0 ? `${hrs}j ${mins}m` : `${lateMin}m`;
            el.textContent = `TERLAMBAT ${lateText}`;
            el.classList.remove('bg-amber-100', 'text-amber-700');
            el.classList.add('bg-rose-100', 'text-rose-700');
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            el.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m` : `Sisa ${mins}m ${secs}s`;
            if (totalMin <= 5) {
                el.classList.remove('bg-amber-100', 'text-amber-700');
                el.classList.add('bg-rose-100', 'text-rose-700');
            }
        }
    });
}

const overdueNotified = new Set();
function watchOverdue() {
    document.querySelectorAll('[data-status="keluar"][data-deadline][data-overdue="false"]').forEach(card => {
        const deadline = new Date(card.dataset.deadline);
        if (new Date() > deadline) {
            card.dataset.overdue = 'true';
            const nama = card.querySelector('.font-bold.text-slate-900')?.textContent.trim() || 'Siswa';
            if (!overdueNotified.has(card.dataset.dispensasi)) {
                overdueNotified.add(card.dataset.dispensasi);
                Swal.fire({
                    icon: 'warning',
                    title: 'Siswa Terlambat!',
                    text: `${nama} telah melewati batas waktu kembali.`,
                    confirmButtonColor: '#e11d48',
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    tickCountdowns();
    setInterval(tickCountdowns, 1000);
    setInterval(watchOverdue, 15000);
});
</script>
@endpush
