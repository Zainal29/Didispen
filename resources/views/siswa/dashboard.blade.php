@extends('siswa.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Siswa')

@section('content')
@include('components.alert')

{{-- Welcome Card --}}
<div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold mb-1">Selamat Datang, {{ auth()->user()->name }}!</h3>
            <p class="text-indigo-100">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <div class="mt-3">
                <span class="bg-white bg-opacity-20 px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fas fa-bell mr-2"></i>{{ $notifikasiBelumDibaca }} Notifikasi Baru
                </span>
            </div>
        </div>
        <div class="text-6xl opacity-20">
            <i class="fas fa-user-graduate"></i>
        </div>
    </div>
</div>

{{-- ✅ KARTU QR CODE AKTIF (Hanya muncul jika ada dispensasi aktif) --}}
@if(isset($dispensasiAktif) && $dispensasiAktif->qr_code)
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 mb-6 text-white border border-blue-400">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex-1">
            <div class="flex items-center mb-3">
                <span class="bg-white text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mr-3 animate-pulse">
                    {{ $dispensasiAktif->status === 'selesai' ? 'SELESAI' : 'AKTIF' }}
                </span>
                <h3 class="text-xl font-bold"><i class="fas fa-qrcode mr-2"></i>QR Code Dispensasi Anda</h3>
            </div>
            <p class="text-blue-100 mb-4 text-sm">
                Tunjukkan layar ini kepada Petugas Satpam saat <strong>keluar</strong> dan <strong>kembali</strong> ke sekolah.
            </p>
            <div class="space-y-2 text-sm bg-white bg-opacity-10 p-3 rounded-lg backdrop-blur-sm">
                <p><i class="fas fa-file-alt w-5 text-center"></i> <strong>No. Surat:</strong> {{ $dispensasiAktif->nomor_surat }}</p>
                <p><i class="fas fa-clock w-5 text-center"></i> <strong>Waktu:</strong> {{ $dispensasiAktif->jam_keluar }} s.d {{ $dispensasiAktif->jam_kembali }}</p>
                <p><i class="fas fa-user-tie w-5 text-center"></i> <strong>Guru Piket:</strong> {{ $dispensasiAktif->guruPiket->guru->nama_lengkap ?? '-' }}</p>
            </div>
        </div>
        
        <div class="bg-white p-3 rounded-xl shadow-inner flex-shrink-0 text-center">
            <img src="{{ asset('storage/' . $dispensasiAktif->qr_code) }}" alt="QR Code Dispensasi" class="w-48 h-48 mx-auto object-contain">
            <p class="text-center text-xs text-gray-500 mt-2 font-mono">Scan di Pos Satpam</p>
            <a href="{{ asset('storage/' . $dispensasiAktif->qr_code) }}" download class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition shadow-sm">
                <i class="fas fa-download mr-1"></i> Simpan Gambar
            </a>
        </div>
    </div>
</div>
@endif

{{-- Statistik Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-gray-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-gray-100 rounded-lg">
                <i class="fas fa-file-alt text-gray-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Menunggu</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['menunggu'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Disetujui</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['disetujui'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Ditolak</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['ditolak'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Selesai</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['selesai'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
                <i class="fas fa-flag-checkered text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- Pengajuan Terbaru --}}
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">
            <i class="fas fa-history mr-2 text-indigo-600"></i>
            Pengajuan Terbaru
        </h3>
        <a href="{{ route('siswa.pengajuan.index') }}" class="text-sm text-indigo-600 hover:underline font-medium">
            Lihat Semua →
        </a>
    </div>

    <div class="p-5">
        @if($pengajuanTerbaru->count() > 0)
        <div class="space-y-3">
            @foreach($pengajuanTerbaru as $pengajuan)
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $pengajuan->nomor_surat }}</h4>
                        <p class="text-sm text-gray-600">{{ $pengajuan->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    @php
                        $colors = [
                            'menunggu' => 'bg-yellow-100 text-yellow-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            'keluar' => 'bg-blue-100 text-blue-800',
                            'selesai' => 'bg-gray-100 text-gray-800',
                        ];
                    @endphp
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$pengajuan->status] ?? 'bg-gray-100' }}">
                        {{ ucfirst($pengajuan->status) }}
                    </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Kategori:</strong> {{ ucfirst(str_replace('_', ' ', $pengajuan->kategori)) }}</p>
                    <p><strong>Tujuan:</strong> {{ $pengajuan->tujuan }}</p>
                </div>
                <div class="mt-3 flex space-x-2">
                    <a href="{{ route('siswa.pengajuan.show', $pengajuan) }}" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                        <i class="fas fa-eye mr-1"></i> Lihat Detail
                    </a>
                    @if(in_array($pengajuan->status, ['disetujui', 'selesai']))
                    <a href="{{ route('siswa.cetak', $pengajuan) }}" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada pengajuan dispensasi</p>
            <a href="{{ route('siswa.pengajuan.create') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>Buat Pengajuan Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@endsection