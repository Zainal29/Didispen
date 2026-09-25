@extends('guru.layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan Dispensasi & Aktivitas')
@section('content')
@include('components.alert')

{{-- ============ HEADER + EXPORT ============ --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 sm:p-5 mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div class="flex items-center space-x-3 min-w-0">
        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-chart-line text-lg"></i>
        </div>
        <div class="">
            <h3 class="text-sm sm:text-base font-bold text-gray-900">Laporan Dispensasi Siswa</h3>
            <p class="text-xs text-gray-500 truncate">Dispensasi yang diproses guru piket.</p>
        </div>
    </div>
    <div class="flex gap-2 w-full sm:w-auto">
        <a href="{{ route('guru.laporan.pdf', request()->query()) }}" target="_blank"
           class="flex-1 sm:flex-none inline-flex justify-center items-center min-h-[44px] px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
            <i class="fas fa-file-pdf mr-1.5"></i>Export PDF
        </a>
        <a href="{{ route('guru.laporan.excel', request()->query()) }}"
           class="flex-1 sm:flex-none inline-flex justify-center items-center min-h-[44px] px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
            <i class="fas fa-file-excel mr-1.5"></i>Export Excel
        </a>
    </div>
</div>

{{-- ============ FILTER ============ --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
            <select name="status"
                    class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                <option value="">Semua Status</option>
                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="ditolak"   {{ request('status') == 'ditolak'   ? 'selected' : '' }}>Ditolak</option>
                <option value="selesai"   {{ request('status') == 'selesai'   ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit"
                    class="w-full inline-flex justify-center items-center min-h-[44px] px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-2"></i>Terapkan Filter
            </button>
        </div>
    </form>
</div>

{{-- ============ STATISTIK ============ --}}
<div class="grid grid-cols-2 gap-2 sm:gap-4 mb-4 md:grid-cols-4">
    @php
    $cards = [
        ['Total Diproses', $stats['total'] ?? 0, 'text-gray-800', 'fa-tasks', 'bg-gray-100 text-gray-600'],
        ['Disetujui', $stats['disetujui'] ?? 0, 'text-emerald-600', 'fa-check-circle', 'bg-emerald-100 text-emerald-600'],
        ['Ditolak', $stats['ditolak'] ?? 0, 'text-red-600', 'fa-times-circle', 'bg-red-100 text-red-600'],
        ['Selesai', $stats['selesai'] ?? 0, 'text-blue-600', 'fa-flag-checkered', 'bg-blue-100 text-blue-600'],
    ];
    @endphp
    @foreach($cards as [$label, $value, $textColor, $icon, $color])
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-3 sm:p-4 text-center">
            <div class="w-9 h-9 mx-auto rounded-lg {{ $color }} flex items-center justify-center mb-2">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
            <p class="text-2xl font-bold {{ $textColor }}">{{ $value }}</p>
            <p class="text-[10px] text-gray-500 uppercase font-semibold mt-1">{{ $label }}</p>
        </div>
    @endforeach
</div>

{{-- ============ TABEL / KARTU DATA ============ --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    {{-- MOBILE: Kartu --}}
    <div class="md:hidden divide-y divide-gray-100">
        @forelse($dispensasi as $d)
            @php
                $badges = [
                    'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                    'selesai'   => 'bg-blue-100 text-blue-700 border-blue-200',
                ];
            @endphp
            <div class="p-4">
                <div class="flex justify-between items-start gap-2 mb-1.5">
                    <div class="min-w-0">
                        <p class="font-mono font-semibold text-gray-700 text-xs">{{ $d->nomor_surat }}</p>
                        <p class="font-bold text-gray-900 text-sm truncate">{{ $d->siswa->nama_lengkap }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold border flex-shrink-0 {{ $badges[$d->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                        {{ ucfirst($d->status) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    {{ $d->created_at->format('d/m/Y') }} • {{ $d->siswa->kelas?->nama_kelas }} • Jam Keluar: {{ $d->jam_keluar }}
                </p>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                <p class="text-gray-700 font-semibold text-sm">Tidak ada data laporan yang sesuai dengan filter.</p>
            </div>
        @endforelse
    </div>

    {{-- DESKTOP: Tabel --}}
    <div class="hidden md:block overflow-x-auto min-w-0 w-full">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-gray-200">
                <tr>
                    <th class="p-4 text-left font-semibold">No. Surat</th>
                    <th class="p-4 text-left font-semibold">Tanggal</th>
                    <th class="p-4 text-left font-semibold">Siswa</th>
                    <th class="p-4 text-left font-semibold">Kelas</th>
                    <th class="p-4 text-left font-semibold">Jam Keluar</th>
                    <th class="p-4 text-left font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($dispensasi as $d)
                    @php
                        $badges = [
                            'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
                            'selesai'   => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4 font-mono font-semibold text-gray-700 text-xs">{{ $d->nomor_surat }}</td>
                        <td class="p-4 text-gray-500">{{ $d->created_at->format('d/m/Y') }}</td>
                        <td class="p-4 font-semibold text-gray-900">{{ $d->siswa->nama_lengkap }}</td>
                        <td class="p-4 text-xs text-gray-500">{{ $d->siswa->kelas?->nama_kelas }}</td>
                        <td class="p-4 font-medium text-gray-700">{{ $d->jam_keluar }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold border {{ $badges[$d->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ ucfirst($d->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3"><i class="fas fa-inbox"></i></div>
                            <p class="text-gray-700 font-semibold text-sm">Tidak ada data laporan yang sesuai dengan filter.</p>
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
<div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-info-circle text-sm"></i>
    </div>
    <div>
        <h4 class="text-sm font-bold text-blue-900">Butuh Laporan Kehadiran Pribadi Anda?</h4>
        <p class="text-xs text-blue-800 mt-1 leading-relaxed">
            Untuk melihat atau mencetak riwayat waktu keluar dan kembali Anda sendiri, kunjungi menu
            <a href="{{ route('guru.checklog.index') }}" class="underline font-semibold hover:text-blue-900">Keluar/Masuk (Checklog)</a>.
            Anda bisa mencetak halaman tersebut langsung menggunakan fitur Print browser (Ctrl+P).
        </p>
    </div>
</div>
@endsection
