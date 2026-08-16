@extends('siswa.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Siswa')

@section('content')

{{-- ============ HERO (compact) ============ --}}
<div class="relative overflow-hidden rounded-2xl mb-4">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500"></div>
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/10 rounded-full"></div>
    <div class="relative z-10 p-4 sm:p-6">
        <p class="text-blue-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
        <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight mt-0.5">Halo, {{ auth()->user()->name }}! 👋</h2>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/15 border border-white/20 text-white text-[11px] font-bold">
                <i class="fas fa-bell mr-1.5 text-amber-300"></i>{{ $notifikasiBelumDibaca }} Notifikasi Baru
            </span>
            <a href="{{ route('siswa.pengajuan.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-white text-blue-700 text-[11px] font-bold shadow-md active:scale-95 transition-transform">
                <i class="fas fa-plus mr-1.5"></i>Buat Pengajuan
            </a>
        </div>
    </div>
</div>

{{-- ============ QR CODE AKTIF (compact, berdampingan) ============ --}}
@if(isset($dispensasiAktif) && $dispensasiAktif->qr_code)
<div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                {{ $dispensasiAktif->status === 'selesai' ? 'bg-gray-100 text-gray-600' : 'bg-emerald-100 text-emerald-700 animate-pulse' }}">
                {{ $dispensasiAktif->status === 'selesai' ? 'Selesai' : 'Aktif' }}
            </span>
            <h3 class="text-sm font-bold text-gray-900"><i class="fas fa-qrcode text-blue-600 mr-1.5"></i>QR Dispensasi</h3>
        </div>
        <a href="{{ asset('storage/' . $dispensasiAktif->qr_code) }}" download class="text-[11px] font-bold text-blue-600 active:opacity-60">
            <i class="fas fa-download mr-1"></i>Simpan
        </a>
    </div>

    <div class="flex items-center gap-4">
        <div class="bg-white p-2 rounded-xl border-2 border-dashed border-blue-200 flex-shrink-0">
            <img src="{{ asset('storage/' . $dispensasiAktif->qr_code) }}" alt="QR Code" class="w-28 h-28 sm:w-40 sm:h-40 object-contain">
        </div>
        <div class="flex-1 min-w-0 space-y-2 text-xs">
            <div class="bg-blue-50/60 border border-blue-100 rounded-lg px-3 py-2">
                <p class="text-[10px] font-bold text-blue-500 uppercase">No. Surat</p>
                <p class="font-mono font-bold text-gray-800 truncate">{{ $dispensasiAktif->nomor_surat }}</p>
            </div>
            <div class="bg-blue-50/60 border border-blue-100 rounded-lg px-3 py-2">
                <p class="text-[10px] font-bold text-blue-500 uppercase">Waktu</p>
                <p class="font-bold text-gray-800">{{ $dispensasiAktif->jam_keluar }} – {{ $dispensasiAktif->jam_kembali }}</p>
            </div>
            <div class="bg-blue-50/60 border border-blue-100 rounded-lg px-3 py-2">
                <p class="text-[10px] font-bold text-blue-500 uppercase">Guru Piket</p>
                <p class="font-bold text-gray-800 truncate">{{ $dispensasiAktif->guruPiket->guru->nama_lengkap ?? '-' }}</p>
            </div>
        </div>
    </div>
    <p class="text-center text-[10px] text-gray-400 font-mono mt-3">Tunjukkan ke petugas satpam saat keluar & kembali</p>
</div>
@endif

{{-- ============ STATISTIK (2 kolom di HP, kartu terakhir melebar) ============ --}}
<div class="grid grid-cols-2 gap-2 sm:gap-4 mb-4 md:grid-cols-5">
    @php
        $cards = [
            ['Total',     $stats['total'] ?? 0,     'fa-file-alt',       'bg-gray-100 text-gray-600',     'border-gray-300'],
            ['Menunggu',  $stats['menunggu'] ?? 0,  'fa-clock',          'bg-amber-100 text-amber-600',   'border-amber-400'],
            ['Disetujui', $stats['disetujui'] ?? 0, 'fa-check-circle',   'bg-emerald-100 text-emerald-600','border-emerald-400'],
            ['Ditolak',   $stats['ditolak'] ?? 0,   'fa-times-circle',   'bg-red-100 text-red-600',       'border-red-400'],
            ['Selesai',   $stats['selesai'] ?? 0,   'fa-flag-checkered', 'bg-blue-100 text-blue-600',     'border-blue-400'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $color, $border])
        <div class="bg-white rounded-xl border border-gray-100 border-l-4 {{ $border }} shadow-sm p-3 sm:p-5 flex items-center justify-between {{ $loop->last ? 'col-span-2 md:col-span-1' : '' }}">
            <div class="min-w-0">
                <p class="text-gray-500 text-[11px] sm:text-xs font-semibold truncate">{{ $label }}</p>
                <h3 class="text-xl sm:text-3xl font-black text-gray-800 mt-0.5">{{ $value }}</h3>
            </div>
            <div class="p-2 sm:p-3 rounded-lg sm:rounded-xl {{ $color }} flex-shrink-0"><i class="fas {{ $icon }} text-sm sm:text-lg"></i></div>
        </div>
    @endforeach
</div>

{{-- ============ PENGAJUAN TERBARU (list compact) ============ --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-4 py-3 sm:p-5 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-sm font-bold text-gray-900"><i class="fas fa-history mr-1.5 text-blue-600"></i>Pengajuan Terbaru</h3>
        <a href="{{ route('siswa.pengajuan.index') }}" class="text-[11px] font-bold text-blue-600">Lihat Semua →</a>
    </div>

    @if($pengajuanTerbaru->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($pengajuanTerbaru as $pengajuan)
                @php
                    $badges = [
                        'menunggu'  => 'bg-amber-100 text-amber-700 border-amber-200',
                        'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                        'keluar'    => 'bg-sky-100 text-sky-700 border-sky-200',
                        'selesai'   => 'bg-gray-100 text-gray-600 border-gray-200',
                    ];
                @endphp
                <div class="p-4 active:bg-blue-50/40 transition-colors">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0">
                            <h4 class="font-mono font-bold text-gray-800 text-xs truncate">{{ $pengajuan->nomor_surat }}</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $pengajuan->created_at->format('d M Y, H:i') }} • <span class="capitalize">{{ str_replace('_', ' ', $pengajuan->kategori) }}</span></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $badges[$pengajuan->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                            {{ ucfirst($pengajuan->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1.5 truncate"><span class="font-semibold text-gray-700">Tujuan:</span> {{ $pengajuan->tujuan }}</p>
                    <div class="mt-2.5 flex gap-2">
                        <a href="{{ route('siswa.pengajuan.show', $pengajuan) }}"
                           class="inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-500/20 active:scale-95 transition-transform">
                            <i class="fas fa-eye mr-1.5"></i>Detail
                        </a>
                        @if(in_array($pengajuan->status, ['disetujui', 'selesai']))
                            <a href="{{ route('siswa.cetak', $pengajuan) }}"
                               class="inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold text-white bg-emerald-600 shadow-md shadow-emerald-500/20 active:scale-95 transition-transform">
                                <i class="fas fa-print mr-1.5"></i>Cetak
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-300 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
            <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan dispensasi</p>
            <a href="{{ route('siswa.pengajuan.create') }}"
               class="inline-flex items-center mt-3 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg shadow-blue-500/30">
                <i class="fas fa-plus mr-1.5"></i>Buat Pengajuan Pertama
            </a>
        </div>
    @endif
</div>
@endsection