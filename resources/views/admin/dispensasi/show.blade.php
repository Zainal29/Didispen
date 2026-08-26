@extends('admin.layouts.app')

@section('title', 'Detail Pengajuan')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ $dispensasi->nomor_surat }}</h3>
                <p class="text-sm text-gray-500">Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm mb-6">
            {{-- Data Siswa --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Data Siswa</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">Nama:</span>
                        <p class="font-semibold">{{ $dispensasi->siswa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">NIS:</span>
                        <p class="font-mono">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Kelas:</span>
                        <p class="font-semibold">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jurusan:</span>
                        <p>{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Detail Pengajuan --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Detail Pengajuan</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500">Kategori:</span>
                        <p class="font-semibold capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Tujuan:</span>
                        <p class="font-semibold">{{ $dispensasi->tujuan }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Lokasi:</span>
                        <p class="font-semibold">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jam Keluar:</span>
                        {{-- jam_keluar disimpan sebagai string jam (bukan timestamp), jadi tetap echo langsung --}}
                        <p class="font-semibold text-indigo-700">
                            {{ $dispensasi->jam_keluar }}
                            <span class="text-xs text-gray-500 block mt-1 font-normal">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Jam Kembali:</span>
                        <p class="font-semibold text-indigo-700">
                            {{ $dispensasi->jam_kembali }}
                            <span class="text-xs text-gray-500 block mt-1 font-normal">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alasan & Catatan --}}
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Alasan & Catatan</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-500">Alasan Siswa:</span>
                        <p class="font-medium mt-1 text-gray-800">{{ $dispensasi->alasan }}</p>
                    </div>
                    @if($dispensasi->catatan_admin)
                    <div class="bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                        <span class="text-gray-700 font-semibold text-xs uppercase">Catatan Admin / Guru:</span>
                        <p class="text-gray-800 mt-1">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Data Guru Piket --}}
            <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Guru Piket Penanggung Jawab</h4>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                        {{ substr($dispensasi->guru?->nama_lengkap ?? 'G', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->guru?->nama_lengkap ?? 'Menunggu persetujuan' }}</p>
                        <p class="text-sm text-gray-500">
                            Tanggal Piket: {{ $dispensasi->created_at ? $dispensasi->created_at->format('d M Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t pt-4 flex space-x-3">
            <a href="{{ route('admin.semua.pengajuan') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection