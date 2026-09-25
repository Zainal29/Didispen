@extends('admin.layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan Dispensasi')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-gray-100">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Filter Laporan</h3>
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <select name="status" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Status</option>
                @foreach(['menunggu','disetujui','ditolak','keluar','selesai'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="jurusan_id" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Jurusan</option>
                @foreach(\App\Models\Jurusan::all() as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="w-full h-11 border border-gray-300 rounded-lg px-3.5 text-base sm:text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="w-full h-11 min-h-[44px] bg-gray-800 text-white rounded-lg px-4 text-sm font-semibold hover:bg-gray-900 transition-colors flex items-center justify-center shadow-sm">
                <i class="fas fa-filter mr-1.5"></i> Filter
            </button>
        </form>
    </div>

    <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/70 flex flex-wrap items-center gap-2">
        {{-- Tombol Export PDF - HAPUS target="_blank" --}}
        <a href="{{ route('admin.laporan.pdf', request()->all()) }}"
           class="min-h-[44px] bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors shadow-sm">
            <i class="fas fa-file-pdf mr-1.5"></i> Export PDF
        </a>

        <a href="{{ route('admin.laporan.excel', request()->all()) }}"
           class="min-h-[44px] bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center transition-colors shadow-sm">
            <i class="fas fa-file-csv mr-1.5"></i> Export CSV (Excel)
        </a>

        <span class="w-full sm:w-auto sm:ml-auto text-sm text-gray-600 mt-2 sm:mt-0">
            Total: <strong>{{ $dispensasi->count() }}</strong> data
        </span>
    </div>

    <div class="overflow-x-auto min-w-0 w-full">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-left">Tujuan</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($dispensasi as $d)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-mono text-sm">{{ $d->nomor_surat }}</td>
                    <td class="p-3 text-sm">{{ $d->created_at->format('d-m-Y') }}</td>
                    <td class="p-3 font-semibold">{{ $d->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $d->siswa->kelas?->nama_kelas }}</td>
                    <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $d->kategori) }}</td>
                    <td class="p-3 text-sm">{{ Str::limit($d->tujuan, 30) }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold bg-gray-100">{{ ucfirst($d->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-5 text-center text-gray-500">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
