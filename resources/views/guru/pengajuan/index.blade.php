@extends('guru.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Daftar Pengajuan yang Anda Buat</h3>
            <p class="text-sm text-gray-500">Kelola dan pantau status dispensasi yang Anda buat secara manual.</p>
        </div>
        <a href="{{ route('guru.pengajuan.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
            <i class="fas fa-plus mr-2"></i> Buat Pengajuan Baru
        </a>
    </div>

    {{-- Tampilan Desktop (Table) --}}
    <div class="hidden lg:block overflow-x-auto">
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

    {{-- Tampilan Mobile (Card) --}}
    <div class="lg:hidden divide-y divide-gray-100">
        @forelse($pengajuan as $item)
        <div class="p-4 hover:bg-gray-50 transition-colors">
            {{-- Header Card --}}
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1 min-w-0">
                    <p class="font-mono text-xs font-bold text-gray-600 mb-1">{{ $item->nomor_surat }}</p>
                    <h4 class="font-bold text-gray-900 text-sm">{{ $item->siswa->nama_lengkap }}</h4>
                    <p class="text-xs text-gray-500">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                @php
                    $statusClass = match($item->status) {
                        'disetujui' => 'bg-emerald-100 text-emerald-700',
                        'keluar' => 'bg-sky-100 text-sky-700',
                        'selesai' => 'bg-gray-100 text-gray-700',
                        default => 'bg-amber-100 text-amber-700'
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusClass }} ml-2 flex-shrink-0">
                    {{ $item->status }}
                </span>
            </div>

            {{-- Info Grid --}}
            <div class="space-y-2 mb-3">
                <div class="flex items-start text-xs">
                    <i class="fas fa-tag text-gray-400 mt-0.5 mr-2 w-4"></i>
                    <div>
                        <span class="text-gray-500">Kategori:</span>
                        <span class="font-semibold text-gray-800 ml-1">{{ str_replace('_', ' ', $item->kategori) }}</span>
                    </div>
                </div>
                <div class="flex items-start text-xs">
                    <i class="fas fa-clock text-gray-400 mt-0.5 mr-2 w-4"></i>
                    <div>
                        <span class="text-gray-500">Jam:</span>
                        <div class="font-semibold text-gray-800 ml-1">
                            <p>{{ $item->jam_keluar }}</p>
                            <p class="text-gray-500">s/d {{ $item->jam_kembali }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Button --}}
            <a href="{{ route('guru.pengajuan.show', $item) }}" class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition-colors">
                <i class="fas fa-eye mr-2"></i> Lihat Detail
            </a>
        </div>
        @empty
        <div class="p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center text-2xl mb-3">
                <i class="fas fa-inbox"></i>
            </div>
            <p class="text-gray-500 font-semibold text-sm">Belum ada pengajuan yang Anda buat</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($pengajuan->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>
@endsection
