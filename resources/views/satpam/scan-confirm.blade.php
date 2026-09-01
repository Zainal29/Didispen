@extends('satpam.layouts.app')

@section('title', 'Verifikasi Dispensasi')
@section('page-title', 'Verifikasi Dispensasi')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">
                <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                Verifikasi Dispensasi
            </h2>
            <span class="px-3 py-1 rounded-full text-xs font-bold
                @if($dispensasi->status === 'disetujui') bg-emerald-100 text-emerald-700
                @elseif($dispensasi->status === 'keluar') bg-sky-100 text-sky-700
                @endif">
                {{ strtoupper($dispensasi->status) }}
            </span>
        </div>

        {{-- Data Dispensasi --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 space-y-3">
            <div>
                <p class="text-[10px] font-bold text-blue-500 uppercase">Nomor Surat</p>
                <p class="font-mono font-bold text-gray-900">{{ $dispensasi->nomor_surat }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-[10px] font-bold text-blue-500 uppercase">ID Dispensasi</p>
                    <p class="font-bold text-gray-900">#{{ $dispensasi->id }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-blue-500 uppercase">Tanggal Pengajuan</p>
                    <p class="font-bold text-gray-900">{{ $dispensasi->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Data Siswa --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4">
            <h3 class="text-sm font-bold text-gray-900 mb-3">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h3>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama Lengkap:</span>
                    <span class="font-bold text-gray-900">{{ $dispensasi->siswa->nama_lengkap }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">NIS/NISN:</span>
                    <span class="font-mono font-bold text-gray-900">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kelas:</span>
                    <span class="font-bold text-gray-900">{{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jurusan:</span>
                    <span class="text-gray-900">{{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Waktu Keluar & Kembali --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">Jam Keluar</p>
                <p class="font-bold text-emerald-900">{{ $dispensasi->jam_keluar }}</p>
                @if($dispensasi->waktu_keluar_aktual)
                <p class="text-xs text-emerald-700 mt-1">
                    <i class="fas fa-check-circle mr-1"></i>
                    Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }}
                </p>
                @endif
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                <p class="text-[10px] font-bold text-amber-600 uppercase mb-1">Jam Kembali</p>
                <p class="font-bold text-amber-900">{{ $dispensasi->jam_kembali }}</p>
                @if($dispensasi->waktu_kembali_aktual)
                <p class="text-xs text-amber-700 mt-1">
                    <i class="fas fa-check-circle mr-1"></i>
                    Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }}
                </p>
                @endif
            </div>
        </div>

        {{-- Tujuan & Alasan --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 mb-4">
            <div class="mb-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase">Tujuan</p>
                <p class="text-sm font-bold text-gray-900">{{ $dispensasi->tujuan }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-500 uppercase">Alasan</p>
                <p class="text-sm text-gray-700">{{ $dispensasi->alasan }}</p>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        @if($dispensasi->status === 'disetujui')
        <form action="{{ route('satpam.scan.verify') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="qr_data" value='@json(['token' => $dispensasi->qr_token])'>
            <input type="hidden" name="action" value="keluar">

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/30">
                <i class="fas fa-door-open mr-2"></i>Konfirmasi Siswa KELUAR
            </button>
        </form>

        @elseif($dispensasi->status === 'keluar')
        <form action="{{ route('satpam.scan.verify') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="qr_data" value='@json(['token' => $dispensasi->qr_token])'>
            <input type="hidden" name="action" value="kembali">

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                <i class="fas fa-door-closed mr-2"></i>Konfirmasi Siswa KEMBALI
            </button>
        </form>
        @endif

        {{-- Tombol Kembali --}}
        <a href="{{ route('satpam.scan') }}" class="block text-center mt-4 text-gray-600 hover:text-gray-800 font-bold">
            <i class="fas fa-redo mr-1"></i>Scan QR Code Lainnya
        </a>
    </div>
</div>
@endsection
