@extends('siswa.layouts.app')

@section('title', 'Buat Pengajuan')
@section('page-title', 'Buat Pengajuan Dispensasi')

@section('content')
@include('components.alert')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Form Pengajuan Dispensasi</h3>
        
        {{-- ✅ enctype="multipart/form-data" DIHAPUS karena tidak ada lagi upload file --}}
        <form method="POST" action="{{ route('siswa.pengajuan.store') }}">
            @csrf
            
            <div class="space-y-4">
                {{-- Guru Piket --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Guru Piket <span class="text-red-500">*</span></label>
                    <select name="guru_piket_id" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Guru Piket --</option>
                        @foreach($guruPiket as $gp)
                        <option value="{{ $gp->id }}">
                            {{ $gp->guru->nama_lengkap }} (Shift: {{ ucfirst($gp->shift) }})
                        </option>
                        @endforeach
                    </select>
                    @error('guru_piket_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
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
                    @error('kategori')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alasan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
                    <textarea name="alasan" required rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Jelaskan alasan pengajuan..."></textarea>
                    @error('alasan')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tujuan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan <span class="text-red-500">*</span></label>
                    <input type="text" name="tujuan" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Contoh: Rumah Sakit Umum">
                    @error('tujuan')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lokasi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Opsional)</label>
                    <input type="text" name="lokasi" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Contoh: Jl. Sudirman No. 10">
                </div>

                {{-- Jam Pelajaran Keluar & Kembali --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Pelajaran Keluar <span class="text-red-500">*</span></label>
                        <select name="jam_keluar" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Pilih Jam Pelajaran --</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Jam Pelajaran ke-{{ $i }}</option>
                            @endfor
                        </select>
                        @error('jam_keluar')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Pelajaran Kembali <span class="text-red-500">*</span></label>
                        <select name="jam_kembali" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Pilih Jam Pelajaran --</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Jam Pelajaran ke-{{ $i }}</option>
                            @endfor
                        </select>
                        @error('jam_kembali')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ✅ BAGIAN UPLOAD FILE SUDAH DIHAPUS SEPENUHNYA --}}
            </div>

            <div class="mt-6 flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                </button>
                <a href="{{ route('siswa.pengajuan.index') }}" class="px-6 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection