@extends('admin.layouts.app')

@section('title', 'Laporan Rekapitulasi Dispensasi')
@section('page-title', 'Laporan Dispensasi Siswa')

@section('content')
<div class="space-y-6">

    {{-- HEADER BANNER & STATS --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                    <i class="ti ti-report-analytics text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Laporan Realisasi Dispensasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi siswa yang nyata keluar gerbang sekolah, ketepatan waktu kembali, dan kepatuhan izin.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                    <i class="ti ti-check-double text-sm"></i>
                    <span>{{ $statusLabel }}</span>
                </span>
            </div>
        </div>

        {{-- QUICK FILTER TABS / PRESET BUTTONS --}}
        @php
            $qParams = request()->except(['status', 'page']);
        @endphp
        <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 no-scrollbar text-xs">
            {{-- 1. Semua Realisasi (Default) --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'realisasi'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ ($currentStatus === 'realisasi' || empty($currentStatus)) ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-list-check"></i>
                <span>Semua Realisasi</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ ($currentStatus === 'realisasi' || empty($currentStatus)) ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $counts['realisasi'] }}
                </span>
            </a>

            {{-- 2. Sedang Keluar --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'keluar'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'keluar' ? 'bg-sky-600 text-white border-sky-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-door-exit"></i>
                <span>Sedang Keluar</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'keluar' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-800' }}">
                    {{ $counts['keluar'] }}
                </span>
            </a>

            {{-- 3. Kembali Tepat Waktu / Lebih Awal --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'selesai_awal'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'selesai_awal' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-circle-check"></i>
                <span>Kembali Lebih Awal / Tepat</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'selesai_awal' ? 'bg-emerald-500 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $counts['selesai_awal'] }}
                </span>
            </a>

            {{-- 4. Terlambat Kembali --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'terlambat'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'terlambat' ? 'bg-amber-600 text-white border-amber-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-alarm"></i>
                <span>Terlambat Kembali</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'terlambat' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ $counts['terlambat'] }}
                </span>
            </a>

            {{-- 5. Kadaluarsa (Tidak Scan Kembali) --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'tidak_kembali'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'tidak_kembali' ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-alert-triangle"></i>
                <span>Tidak Scan Kembali</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'tidak_kembali' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-800' }}">
                    {{ $counts['tidak_kembali'] }}
                </span>
            </a>

            {{-- 6. Sampai Pulang --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'sampai_pulang'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'sampai_pulang' ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="ti ti-home-check"></i>
                <span>Izin Sampai Pulang</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'sampai_pulang' ? 'bg-purple-500 text-white' : 'bg-purple-100 text-purple-800' }}">
                    {{ $counts['sampai_pulang'] }}
                </span>
            </a>

            {{-- 7. Semua Riwayat (Audit) --}}
            <a href="{{ route('admin.laporan.index', array_merge($qParams, ['status' => 'semua'])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 border {{ $currentStatus === 'semua' ? 'bg-slate-800 text-white border-slate-800 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200' }}">
                <i class="ti ti-archive"></i>
                <span>Semua (Audit)</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] {{ $currentStatus === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-600' }}">
                    {{ $counts['semua'] }}
                </span>
            </a>
        </div>
    </div>

    {{-- FILTER FORM & EXPORT TOOLBAR CARD --}}
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        {{-- FILTER HEADER --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                {{-- Status Selector --}}
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status Realisasi</label>
                    <select name="status" class="w-full h-10 border border-slate-300 rounded-xl px-3 text-xs bg-white text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none cursor-pointer">
                        <option value="realisasi" {{ ($currentStatus === 'realisasi' || empty($currentStatus)) ? 'selected' : '' }}>Semua Realisasi Siswa Keluar (Default)</option>
                        <option value="keluar" {{ $currentStatus === 'keluar' ? 'selected' : '' }}>Sedang Keluar (Belum Kembali)</option>
                        <option value="selesai_awal" {{ $currentStatus === 'selesai_awal' ? 'selected' : '' }}>Selesai (Kembali Lebih Awal / Tepat)</option>
                        <option value="terlambat" {{ $currentStatus === 'terlambat' ? 'selected' : '' }}>Selesai (Terlambat Kembali)</option>
                        <option value="tidak_kembali" {{ $currentStatus === 'tidak_kembali' ? 'selected' : '' }}>Kadaluarsa (Tidak Scan Kembali)</option>
                        <option value="sampai_pulang" {{ $currentStatus === 'sampai_pulang' ? 'selected' : '' }}>Selesai (Izin Sampai Pulang)</option>
                        <option value="semua" {{ $currentStatus === 'semua' ? 'selected' : '' }}>Semua Riwayat Lengkap (Audit Termasuk Batal & Tolak)</option>
                        <optgroup label="Status Baku Lainnya">
                            <option value="menunggu" {{ $currentStatus === 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan Guru Piket</option>
                            <option value="disetujui" {{ $currentStatus === 'disetujui' ? 'selected' : '' }}>Disetujui Guru Piket (Belum Keluar)</option>
                            <option value="ditolak" {{ $currentStatus === 'ditolak' ? 'selected' : '' }}>Ditolak Guru Piket</option>
                            <option value="dibatalkan" {{ $currentStatus === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </optgroup>
                    </select>
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Jurusan</label>
                    <select name="jurusan_id" class="w-full h-10 border border-slate-300 rounded-xl px-3 text-xs bg-white text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none cursor-pointer">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Dari --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                           class="w-full h-10 border border-slate-300 rounded-xl px-3 text-xs bg-white text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none">
                </div>

                {{-- Tanggal Sampai --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                           class="w-full h-10 border border-slate-300 rounded-xl px-3 text-xs bg-white text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none">
                </div>

                {{-- Submit & Reset Buttons --}}
                <div class="flex items-end gap-1.5">
                    <button type="submit"
                            class="flex-1 h-10 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl px-3 text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="ti ti-filter text-sm"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.laporan.index') }}"
                       class="h-10 px-3 bg-white hover:bg-slate-100 text-slate-600 border border-slate-300 rounded-xl text-xs font-semibold flex items-center justify-center transition-colors"
                       title="Reset Filter ke Default">
                        <i class="ti ti-refresh text-sm"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- ACTION TOOLBAR (EXPORT PDF, EXCEL & DIRECT PRINT) --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol Export PDF --}}
                <a href="{{ route('admin.laporan.pdf', request()->all()) }}"
                   class="min-h-[38px] bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-2xs shadow-rose-500/20">
                    <i class="ti ti-file-type-pdf text-base"></i>
                    <span>Ekspor PDF</span>
                </a>

                {{-- Tombol Export CSV / Excel --}}
                <a href="{{ route('admin.laporan.excel', request()->all()) }}"
                   class="min-h-[38px] bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-2xs shadow-emerald-500/20">
                    <i class="ti ti-file-spreadsheet text-base"></i>
                    <span>Ekspor Excel (.xls)</span>
                </a>

                {{-- Tombol Cetak Browser Langsung --}}
                <button type="button" onclick="window.print()"
                        class="min-h-[38px] bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all border border-slate-200">
                    <i class="ti ti-printer text-base"></i>
                    <span>Cetak Langsung</span>
                </button>
            </div>

            <div class="text-xs text-slate-500 font-medium self-end sm:self-center">
                Menampilkan <strong class="text-slate-900 font-bold">{{ $dispensasi->total() }}</strong> berkas
                @if(request('tanggal_dari') || request('tanggal_sampai'))
                    <span class="text-slate-400">({{ request('tanggal_dari') ?? 'Awal' }} s/d {{ request('tanggal_sampai') ?? 'Kini' }})</span>
                @endif
            </div>
        </div>

        {{-- TABLE AREA --}}
        <div class="overflow-x-auto min-w-0 w-full">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50/80 text-[11px] uppercase text-slate-400 font-bold tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="p-3.5 pl-5 text-left">No. Surat</th>
                        <th class="p-3.5 text-left">Tanggal</th>
                        <th class="p-3.5 text-left">Siswa & Kelas</th>
                        <th class="p-3.5 text-left">Kategori & Tujuan</th>
                        <th class="p-3.5 text-center">Waktu Keluar</th>
                        <th class="p-3.5 text-center">Waktu Kembali</th>
                        <th class="p-3.5 text-center">Status Realisasi</th>
                        <th class="p-3.5 pr-5 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($dispensasi as $d)
                        @php
                            $badge = $d->status_badge;
                            $scanKeluar = $d->waktu_keluar_aktual
                                ? \Carbon\Carbon::parse($d->waktu_keluar_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                                : '-';
                            $scanKembali = $d->waktu_kembali_aktual
                                ? \Carbon\Carbon::parse($d->waktu_kembali_aktual)->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                                : ($d->sampai_pulang ? 'Sampai Pulang' : '-');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- No. Surat --}}
                            <td class="p-3.5 pl-5 font-mono text-xs font-bold text-blue-600 whitespace-nowrap">
                                <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}" class="hover:underline">
                                    {{ $d->nomor_surat }}
                                </a>
                            </td>

                            {{-- Tanggal --}}
                            <td class="p-3.5 text-slate-500 whitespace-nowrap">
                                <i class="ti ti-calendar text-slate-400 mr-1"></i>
                                {{ $d->created_at?->format('d/m/Y') ?? '-' }}
                            </td>

                            {{-- Siswa & Kelas --}}
                            <td class="p-3.5">
                                <p class="font-bold text-slate-900">{{ $d->siswa?->nama_lengkap ?? '-' }}</p>
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $d->siswa?->kelas?->nama_kelas ?? '-' }} • NIS: {{ $d->siswa?->user?->nis_nip ?? '-' }}
                                </p>
                            </td>

                            {{-- Kategori & Tujuan --}}
                            <td class="p-3.5 max-w-xs">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 capitalize inline-block mb-1">
                                    {{ str_replace('_', ' ', $d->kategori) }}
                                </span>
                                <p class="text-slate-700 truncate" title="{{ $d->tujuan }}">{{ Str::limit($d->tujuan, 35) }}</p>
                            </td>

                            {{-- Waktu Keluar --}}
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-800 block">{{ $scanKeluar }}</span>
                                <span class="text-[10px] text-slate-400">Rencana: {{ $d->jam_keluar }}</span>
                            </td>

                            {{-- Waktu Kembali --}}
                            <td class="p-3.5 text-center whitespace-nowrap">
                                @if($d->sampai_pulang)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="ti ti-home-check"></i> Sampai Pulang
                                    </span>
                                @else
                                    <span class="font-mono font-bold text-slate-800 block">{{ $scanKembali }}</span>
                                    <span class="text-[10px] text-slate-400">Batas: {{ $d->jam_kembali }}</span>
                                @endif
                            </td>

                            {{-- Status Realisasi --}}
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['text'] }}
                                </span>
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="p-3.5 pr-5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 transition-colors text-xs font-semibold"
                                   title="Buka rincian lengkap dispensasi">
                                    <i class="ti ti-eye"></i>
                                    <span>Lihat</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <i class="ti ti-file-search text-4xl mb-2 text-slate-300 block"></i>
                                <p class="text-xs font-bold text-slate-700">Tidak ada data dispensasi yang sesuai dengan filter.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Coba sesuaikan pilihan status, tanggal, atau gunakan opsi "Semua Realisasi".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($dispensasi->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $dispensasi->links() }}
            </div>
        @endif
    </div>

</div>

{{-- PRINT STYLING UNTUK CETAK BROWSER LANGSUNG --}}
<style>
@media print {
    /* Sembunyikan navigasi dan filter saat tombol cetak browser diklik */
    aside, header, nav, .no-print, form, a, button {
        display: none !important;
    }
    body, main {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .shadow-2xs, .border {
        box-shadow: none !important;
        border: none !important;
    }
    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    th, td {
        border: 1px solid #94a3b8 !important;
        padding: 6px !important;
    }
}
</style>
@endsection
