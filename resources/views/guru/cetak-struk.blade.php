@extends('guru.layouts.app')
@section('title', 'Cetak Dispensasi')
@section('page-title', 'Cetak Struk')
@section('content')

@include('components.alert')

<div class="max-w-2xl mx-auto space-y-4">
    {{-- Header Card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-print text-lg"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Cetak Dispensasi</h3>
                <p class="text-xs text-gray-500 font-mono">{{ $dispensasi->nomor_surat }}</p>
            </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-gray-500 font-medium">Surat Dispensasi</p>
                    <p class="text-sm font-bold text-gray-900">SMK NEGERI 1 BANGSRI</p>
                </div>
            </div>

            <div class="space-y-2 text-xs border-t border-gray-200 pt-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-semibold text-gray-900">{{ $dispensasi->siswa->nama_lengkap }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">NIS</span>
                    <span class="font-mono font-semibold text-gray-900">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kelas</span>
                    <span class="font-semibold text-gray-900">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kategori</span>
                    <span class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('guru.cetak-pdf', [$dispensasi, 'format' => 'thermal']) }}"
               target="_blank"
               class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Buka PDF Struk (58mm)
            </a>
            <a href="{{ route('guru.pengajuan.show', $dispensasi) }}"
               class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>Tutup
            </a>
        </div>
    </div>
</div>

@endsection
