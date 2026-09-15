@extends('admin.layouts.app')
@section('title', 'Riwayat Guru Piket')
@section('page-title', 'Riwayat Aktivitas Guru Piket')

@section('content')
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold">Statistik Guru Piket</h3>
        <p class="text-sm text-gray-500 mt-1">Daftar guru dan jumlah dispensasi yang telah diproses.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama Guru</th>
                    <th class="p-3 text-left">NIP</th>
                    <th class="p-3 text-center">Total Dispensasi Diproses</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($gurus as $g)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ $g->nama_lengkap }}</td>
                    <td class="p-3 text-sm font-mono">{{ $g->nip }}</td>
                    <td class="p-3 text-center">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 font-bold text-xs rounded-full">
                            {{ $g->dispensasi_count }} Surat
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-5 text-center text-gray-500">Belum ada data guru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b">
        <h3 class="text-lg font-bold">100 Riwayat Persetujuan Terakhir</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">No. Surat</th>
                    <th class="p-3 text-left">Siswa</th>
                    <th class="p-3 text-left">Diproses Oleh</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($riwayat as $r)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3 font-mono font-bold">{{ $r->nomor_surat }}</td>
                    <td class="p-3">{{ $r->siswa->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 font-semibold text-blue-700">{{ $r->guru->nama_lengkap ?? '-' }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold bg-gray-100">{{ ucfirst($r->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-5 text-center text-gray-500">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection