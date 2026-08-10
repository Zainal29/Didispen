@extends('siswa.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold">{{ $dispensasi->nomor_surat }}</h2>
                    <p class="text-indigo-100 text-sm mt-1">
                        <i class="far fa-calendar-plus mr-1"></i>
                        Diajukan: {{ $dispensasi->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </p>
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
                <span class="px-4 py-1.5 rounded-full text-sm font-bold {{ $statusColors[$dispensasi->status] ?? 'bg-gray-100' }}">
                    {{ ucfirst($dispensasi->status) }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kolom Kiri: Detail Dispensasi --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Dispensasi</h3>
                    
                    <div>
                        <span class="text-gray-500 text-sm">Kategori</span>
                        <p class="font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                    </div>
                    
                    <div>
                        <span class="text-gray-500 text-sm">Alasan</span>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->alasan }}</p>
                    </div>

                    <div>
                        <span class="text-gray-500 text-sm">Tujuan</span>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->tujuan }}</p>
                    </div>

                    <div>
                        <span class="text-gray-500 text-sm">Lokasi</span>
                        <p class="font-semibold text-gray-800">{{ $dispensasi->lokasi ?? '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="bg-indigo-50 p-3 rounded-lg">
                            <span class="text-indigo-600 text-xs font-bold block mb-1">JAM KELUAR</span>
                            <p class="font-bold text-indigo-800">{{ $dispensasi->jam_keluar }}</p>
                            <p class="text-xs text-indigo-600 mt-1">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar) }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 p-3 rounded-lg">
                            <span class="text-indigo-600 text-xs font-bold block mb-1">JAM KEMBALI</span>
                            <p class="font-bold text-indigo-800">{{ $dispensasi->jam_kembali }}</p>
                            <p class="text-xs text-indigo-600 mt-1">
                                <i class="far fa-clock mr-1"></i>
                                {{ \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali) }}
                            </p>
                        </div>
                    </div>

                    @if($dispensasi->catatan_admin)
                    <div class="bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-400 mt-4">
                        <span class="text-yellow-800 text-xs font-bold block mb-1">CATATAN GURU PIKET:</span>
                        <p class="text-yellow-900 text-sm">{{ $dispensasi->catatan_admin }}</p>
                    </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Data Siswa, Guru, & QR Code --}}
                <div class="space-y-6">
                    
                    {{-- Data Siswa --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">Data Siswa</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-semibold text-gray-800 text-right">{{ $dispensasi->siswa->nama_lengkap }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">NIS:</span>
                                <span class="font-mono text-gray-800">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kelas:</span>
                                <span class="font-semibold text-gray-800">{{ $dispensasi->siswa->kelas->nama_kelas }} ({{ $dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-' }})</span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Guru Piket --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3">Guru Piket</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-semibold text-gray-800 text-right">{{ $dispensasi->guruPiket->guru->nama_lengkap ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal:</span>
                                <span class="text-gray-800">{{ $dispensasi->guruPiket->tanggal ? \Carbon\Carbon::parse($dispensasi->guruPiket->tanggal)->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ BAGIAN QR CODE (Muncul jika sudah disetujui) --}}
                    @if($dispensasi->qr_code && in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                    <div class="bg-indigo-50 border-2 border-dashed border-indigo-300 rounded-xl p-5 text-center">
                        <h4 class="text-base font-bold text-indigo-800 mb-2">
                            <i class="fas fa-qrcode mr-2"></i>QR Code Aktif
                        </h4>
                        <p class="text-xs text-indigo-600 mb-4">Tunjukkan ke Satpam saat keluar & kembali</p>
                        
                        <div class="bg-white p-3 rounded-lg shadow-sm inline-block mx-auto border border-gray-200">
                            <img src="{{ asset('storage/' . $dispensasi->qr_code) }}" alt="QR Code" class="w-48 h-48 mx-auto object-contain">
                        </div>
                        
                        <div class="mt-4 flex justify-center gap-2">
                            <a href="{{ asset('storage/' . $dispensasi->qr_code) }}" download class="flex-1 px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition shadow-sm">
                                <i class="fas fa-download mr-1"></i> Simpan
                            </a>
                            <button onclick="window.print()" class="flex-1 px-3 py-2 bg-gray-700 text-white text-xs font-medium rounded hover:bg-gray-800 transition shadow-sm">
                                <i class="fas fa-print mr-1"></i> Cetak
                            </button>
                        </div>
                    </div>
                    @elseif($dispensasi->status === 'menunggu')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
                        <i class="fas fa-hourglass-half text-yellow-500 text-3xl mb-2"></i>
                        <p class="text-sm text-yellow-800 font-medium">Menunggu persetujuan guru piket.<br>QR Code akan muncul setelah disetujui.</p>
                    </div>
                    @elseif($dispensasi->status === 'ditolak')
                    <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
                        <i class="fas fa-times-circle text-red-500 text-3xl mb-2"></i>
                        <p class="text-sm text-red-800 font-medium">Pengajuan ditolak.<br>Tidak ada QR Code yang diterbitkan.</p>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Tombol Aksi Bawah --}}
            <div class="mt-8 pt-6 border-t flex justify-between items-center">
                <a href="{{ route('siswa.pengajuan.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat
                </a>
                
                @if(in_array($dispensasi->status, ['disetujui', 'selesai']))
                <a href="{{ route('siswa.cetak', $dispensasi) }}" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium shadow-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Cetak Surat Dispensasi
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection