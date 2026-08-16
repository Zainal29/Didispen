@extends('siswa.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
@include('components.alert')

@php
    $statusColors = [
        'menunggu'  => 'text-amber-600 bg-amber-50 border-amber-200',
        'disetujui' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
        'ditolak'   => 'text-red-600 bg-red-50 border-red-200',
        'keluar'    => 'text-sky-600 bg-sky-50 border-sky-200',
        'selesai'   => 'text-gray-600 bg-gray-50 border-gray-200',
    ];
@endphp

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500"></div>
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
            <div class="relative z-10 p-4 sm:p-6 flex justify-between items-start gap-3">
                <div class="min-w-0">
                    <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest mb-0.5">Surat Dispensasi</p>
                    <h2 class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight truncate">{{ $dispensasi->nomor_surat }}</h2>
                    <p class="text-blue-100 text-[11px] mt-1.5">
                        <i class="far fa-calendar-plus mr-1"></i>Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                <span class="px-3.5 py-1.5 rounded-full bg-white text-xs sm:text-sm font-bold shadow-lg flex-shrink-0 {{ $statusColors[$dispensasi->status] ?? 'text-gray-600' }}">
                    {{ ucfirst($dispensasi->status) }}
                </span>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Kolom Kiri: Informasi Dispensasi --}}
                <div class="space-y-3.5">
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2">Informasi Dispensasi</h3>

                    <div>
                        <span class="text-gray-500 text-[11px]">Kategori</span>
                        <p class="font-bold text-gray-800 text-sm capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px]">Alasan</span>
                        <p class="font-semibold text-gray-800 text-sm leading-relaxed">{{ $dispensasi->alasan }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px]">Tujuan</span>
                        <p class="font-bold text-gray-800 text-sm">{{ $dispensasi->tujuan }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-[11px]">Lokasi</span>
                        <p class="font-bold text-gray-800 text-sm">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 pt-1">
                        <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3">
                            <span class="text-blue-500 text-[10px] font-bold uppercase tracking-wider block mb-1">Jam Keluar</span>
                            <p class="font-black text-blue-800 text-sm">{{ $dispensasi->jam_keluar }}</p>
                            <p class="text-[10px] text-blue-600 mt-1">
                                <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                            </p>
                        </div>
                        <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3">
                            <span class="text-blue-500 text-[10px] font-bold uppercase tracking-wider block mb-1">Jam Kembali</span>
                            <p class="font-black text-blue-800 text-sm">{{ $dispensasi->jam_kembali }}</p>
                            <p class="text-[10px] text-blue-600 mt-1">
                                <i class="far fa-clock mr-1"></i>{{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                            </p>
                        </div>
                    </div>

                    @if($dispensasi->catatan_admin)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5">
                            <span class="text-amber-700 text-[10px] font-bold uppercase tracking-wider block mb-1">Catatan Guru Piket</span>
                            <p class="text-amber-800 text-sm font-medium">{{ $dispensasi->catatan_admin }}</p>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Data Siswa, Guru Piket, & QR Code --}}
                <div class="space-y-3.5">
                    
                    {{-- Data Siswa --}}
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-2.5">
                            <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
                        </h3>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-500">Nama</span>
                                <span class="font-bold text-gray-800 text-right">{{ $dispensasi->siswa->nama_lengkap }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-500">NIS</span>
                                <span class="font-mono text-gray-800">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-500">Kelas</span>
                                <span class="font-bold text-gray-800 text-right">{{ $dispensasi->siswa->kelas->nama_kelas }} ({{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }})</span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Guru Piket --}}
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-2.5">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>Guru Piket
                        </h3>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-500">Nama</span>
                                <span class="font-bold text-gray-800 text-right">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? 'GURU PIKET UTAMA' }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-500">Tanggal</span>
                                <span class="text-gray-800">{{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN QR CODE --}}
                    @if($dispensasi->qr_code && $dispensasi->status === 'disetujui')
                        <div class="bg-blue-50/70 border-2 border-dashed border-blue-200 rounded-xl p-4 text-center">
                            <h4 class="text-sm font-bold text-blue-800 mb-1">
                                <i class="fas fa-qrcode mr-2"></i>QR Code Aktif
                            </h4>
                            <p class="text-[11px] text-blue-600 mb-3">Tunjukkan ke Satpam saat keluar</p>
                            
                            <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm inline-block">
                                <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-36 h-36 sm:w-44 sm:h-44 object-contain">
                            </div>
                            
                            <div class="mt-3 flex justify-center gap-2">
                                <a href="{{ asset('storage/' . $dispensasi->qr_code) }}" download 
                                   class="flex-1 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors shadow-md shadow-blue-500/20">
                                    <i class="fas fa-download mr-1"></i> Simpan
                                </a>
                                <button onclick="window.print()" 
                                        class="flex-1 px-3 py-2 rounded-xl bg-gray-700 hover:bg-gray-800 text-white text-xs font-bold transition-colors">
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </button>
                            </div>
                        </div>
                    @elseif(in_array($dispensasi->status, ['keluar', 'selesai']))
                        <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-xl p-4 text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-lg mb-2">
                                <i class="fas fa-check-circle text-gray-500"></i>
                            </div>
                            <h4 class="text-sm font-bold text-gray-700 mb-1">
                                QR Code Sudah Di-Scan (Tidak Aktif)
                            </h4>
                            <p class="text-[11px] text-gray-500">
                                Dispensasi ini sudah pernah di-scan oleh Satpam (Status: <strong class="capitalize">{{ $dispensasi->status }}</strong>) dan QR Code tidak dapat di-scan lagi.
                            </p>
                        </div>
                    @elseif($dispensasi->status === 'menunggu')
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                            <i class="fas fa-hourglass-half text-amber-500 text-2xl mb-2"></i>
                            <p class="text-xs text-amber-800 font-semibold">Menunggu persetujuan guru piket.<br>QR Code akan muncul setelah disetujui.</p>
                        </div>
                    @elseif($dispensasi->status === 'ditolak')
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                            <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                            <p class="text-xs text-red-800 font-semibold">Pengajuan ditolak.<br>Tidak ada QR Code yang diterbitkan.</p>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Tombol Aksi Bawah --}}
            <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between gap-2.5">
                <a href="{{ route('siswa.pengajuan.index') }}" 
                   class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat
                </a>
                
                @if(in_array($dispensasi->status, ['disetujui', 'selesai']))
                    <a href="{{ route('siswa.cetak', $dispensasi) }}" 
                       class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all">
                        <i class="fas fa-file-pdf mr-2"></i>Cetak Surat Dispensasi
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection