@extends('siswa.layouts.app')

@section('title', 'Buat Pengajuan')
@section('page-title', 'Buat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Form Pengajuan Dispensasi</h3>
        
        @if(!isset($guruPiketHariIni))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                <p class="text-red-800 font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Maaf, tidak ada guru piket yang bertugas hari ini. Silakan hubungi Admin.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('siswa.pengajuan.store') }}">
                @csrf
                
                <div class="space-y-4">
                    {{-- ✅ DATA SISWA (READ-ONLY) --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Data Siswa</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block">Nama Lengkap</span>
                                <span class="font-semibold text-gray-800">{{ $siswa->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">NIS / NISN</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $siswa->user->nis_nip ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Kelas</span>
                                <span class="font-semibold text-gray-800">{{ $siswa->kelas->nama_kelas }} - {{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ GURU PIKET OTOMATIS (READ-ONLY) --}}
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-bold text-blue-800 mb-2">
                            <i class="fas fa-user-tie mr-1"></i> Guru Piket Hari Ini
                        </h4>
                        <p class="text-lg font-bold text-blue-900">{{ $guruPiketHariIni->guru->nama_lengkap }}</p>
                        <p class="text-xs text-blue-700 mt-1">Pengajuan akan otomatis diteruskan ke guru ini.</p>
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="keperluan_sekolah">Keperluan Sekolah</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
                        <textarea name="alasan" required rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Jelaskan alasan pengajuan..."></textarea>
                    </div>

                    {{-- Tujuan & Lokasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan <span class="text-red-500">*</span></label>
                            <input type="text" name="tujuan" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Contoh: Rumah Sakit">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Opsional)</label>
                            <input type="text" name="lokasi" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Contoh: Jl. Merdeka No. 1">
                        </div>
                    </div>

                    {{-- Jam Pelajaran --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Keluar <span class="text-red-500">*</span></label>
                            <select name="jam_keluar" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">Jam Pelajaran ke-{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kembali <span class="text-red-500">*</span></label>
                            <select name="jam_kembali" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">Jam Pelajaran ke-{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                    </button>
                    <a href="{{ route('siswa.pengajuan.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                        Batal
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection