@extends('guru.layouts.app')

@section('title', 'Riwayat Pengajuan')
@section('page-title', 'Riwayat Pengajuan Dispensasi')

@section('content')

<div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden w-full min-w-0">
    {{-- Header --}}
    <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
        <div class="min-w-0">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="ti ti-history text-blue-600 text-lg"></i> Daftar Pengajuan Dispensasi
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Verifikasi dan pantau status seluruh dispensasi siswa.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex-1 sm:flex-none">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto h-10 px-3 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 transition-all">
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
            @if($isSedangBertugasAktif ?? false)
                <a href="{{ route('guru.pengajuan.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[40px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex-shrink-0">
                    <i class="ti ti-plus mr-1.5"></i> Buat Pengajuan Baru
                </a>
            @endif
        </div>
    </div>

    {{-- Tampilan Desktop (Table) --}}
    <div class="hidden lg:block overflow-x-auto min-w-0 w-full">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200 font-semibold">
                <tr>
                    <th class="px-6 py-3.5">No. Surat</th>
                    <th class="px-6 py-3.5">Siswa</th>
                    <th class="px-6 py-3.5">Kategori</th>
                    <th class="px-6 py-3.5">Jam Keluar - Kembali</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pengajuan as $item)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold text-slate-900 text-xs">{{ $item->nomor_surat }}</span>
                        @if($item->isOrganisasi())
                            <div class="mt-1 flex items-center gap-1 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="ti ti-users text-[10px] text-blue-600"></i> {{ $item->nama_organisasi }}
                                </span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    Organisasi
                                </span>
                            </div>
                        @else
                            <div class="mt-1">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="ti ti-user text-[9px]"></i> Individu
                                </span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-900 text-sm">{{ $item->siswa->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4 capitalize text-slate-700 text-xs">{{ str_replace('_', ' ', $item->kategori) }}</td>
                    <td class="px-6 py-4 text-xs text-slate-600">
                        <p class="font-medium text-slate-900">{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_keluar, $item->created_at?->dayOfWeek) }}</p>
                        <p class="text-slate-400">s/d {{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_kembali, $item->created_at?->dayOfWeek) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $badge = $item->status_badge;
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                            {{ $badge['text'] }}
                        </span>
                        @if($item->isMenungguHariIni())
                            <span class="block text-[10px] text-amber-600 font-medium mt-1">
                                <i class="ti ti-hourglass-low mr-0.5"></i>Kadaluarsa {{ $item->getBatasWaktuExpiredText() }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('guru.pengajuan.show', $item) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                            <i class="ti ti-eye mr-1.5 text-sm"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                            <i class="ti ti-inbox text-3xl"></i>
                        </div>
                        <p class="text-slate-700 font-bold text-sm">Belum ada data pengajuan dispensasi.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tampilan Mobile (Card) --}}
    <div class="lg:hidden divide-y divide-slate-100">
        @forelse($pengajuan as $item)
        <div class="p-4 hover:bg-slate-50/70 transition-colors">
            <div class="flex justify-between items-start gap-2 mb-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                        <p class="font-mono font-bold text-slate-900 text-xs">{{ $item->nomor_surat }}</p>
                        @if($item->isOrganisasi())
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="ti ti-users text-[10px] text-blue-600"></i> {{ $item->nama_organisasi }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                Organisasi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                <i class="ti ti-user text-[9px]"></i> Individu
                            </span>
                        @endif
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm truncate">{{ $item->siswa->nama_lengkap }}</h4>
                    <p class="text-xs text-slate-500">{{ $item->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                @php
                    $badge = $item->status_badge;
                @endphp
                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                        {{ $badge['text'] }}
                    </span>
                    @if($item->isMenungguHariIni())
                        <span class="text-[9px] text-amber-600 font-medium">
                            <i class="ti ti-hourglass-low mr-0.5"></i>Batas {{ $item->getBatasWaktuExpiredText() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-1.5 mb-3 text-xs">
                <div class="flex items-start">
                    <i class="ti ti-tag text-slate-400 mt-0.5 mr-2 text-sm flex-shrink-0"></i>
                    <span class="text-slate-600 capitalize">{{ str_replace('_', ' ', $item->kategori) }}</span>
                </div>
                <div class="flex items-start">
                    <i class="ti ti-clock text-slate-400 mt-0.5 mr-2 text-sm flex-shrink-0"></i>
                    <div class="text-slate-600">
                        <span class="font-medium text-slate-900">{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_keluar, $item->created_at?->dayOfWeek) }}</span>
                        <span class="text-slate-400 mx-1">s/d</span>
                        <span>{{ \App\Helpers\TimeHelper::getWaktuAktual($item->jam_kembali, $item->created_at?->dayOfWeek) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('guru.pengajuan.show', $item) }}" class="inline-flex items-center justify-center w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                <i class="ti ti-eye mr-1.5 text-sm"></i> Lihat Detail
            </a>
        </div>
        @empty
        <div class="p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-3">
                <i class="ti ti-inbox text-3xl"></i>
            </div>
            <p class="text-slate-700 font-bold text-sm">Belum ada data pengajuan dispensasi.</p>
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
