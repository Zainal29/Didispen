@extends('guru.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden w-full min-w-0">
    {{-- Header --}}
    <div class="p-4 sm:p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
        <div class="min-w-0">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-blue-600"></i> Daftar Pengajuan Dispensasi
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Verifikasi dan pantau status seluruh dispensasi siswa.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto h-10 px-3 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu"   {{ request('status') == 'menunggu'   ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui"  {{ request('status') == 'disetujui'  ? 'selected' : '' }}>Disetujui</option>
                    <option value="keluar"     {{ request('status') == 'keluar'     ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="selesai"    {{ request('status') == 'selesai'    ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak"    {{ request('status') == 'ditolak'    ? 'selected' : '' }}>Ditolak</option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="kadaluarsa" {{ request('status') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </form>
            <a href="{{ route('guru.pengajuan.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[40px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors flex-shrink-0">
                <i class="fas fa-plus mr-1.5"></i> Buat Pengajuan Baru
            </a>
        </div>
    </div>

    {{-- Tampilan Desktop (Table) --}}
    <div class="hidden lg:block overflow-x-auto min-w-0 w-full">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">No. Surat</th>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Jam Keluar - Kembali</th>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pengajuan as $item)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $item->nomor_surat }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 text-sm">{{ $item->siswa->nama_lengkap }}</p>
                        <p class="text-xs text-gray-500">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4 capitalize text-gray-700 text-sm">{{ str_replace('_', ' ', $item->kategori) }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        <p class="font-medium text-gray-900">{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_keluar, $item->created_at?->dayOfWeek) }}</p>
                        <p class="text-gray-400">s/d {{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_kembali, $item->created_at?->dayOfWeek) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $badge = $item->status_badge;
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold border {{ $badge['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['text'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('guru.pengajuan.show', $item) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-eye mr-1.5"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <p class="text-gray-700 font-semibold text-sm">Belum ada data pengajuan dispensasi.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tampilan Mobile (Card) --}}
    <div class="lg:hidden divide-y divide-gray-100">
        @forelse($pengajuan as $item)
        <div class="p-4 hover:bg-gray-50/50 transition-colors">
            <div class="flex justify-between items-start gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="font-mono text-[10px] text-gray-500 mb-0.5">{{ $item->nomor_surat }}</p>
                    <h4 class="font-semibold text-gray-900 text-sm truncate">{{ $item->siswa->nama_lengkap }}</h4>
                    <p class="text-xs text-gray-500">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                @php
                    $badge = $item->status_badge;
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-semibold border flex-shrink-0 {{ $badge['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                    {{ $badge['text'] }}
                </span>
            </div>

            <div class="space-y-1.5 mb-3 text-xs">
                <div class="flex items-start">
                    <i class="fas fa-tag text-gray-400 mt-0.5 mr-2 w-4 flex-shrink-0"></i>
                    <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $item->kategori) }}</span>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-clock text-gray-400 mt-0.5 mr-2 w-4 flex-shrink-0"></i>
                    <div class="text-gray-600">
                        <span class="font-medium text-gray-900">{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_keluar, $item->created_at?->dayOfWeek) }}</span>
                        <span class="text-gray-400 mx-1">s/d</span>
                        <span>{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_kembali, $item->created_at?->dayOfWeek) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('guru.pengajuan.show', $item) }}" class="inline-flex items-center justify-center w-full px-3 py-2.5 min-h-[44px] bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200">
                <i class="fas fa-eye mr-1.5"></i> Lihat Detail
            </a>
        </div>
        @empty
        <div class="p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                <i class="fas fa-inbox"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm">Belum ada data pengajuan dispensasi.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($pengajuan->hasPages())
    <div class="p-4 border-t border-gray-200 bg-gray-50/50">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>
@endsection
