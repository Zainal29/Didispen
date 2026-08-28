@extends('guru.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Daftar Pengajuan yang Anda Buat</h3>
            <p class="text-sm text-gray-500">Kelola dan pantau status dispensasi yang Anda buat secara manual.</p>
        </div>
        <a href="{{ route('guru.pengajuan.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
            <i class="fas fa-plus mr-2"></i> Buat Pengajuan Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-bold uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">No. Surat</th>
                    <th class="px-6 py-4">Siswa</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4">Jam Keluar - Kembali</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pengajuan as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs font-bold text-gray-700">{{ $item->nomor_surat }}</td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-gray-900">{{ $item->siswa->nama_lengkap }}</p>
                        <p class="text-xs text-gray-500">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $item->kategori) }}</td>
                    <td class="px-6 py-4 text-xs">
                        <p class="text-gray-700">{{ $item->jam_keluar }}</p>
                        <p class="text-gray-500">s/d {{ $item->jam_kembali }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusClass = match($item->status) {
                                'disetujui' => 'bg-emerald-100 text-emerald-700',
                                'keluar' => 'bg-sky-100 text-sky-700',
                                'selesai' => 'bg-gray-100 text-gray-700',
                                default => 'bg-amber-100 text-amber-700'
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusClass }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('guru.pengajuan.show', $item) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs">
                            <i class="fas fa-eye mr-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada pengajuan yang Anda buat.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>
@endsection
