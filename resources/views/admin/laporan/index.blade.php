@extends('admin.layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan Dispensasi')

@section('content')
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold mb-4">Filter Laporan</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['menunggu','disetujui','ditolak','keluar','selesai'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="jurusan_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Jurusan</option>
                @foreach(\App\Models\Jurusan::all() as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="border rounded px-3 py-2 text-sm">
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="border rounded px-3 py-2 text-sm">
            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    <div class="p-5 border-b bg-gray-50 flex flex-wrap gap-2">
        <a href="{{ route('admin.laporan.pdf', request()->all()) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
            <i class="fas fa-file-pdf mr-1"></i> Export PDF
        </a>
        <a href="{{ route('admin.laporan.excel', request()->all()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
    <i class="fas fa-file-csv mr-1"></i> Export CSV (Excel)
</a>
        <span class="ml-auto text-sm text-gray-600 self-center">
            Total: <strong>{{ $dispensasi->count() }}</strong> data
        </span>
    </div>

    <div class="overflow-x-auto">
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