@extends('satpam.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Satpam')

@section('content')

{{-- Statistik Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <p class="text-gray-500 text-sm">Menunggu Keluar</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['menunggu_keluar'] }}</h3>
        <p class="text-xs text-gray-400 mt-1">Dispensasi disetujui</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm">Sedang Keluar</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_keluar'] }}</h3>
        <p class="text-xs text-gray-400 mt-1">Di luar sekolah</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <p class="text-gray-500 text-sm">Sudah Kembali</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['selesai'] }}</h3>
        <p class="text-xs text-gray-400 mt-1">Hari ini</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <p class="text-gray-500 text-sm">Total Hari Ini</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['hari_ini'] }}</h3>
        <p class="text-xs text-gray-400 mt-1">Semua dispensasi</p>
    </div>
</div>

{{-- Tabel: Menunggu Konfirmasi Keluar --}}
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-5 border-b bg-yellow-50">
        <h3 class="text-lg font-bold text-yellow-800">
            <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi Keluar
        </h3>
        <p class="text-sm text-yellow-700">Siswa dengan dispensasi disetujui, belum keluar</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Nama Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Jam Keluar</th>
                    <th class="p-3 text-left">Jam Kembali</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($menungguKeluar as $s)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-mono text-sm">{{ $s->nomor_surat }}</td>
                    <td class="p-3 font-semibold">{{ $s->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $s->siswa->kelas->nama_kelas }} - {{ $s->siswa->kelas->jurusan->nama_jurusan }}</td>
                    <td class="p-3 text-sm font-medium text-indigo-600">{{ $s->jam_keluar }}</td>
                    <td class="p-3 text-sm font-medium text-indigo-600">{{ $s->jam_kembali }}</td>
                    <td class="p-3 text-center">
                        <form method="POST" action="{{ route('satpam.konfirmasi.keluar', $s) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition" onclick="return confirm('Konfirmasi {{ $s->siswa->nama_lengkap }} KELUAR dari sekolah?')">
                                <i class="fas fa-door-open mr-1"></i>Konfirmasi Keluar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">
                        <i class="fas fa-check-circle text-3xl text-green-300 mb-2"></i>
                        <p>Tidak ada siswa yang menunggu konfirmasi keluar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tabel: Siswa Sedang Keluar --}}
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b bg-blue-50">
        <h3 class="text-lg font-bold text-blue-800">
            <i class="fas fa-walking mr-2"></i>Siswa Sedang di Luar Sekolah
        </h3>
        <p class="text-sm text-blue-700">Siswa yang sudah keluar, belum kembali</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Jam Keluar Aktual</th>
                    <th class="p-3 text-left">Batas Kembali</th>
                    <th class="p-3 text-left">Guru Piket</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($siswaKeluar as $s)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-semibold">{{ $s->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $s->siswa->kelas->nama_kelas }} - {{ $s->siswa->kelas->jurusan->nama_jurusan }}</td>
                    <td class="p-3 text-sm">
                        {{ $s->waktu_keluar_aktual ? $s->waktu_keluar_aktual->format('H:i') : '-' }}
                    </td>
                    <td class="p-3 font-semibold text-indigo-600">{{ $s->jam_kembali }}</td>
                    <td class="p-3 text-sm">{{ $s->guruPiket->guru->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 text-center">
                        <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $s) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition" onclick="return confirm('Konfirmasi {{ $s->siswa->nama_lengkap }} KEMBALI ke sekolah?')">
                                <i class="fas fa-door-closed mr-1"></i>Konfirmasi Kembali
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">
                        <i class="fas fa-check-circle text-3xl text-green-300 mb-2"></i>
                        <p>Tidak ada siswa yang sedang di luar sekolah</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection