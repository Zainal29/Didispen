@extends('guru.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Guru Piket')

@section('content')
@include('components.alert')

{{-- Info Piket Hari Ini --}}
@if($stats['piket_hari_ini'] ?? null)
<div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold mb-1">Jadwal Piket Hari Ini</h3>
            <p class="text-blue-100">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <div class="mt-3 flex items-center space-x-4">
                <span class="bg-white bg-opacity-20 px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fas fa-clock mr-2"></i>Shift {{ ucfirst($stats['piket_hari_ini']->shift) }}
                </span>
                <span class="bg-white bg-opacity-20 px-4 py-2 rounded-full text-sm">
                    <i class="fas fa-user mr-2"></i>{{ auth()->user()->name }}
                </span>
            </div>
        </div>
        <div class="text-5xl opacity-20">
            <i class="fas fa-calendar-check"></i>
        </div>
    </div>
</div>
@else
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6 rounded">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-3"></i>
        <div>
            <h3 class="text-yellow-800 font-semibold mb-1">Tidak Ada Jadwal Piket</h3>
            <p class="text-yellow-700 text-sm">Anda tidak memiliki jadwal piket hari ini.</p>
        </div>
    </div>
</div>
@endif

{{-- NOTIFIKASI SISWA SEDANG KELUAR --}}
@if(isset($siswaKeluar) && $siswaKeluar->count() > 0)
<div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
            <div>
                <h3 class="text-xl font-bold">⚠️ PERHATIAN: Ada Siswa Sedang Keluar!</h3>
                <p class="text-orange-100 text-sm mt-1">{{ $siswaKeluar->count() }} siswa sedang di luar area sekolah (Menunggu scan Satpam)</p>
            </div>
        </div>
        <div class="text-5xl opacity-30">
            <i class="fas fa-walking"></i>
        </div>
    </div>

    <div class="bg-white bg-opacity-20 rounded-lg p-4 mt-4">
        <h4 class="font-semibold mb-3">Daftar Siswa yang Sedang Keluar:</h4>
        <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
            @foreach($siswaKeluar as $item)
            <div class="flex flex-col md:flex-row md:justify-between md:items-center bg-white bg-opacity-10 rounded p-3 border border-white border-opacity-20">
                <div class="mb-2 md:mb-0">
                    <p class="font-bold text-lg">{{ $item->siswa->nama_lengkap }}</p>
                    <p class="text-sm text-orange-100">
                        {{ $item->siswa->kelas->nama_kelas ?? '-' }} - {{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}
                    </p>
                </div>
                <div class="text-right flex flex-col md:items-end gap-1">
                    <p class="text-sm font-semibold">Keluar: {{ $item->jam_keluar }}</p>
                    <p class="text-sm font-semibold text-yellow-200">Batas Kembali: {{ $item->jam_kembali }}</p>
                    <span class="mt-1 px-3 py-1 bg-white text-orange-600 rounded text-xs font-bold shadow-sm">
                        <i class="fas fa-clock mr-1"></i>Menunggu Satpam
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Statistik Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Menunggu</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pending'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Sedang Keluar</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['keluar'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
                <i class="fas fa-walking text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Selesai</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['selesai'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Hari Ini</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-purple-100 rounded-lg">
                <i class="fas fa-tasks text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- Pending Dispensasi --}}
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">
            <i class="fas fa-list mr-2 text-blue-600"></i>
            Pengajuan Menunggu Persetujuan
        </h3>
        @if(isset($pendingDispensasi) && $pendingDispensasi->count() > 0)
        <a href="{{ route('guru.pengajuan.index') }}" class="text-sm text-blue-600 hover:underline font-medium">
            Lihat Semua &rarr;
        </a>
        @endif
    </div>

    <div class="p-5">
        @if(isset($pendingDispensasi) && $pendingDispensasi->count() > 0)
        <div class="space-y-3">
            @foreach($pendingDispensasi->take(5) as $item)
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $item->siswa->nama_lengkap }}</h4>
                        <p class="text-sm text-gray-600">{{ $item->siswa->kelas->nama_kelas }} - {{ $item->siswa->kelas->jurusan->nama_jurusan }}</p>
                    </div>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">
                        Menunggu
                    </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Kategori:</strong> {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}</p>
                    <p><strong>Tujuan:</strong> {{ $item->tujuan }}</p>
                    <p><strong>Waktu:</strong> {{ $item->jam_keluar }} - {{ $item->jam_kembali }}</p>
                </div>
                <div class="mt-3 flex space-x-2">
                    <a href="{{ route('guru.pengajuan.show', $item) }}" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        <i class="fas fa-eye mr-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Tidak ada pengajuan yang menunggu</p>
            <p class="text-gray-400 text-sm mt-1">Semua dispensasi telah diproses</p>
        </div>
        @endif
    </div>
</div>
@endsection
