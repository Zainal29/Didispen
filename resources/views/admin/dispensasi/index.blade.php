@extends('admin.layouts.app')
@section('title', 'Semua Pengajuan')
@section('page-title', 'Semua Pengajuan Dispensasi')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold mb-4">Filter Pengajuan</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['menunggu','disetujui','ditolak','dikonfirmasi','keluar','kembali','selesai'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="jurusan_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Jurusan</option>
                @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="border rounded px-3 py-2 text-sm">
            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Siswa</th>
                    <th class="p-3 text-left">Kelas</th>
                    <th class="p-3 text-left">Kategori</th>
                    <th class="p-3 text-left">Tujuan</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Guru Piket</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($dispensasi as $d)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-mono text-sm">{{ $d->nomor_surat }}</td>
                    <td class="p-3 font-semibold">{{ $d->siswa->nama_lengkap }}</td>
                    <td class="p-3 text-sm">{{ $d->siswa->kelas?->nama_kelas ?? '-' }}</td>
                    <td class="p-3 text-sm capitalize">{{ str_replace('_', ' ', $d->kategori) }}</td>
                    <td class="p-3 text-sm">{{ Str::limit($d->tujuan, 30) }}</td>
                    <td class="p-3">
                        @php
                            $colors = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'dikonfirmasi' => 'bg-blue-100 text-blue-800',
                                'keluar' => 'bg-indigo-100 text-indigo-800',
                                'kembali' => 'bg-purple-100 text-purple-800',
                                'selesai' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$d->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst($d->status) }}
                        </span>
                    </td>
                    <td class="p-3 text-sm">{{ $d->guru?->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 text-center">
                        <a href="{{ route('admin.semua.pengajuan.show', $d->id) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="p-5 text-center text-gray-500">Tidak ada data pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dispensasi->hasPages())
    <div class="p-4 border-t">{{ $dispensasi->links() }}</div>
    @endif
</div>
@endsection