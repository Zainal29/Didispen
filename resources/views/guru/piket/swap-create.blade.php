@extends('guru.layouts.app')
@section('title', 'Tukar Jadwal Piket')
@section('page-title', 'Pengajuan Penggantian Guru Piket')

@section('content')
@include('components.alert')

<div class="max-w-2xl mx-auto space-y-4">
    {{-- Header Card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900">Form Pengajuan Tukar / Penggantian Jadwal</h3>
                <p class="text-xs text-gray-500">Ajukan permohonan penggantian tugas piket kepada guru lain.</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <form method="POST" action="{{ route('guru.piket.swap.store') }}" class="space-y-4">
            @csrf

            {{-- Pilihan Jadwal Resmi Guru --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Pilih Jadwal Resmi Piket Anda <span class="text-red-500">*</span>
                </label>
                @if($jadwalList->isEmpty())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Anda saat ini belum terdaftar pada jadwal resmi piket aktif mana pun.
                    </div>
                @else
                    <select name="jadwal_piket_id" required
                            class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                        <option value="">-- Pilih Sesi Jadwal Piket --</option>
                        @php
                            $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        @endphp
                        @foreach($jadwalList as $j)
                            <option value="{{ $j->id }}" {{ (old('jadwal_piket_id', $selectedJadwalId) == $j->id) ? 'selected' : '' }}>
                                {{ $namaHari[$j->hari] ?? 'Hari '.$j->hari }} ({{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('jadwal_piket_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Tugas Piket --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Tanggal Tugas Piket Yang Ingin Digantikan <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal" required value="{{ old('tanggal', $selectedTanggal) }}"
                       class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                <p class="text-[11px] text-gray-500 mt-1">Pastikan hari pada tanggal yang dipilih sesuai dengan hari jadwal piket di atas.</p>
                @error('tanggal')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilihan Guru Pengganti --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Guru Pengganti <span class="text-red-500">*</span>
                </label>
                <select name="guru_pengganti_id" required
                        class="w-full h-11 px-3.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">
                    <option value="">-- Pilih Guru Pengganti --</option>
                    @foreach($guruPenggantiList as $gp)
                        <option value="{{ $gp->id }}" {{ old('guru_pengganti_id') == $gp->id ? 'selected' : '' }}>
                            {{ $gp->nama_lengkap }} ({{ $gp->nip ?? 'NIP -' }})
                        </option>
                    @endforeach
                </select>
                @error('guru_pengganti_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Alasan Penggantian --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Alasan Penggantian <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <textarea name="alasan" rows="3" placeholder="Contoh: Mengikuti dinas luar / pelatihan guru..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 bg-white text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition-all">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-3 flex items-center justify-end space-x-3">
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    Kembali
                </a>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-xs transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
