@extends('guru.layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan Dispensasi & Aktivitas')
@section('content')

{{-- ============ HEADER + EXPORT ============ --}}
<div class="bg-white border border-gray-200/90 rounded-2xl shadow-sm p-5 mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center space-x-3.5 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-report-analytics text-2xl"></i>
        </div>
        <div class="min-w-0">
            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Laporan Dispensasi Siswa</h3>
            <p class="text-xs text-gray-500 mt-0.5 truncate">Rekap data dispensasi yang diproses oleh guru piket.</p>
        </div>
    </div>
    <div class="flex gap-2.5 w-full sm:w-auto">
        <a href="{{ route('guru.laporan.pdf', request()->query()) }}" target="_blank"
           class="flex-1 sm:flex-none inline-flex justify-center items-center min-h-[42px] px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm">
            <i class="ti ti-file-type-pdf text-base mr-1.5"></i>Export PDF
        </a>
        <a href="{{ route('guru.laporan.excel', request()->query()) }}"
           class="flex-1 sm:flex-none inline-flex justify-center items-center min-h-[42px] px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-sm">
            <i class="ti ti-file-spreadsheet text-base mr-1.5"></i>Export Excel
        </a>
    </div>
</div>

{{-- ============ FILTER COLLAPSIBLE ============ --}}
@php
    $hasFilter = request()->hasAny(['tanggal_dari', 'tanggal_sampai', 'status']) && (request('tanggal_dari') || request('tanggal_sampai') || request('status'));
@endphp
<div class="bg-white border border-gray-200/90 rounded-2xl shadow-sm p-4 sm:p-5 mb-5"
     x-data="{ openFilter: {{ $hasFilter ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between cursor-pointer select-none"
         @click="openFilter = !openFilter">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-sm">
                <i class="ti ti-filter text-base"></i>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-gray-900">Filter Data Laporan</h4>
                <p class="text-[11px] text-gray-500">Sesuaikan rentang tanggal & status pengajuan</p>
            </div>
            @if($hasFilter)
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                    Filter Aktif
                </span>
            @endif
        </div>
        <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform duration-200"
                :class="openFilter ? 'rotate-180' : ''">
            <i class="ti ti-chevron-down text-lg"></i>
        </button>
    </div>

    <form method="GET" x-show="openFilter" x-cloak class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   class="w-full h-11 px-3.5 rounded-xl border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   class="w-full h-11 px-3.5 rounded-xl border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
            <select name="status"
                    class="w-full h-11 px-3.5 rounded-xl border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                <option value="">Semua Status</option>
                <option value="disetujui"  {{ request('status') == 'disetujui'  ? 'selected' : '' }}>Disetujui</option>
                <option value="keluar"     {{ request('status') == 'keluar'     ? 'selected' : '' }}>Sedang Keluar</option>
                <option value="selesai"    {{ request('status') == 'selesai'    ? 'selected' : '' }}>Selesai</option>
                <option value="ditolak"    {{ request('status') == 'ditolak'    ? 'selected' : '' }}>Ditolak</option>
                <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                <option value="kadaluarsa" {{ request('status') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            @if($hasFilter)
                <a href="{{ route('guru.laporan.index') }}"
                   class="inline-flex justify-center items-center h-11 px-3.5 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors"
                   title="Reset Filter">
                    <i class="ti ti-refresh text-sm"></i>
                </a>
            @endif
            <button type="submit"
                    class="flex-1 inline-flex justify-center items-center h-11 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                <i class="ti ti-filter text-base mr-2"></i>Terapkan Filter
            </button>
        </div>
    </form>
</div>

{{-- ============ STATISTIK ============ --}}
<div class="grid grid-cols-2 gap-3 sm:gap-4 mb-5 md:grid-cols-4">
    @php
    $cards = [
        ['Total Diproses', $stats['total'] ?? 0, 'text-gray-900', 'ti ti-list-check', 'bg-gray-100 text-gray-700'],
        ['Disetujui', $stats['disetujui'] ?? 0, 'text-emerald-600', 'ti ti-circle-check', 'bg-emerald-50 border border-emerald-100 text-emerald-600'],
        ['Ditolak', $stats['ditolak'] ?? 0, 'text-red-600', 'ti ti-circle-x', 'bg-red-50 border border-red-100 text-red-600'],
        ['Selesai', $stats['selesai'] ?? 0, 'text-blue-600', 'ti ti-flag', 'bg-blue-50 border border-blue-100 text-blue-600'],
    ];
    @endphp
    @foreach($cards as [$label, $value, $textColor, $icon, $color])
        <div class="bg-white border border-gray-200/90 rounded-2xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
            <div class="w-10 h-10 mx-auto rounded-xl {{ $color }} flex items-center justify-center mb-2.5">
                <i class="{{ $icon }} text-xl"></i>
            </div>
            <p class="text-2xl font-extrabold {{ $textColor }}">{{ $value }}</p>
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mt-1">{{ $label }}</p>
        </div>
    @endforeach
</div>

{{-- ============ TABEL / KARTU DATA ============ --}}
<div class="bg-white border border-gray-200/90 rounded-2xl shadow-sm overflow-hidden">
    {{-- MOBILE: Kartu Interaktif --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($dispensasi as $d)
            @php
                $badge = $d->status_badge;
            @endphp
            <div class="p-4 space-y-2 hover:bg-slate-50/60 transition-colors">
                <div class="flex justify-between items-start gap-2">
                    <div class="min-w-0">
                        <span class="font-mono font-bold text-slate-900 text-xs block">{{ $d->nomor_surat }}</span>
                        @if($d->isRombongan())
                            <div class="mt-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    <i class="ti ti-users text-[10px]"></i> Rombongan
                                    @php $tot = ($d->anggota_siswa_count ?? $d->anggotaSiswa?->count() ?? 0) + 1; @endphp
                                    @if($tot > 1)
                                        <span class="bg-purple-200/70 text-purple-800 text-[9px] px-1 rounded-sm ml-0.5 font-mono">{{ $tot }} siswa</span>
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="mt-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="ti ti-user text-[9px]"></i> Individu
                                </span>
                            </div>
                        @endif
                        <h4 class="font-bold text-slate-900 text-sm truncate mt-1">{{ $d->siswa->nama_lengkap }}</h4>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border flex-shrink-0 {{ $badge['class'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                        {{ $badge['text'] }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-2 text-[11px] text-slate-500 pt-1.5 border-t border-slate-100">
                    <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                        <i class="ti ti-calendar text-xs text-slate-400"></i>
                        {{ $d->created_at->format('d/m/Y') }}
                    </span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1">
                        <i class="ti ti-school text-xs text-slate-400"></i>
                        {{ $d->siswa->kelas?->nama_kelas }}
                    </span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                        <i class="ti ti-clock text-xs text-slate-400"></i>
                        Jam: {{ \App\Helpers\TimeHelper::getWaktuAktual($d->jam_keluar, $d->created_at?->dayOfWeek) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mb-3"><i class="ti ti-inbox"></i></div>
                <p class="text-slate-700 font-bold text-sm">Tidak ada data laporan yang sesuai dengan filter.</p>
                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan rentang tanggal atau status filter di atas.</p>
            </div>
        @endforelse
    </div>

    {{-- DESKTOP: Tabel --}}
    <div class="hidden md:block overflow-x-auto min-w-0 w-full">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200 font-semibold">
                <tr>
                    <th class="p-4 text-left font-bold">No. Surat</th>
                    <th class="p-4 text-left font-bold">Tanggal</th>
                    <th class="p-4 text-left font-bold">Siswa</th>
                    <th class="p-4 text-left font-bold">Kelas</th>
                    <th class="p-4 text-left font-bold">Jam Keluar</th>
                    <th class="p-4 text-left font-bold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($dispensasi as $d)
                    @php
                        $badge = $d->status_badge;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="p-4 text-xs">
                            <span class="font-mono font-bold text-slate-900">{{ $d->nomor_surat }}</span>
                            @if($d->isRombongan())
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="ti ti-users text-[10px]"></i> Rombongan
                                        @php $tot = ($d->anggota_siswa_count ?? $d->anggotaSiswa?->count() ?? 0) + 1; @endphp
                                        @if($tot > 1)
                                            <span class="bg-purple-200/70 text-purple-800 text-[9px] px-1 rounded-sm ml-0.5 font-mono">{{ $tot }} siswa</span>
                                        @endif
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
                        <td class="p-4 text-slate-500 text-xs">{{ $d->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 font-bold text-slate-900">{{ $d->siswa->nama_lengkap }}</td>
                        <td class="p-4 text-xs text-slate-600 font-medium">{{ $d->siswa->kelas?->nama_kelas }}</td>
                        <td class="p-4 font-semibold text-slate-700 text-xs">{{ \App\Helpers\TimeHelper::getWaktuAktual($d->jam_keluar, $d->created_at?->dayOfWeek) }}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $badge['text'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center text-3xl mb-3"><i class="ti ti-inbox"></i></div>
                            <p class="text-gray-700 font-bold text-sm">Tidak ada data laporan yang sesuai dengan filter.</p>
                            <p class="text-xs text-gray-400 mt-1">Coba sesuaikan rentang tanggal atau status filter di atas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dispensasi->hasPages())
        <div class="p-4 border-t border-gray-200 bg-gray-50/50">{{ $dispensasi->links() }}</div>
    @endif
</div>

{{-- ============ INFO CHECKLOG ============ --}}
<div class="mt-5 bg-blue-50/70 border border-blue-200/90 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-xs">
    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
        <i class="ti ti-info-circle text-xl"></i>
    </div>
    <div>
        <h4 class="text-sm font-bold text-blue-900">Butuh Laporan Kehadiran Pribadi Anda?</h4>
        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
            Untuk melihat atau mencetak riwayat waktu keluar dan kembali Anda sendiri, kunjungi menu
            <a href="{{ route('guru.checklog.index') }}" class="underline font-bold hover:text-blue-950">Keluar/Masuk (Checklog)</a>.
            Anda bisa mencetak halaman tersebut langsung menggunakan fitur Print browser (Ctrl+P).
        </p>
    </div>
</div>
@endsection
