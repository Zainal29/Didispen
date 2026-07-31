@extends('siswa.layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ $dispensasi->nomor_surat }}</h3>
                <p class="text-sm text-gray-500">Diajukan: {{ $dispensasi->created_at->format('d M Y, H:i') }}</p>
            </div>
            @php
                $statusColors = [
                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                    'disetujui' => 'bg-green-100 text-green-800',
                    'ditolak' => 'bg-red-100 text-red-800',
                    'keluar' => 'bg-blue-100 text-blue-800',
                    'selesai' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100' }}">
                {{ ucfirst($dispensasi->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <span class="text-gray-500">Kategori:</span>
                <p class="font-semibold capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Lokasi:</span>
                <p class="font-semibold">{{ $dispensasi->lokasi ?? '-' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">Alasan:</span>
                <p class="font-semibold">{{ $dispensasi->alasan }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">Tujuan:</span>
                <p class="font-semibold">{{ $dispensasi->tujuan }}</p>
            </div>
           <div>
    <span class="text-gray-500">Jam Keluar:</span>
    <p class="font-semibold">{{ $dispensasi->jam_keluar }}</p>
</div>
<div>
    <span class="text-gray-500">Jam Kembali:</span>
    <p class="font-semibold">{{ $dispensasi->jam_kembali }}</p>
</div>
            @if($dispensasi->catatan_admin)
            <div class="col-span-2 bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                <span class="text-gray-500 text-xs">Catatan Guru:</span>
                <p class="font-semibold">{{ $dispensasi->catatan_admin }}</p>
            </div>
            @endif
        </div>

        <div class="border-t pt-4 flex space-x-3">
            @if(in_array($dispensasi->status, ['disetujui', 'selesai']))
            <a href="{{ route('siswa.cetak', $dispensasi) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fas fa-print mr-2"></i>Cetak Surat
            </a>
            @endif
            <a href="{{ route('siswa.pengajuan.index') }}" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>
</div>
@endsection