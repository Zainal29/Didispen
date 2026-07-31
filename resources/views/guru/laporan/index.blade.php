@extends('guru.layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan Dispensasi & Aktivitas')

@section('content')
@include('components.alert')

<div class="bg-white rounded-lg shadow">
    {{-- Header & Tombol Export --}}
    <div class="p-5 border-b flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Laporan Dispensasi Siswa</h3>
            <p class="text-sm text-gray-500">Data dispensasi yang diproses oleh Anda sebagai guru piket.</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('guru.laporan.pdf', request()->query()) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition text-sm font-medium">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
            <a href="{{ route('guru.laporan.excel', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm font-medium">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="p-5 bg-gray-50 border-b">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-800 text-white rounded px-4 py-2 hover:bg-gray-900 transition">
                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 bg-gray-50 border-b">
        <div class="text-center p-3 bg-white rounded shadow-sm border border-gray-100">
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-xs text-gray-600 uppercase font-semibold mt-1">Total Diproses</p>
        </div>
        <div class="text-center p-3 bg-white rounded shadow-sm border border-gray-100">
            <p class="text-2xl font-bold text-green-600">{{ $stats['disetujui'] ?? 0 }}</p>
            <p class="text-xs text-gray-600 uppercase font-semibold mt-1">Disetujui</p>
        </div>
        <div class="text-center p-3 bg-white rounded shadow-sm border border-gray-100">
            <p class="text-2xl font-bold text-red-600">{{ $stats['ditolak'] ?? 0 }}</p>
            <p class="text-xs text-gray-600 uppercase font-semibold mt-1">Ditolak</p>
        </div>
        <div class="text-center p-3 bg-white rounded shadow-sm border border-gray-100">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['selesai'] ?? 0 }}</p>
            <p class="text-xs text-gray-600 uppercase font-semibold mt-1">Selesai</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Jam Keluar</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($dispensasi as $d)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-mono text-sm">{{ $d->nomor_surat }}</td>
                    <td class="p-3 text-sm">{{ $d->created_at->format('d/m/Y') }}</td>
                    <td class="p-3 font-semibold">{{ $d->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $d->siswa->kelas->nama_kelas }}</td>
                    <td class="p-3 text-sm font-medium text-gray-700">
                        {{ $d->jam_keluar }} <!-- Tanpa ->format() karena sekarang VARCHAR -->
                    </td>
                    <td class="p-3">
                        @php
                            $colors = [
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'selesai' => 'bg-blue-100 text-blue-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$d->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($d->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>Tidak ada data laporan yang sesuai dengan filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dispensasi->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $dispensasi->links() }}
    </div>
    @endif
</div>

{{-- Info Tambahan untuk Laporan Checklog Pribadi --}}
<div class="mt-6 bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex items-start">
    <i class="fas fa-info-circle text-indigo-600 mt-1 mr-3"></i>
    <div>
        <h4 class="font-semibold text-indigo-800">Butuh Laporan Kehadiran/Keluar Pribadi Anda?</h4>
        <p class="text-sm text-indigo-700 mt-1">
            Untuk melihat atau mencetak riwayat waktu keluar dan kembali Anda sendiri, silakan kunjungi menu 
            <a href="{{ route('guru.checklog.index') }}" class="underline font-bold hover:text-indigo-900">Keluar/Masuk (Checklog)</a>. 
            Anda bisa mencetak halaman tersebut langsung menggunakan fitur Print browser (Ctrl+P).
        </p>
    </div>
</div>
@endsection