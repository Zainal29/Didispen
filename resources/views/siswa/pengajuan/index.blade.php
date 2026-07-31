@extends('siswa.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Pengajuan</h3>
        <a href="{{ route('siswa.pengajuan.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm transition">
            <i class="fas fa-plus mr-1"></i> Buat Pengajuan Baru
        </a>
    </div>

    {{-- Filter --}}
    <div class="p-4 bg-gray-50 border-b">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="keluar" {{ request('status') == 'keluar' ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-left">Tujuan</th>
                    <th class="p-3 text-left">Waktu</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($pengajuan as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-mono text-sm">{{ $p->nomor_surat }}</td>
                    <td class="p-3 text-sm">{{ $p->created_at->format('d/m/Y') }}</td>
                    <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $p->kategori) }}</td>
                    <td class="p-3 text-sm">{{ $p->tujuan }}</td>
                    
                    {{-- KOLOM WAKTU YANG SUDAH DIPERBAIKI (Tanpa ->format) --}}
                    <td class="p-3 text-sm font-medium text-gray-700">
                        {{ $p->jam_keluar }} - {{ $p->jam_kembali }}
                    </td>
                    
                    <td class="p-3">
                        @php
                            $colors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'keluar' => 'bg-blue-100 text-blue-800',
                                'selesai' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$p->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ route('siswa.pengajuan.show', $p) }}" class="text-indigo-600 hover:text-indigo-800" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(in_array($p->status, ['disetujui', 'selesai']))
                        <a href="{{ route('siswa.cetak', $p) }}" class="text-green-600 hover:text-green-800" title="Cetak Surat">
                            <i class="fas fa-print"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>Belum ada pengajuan dispensasi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>
@endsection