@extends('satpam.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')

{{-- Header dengan Nomor Surat & Status --}}
<div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-5 mb-4 text-white">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-blue-100 text-xs font-bold uppercase mb-1">Surat Dispensasi</p>
            <h1 class="text-2xl font-black font-mono">{{ $dispensasi->nomor_surat }}</h1>
            <p class="text-blue-100 text-xs mt-1">
                <i class="far fa-calendar-alt mr-1"></i>
                Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
            </p>
        </div>
        <div>
            @php
                $statusBadges = [
                    'menunggu' => ['bg-amber-100', 'text-amber-700', 'Menunggu'],
                    'disetujui' => ['bg-emerald-100', 'text-emerald-700', 'Disetujui'],
                    'ditolak' => ['bg-red-100', 'text-red-700', 'Ditolak'],
                    'keluar' => ['bg-sky-100', 'text-sky-700', 'Sedang Keluar'],
                    'selesai' => ['bg-gray-100', 'text-gray-700', 'Selesai'],
                ];
                $badge = $statusBadges[$dispensasi->status] ?? $statusBadges['menunggu'];
                
                // Cek apakah terlambat
                $isOverdue = $dispensasi->status === 'keluar' && 
                             $dispensasi->batas_waktu_kembali && 
                             now()->greaterThan($dispensasi->batas_waktu_kembali);
            @endphp
            <div class="flex flex-col gap-2">
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $badge[0] }} {{ $badge[1] }}">
                    {{ $badge[2] }}
                </span>
                @if($isOverdue)
                    <span class="px-4 py-2 rounded-full text-sm font-bold bg-red-500 text-white animate-pulse">
                        <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    
    {{-- KOLOM KIRI: Detail Dispensasi --}}
    <div class="lg:col-span-2 space-y-4">
        
        {{-- Informasi Utama --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>Informasi Dispensasi
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Kategori</p>
                    <p class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Lokasi</p>
                    <p class="font-bold text-gray-800">{{ $dispensasi->lokasi ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-4 bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Alasan</p>
                <p class="font-semibold text-gray-800">{{ $dispensasi->alasan }}</p>
            </div>

            <div class="mt-4 bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Tujuan</p>
                <p class="font-semibold text-gray-800">{{ $dispensasi->tujuan }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                    <p class="text-blue-400 text-[10px] font-bold uppercase mb-1">Jam Keluar</p>
                    <p class="font-bold text-blue-700">{{ $dispensasi->jam_keluar }}</p>
                    @if($dispensasi->waktu_keluar_aktual)
                        <p class="text-blue-600 text-xs mt-1">
                            <i class="fas fa-check-circle mr-1"></i>
                            Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
                <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                    <p class="text-amber-400 text-[10px] font-bold uppercase mb-1">Jam Kembali</p>
                    <p class="font-bold text-amber-700">{{ $dispensasi->jam_kembali }}</p>
                    @if($dispensasi->waktu_kembali_aktual)
                        <p class="text-emerald-600 text-xs mt-1">
                            <i class="fas fa-check-circle mr-1"></i>
                            Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
            </div>

            @if($dispensasi->batas_waktu_kembali)
                <div class="mt-4 bg-{{ $isOverdue ? 'red' : 'amber' }}-50 rounded-xl p-3 border border-{{ $isOverdue ? 'red' : 'amber' }}-100">
                    <p class="text-{{ $isOverdue ? 'red' : 'amber' }}-400 text-[10px] font-bold uppercase mb-1">
                        <i class="fas fa-clock mr-1"></i>Batas Waktu Kembali
                    </p>
                    <p class="font-bold text-{{ $isOverdue ? 'red' : 'amber' }}-700 text-lg">
                        {{ $dispensasi->batas_waktu_kembali->format('H:i') }} WIB
                    </p>
                    @if($isOverdue)
                        <p class="text-red-600 text-xs mt-1 font-bold">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Terlambat {{ now()->diffInMinutes($dispensasi->batas_waktu_kembali) }} menit
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Kontak Darurat & WhatsApp --}}
        @if(!empty($dispensasi->siswa->no_telepon))
            @php
                $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);
                
                $namaKelas = $dispensasi->siswa->kelas?->nama_kelas ?? 'Siswa';
                $pesan = "Halo *{$dispensasi->siswa->nama_lengkap}* ({$namaKelas}),\n\n";
                
                if ($dispensasi->status === 'menunggu') {
                    $pesan .= "Anda tercatat menunggu konfirmasi keluar dispensasi.\n";
                } elseif ($dispensasi->status === 'keluar') {
                    $pesan .= "Anda tercatat sedang dispensasi keluar sekolah.\n";
                }
                
                $pesan .= " No. Surat: {$dispensasi->nomor_surat}\n";
                $pesan .= "📍 Tujuan: {$dispensasi->tujuan}\n";
                $pesan .= " Batas Kembali: {$dispensasi->jam_kembali}\n\n";
                $pesan .= "Mohon segera kembali ke sekolah atau lapor ke Pos Satpam. Terima kasih.";
                
                $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-phone-alt text-green-600 mr-2"></i>Kontak Darurat
                </h3>
                
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-green-700 text-xs font-bold uppercase mb-1">No. Telepon / WhatsApp</p>
                            <p class="text-lg font-bold text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                            <p class="text-green-600 text-xs mt-1">{{ $dispensasi->siswa->nama_lengkap }}</p>
                        </div>
                        
                    </div>
                    <p class="text-green-700 text-xs">
                        <i class="fas fa-info-circle mr-1"></i>
                        Klik tombol Tandai Hubungi untuk menghubungi siswa/orang tua secara langsung
                    </p>
                </div>

                @if($dispensasi->status === 'keluar' && !$dispensasi->is_warned)
                    <form method="POST" action="{{ route('satpam.mark-contacted', $dispensasi) }}" class="mt-4">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 shadow-md shadow-purple-500/20 transition-all">
                            <i class="fas fa-phone-alt mr-2"></i>Tandai Sudah Dihubungi
                        </button>
                    </form>
                @elseif($dispensasi->is_warned)
                    <div class="mt-4 p-4 bg-purple-50 border-2 border-purple-200 rounded-xl">
                        <p class="text-purple-700 text-sm font-bold">
                            <i class="fas fa-check-circle mr-2"></i>Sudah Dihubungi
                        </p>
                        <p class="text-purple-600 text-xs mt-1">
                            {{ $dispensasi->warned_at ? $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') : '-' }} WIB
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Aksi untuk Satpam --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-tasks text-red-600 mr-2"></i>Aksi Satpam
            </h3>
            
            <div class="space-y-3">
                @if($dispensasi->status === 'disetujui')
                    <a href="{{ route('satpam.scan', ['dispensasi' => $dispensasi->id]) }}" 
                       class="w-full inline-flex justify-center items-center px-5 py-3.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-600 to-rose-500 shadow-lg shadow-red-500/30 hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-qrcode mr-2 text-lg"></i>Scan QR Code untuk Keluar
                    </a>
                @elseif($dispensasi->status === 'keluar')
                    <form method="POST" action="{{ route('satpam.konfirmasi.kembali', $dispensasi) }}">
                        @csrf
                        <button type="submit" 
                                data-confirm="Konfirmasi {{ $dispensasi->siswa->nama_lengkap }} sudah KEMBALI ke sekolah?"
                                class="w-full inline-flex justify-center items-center px-5 py-3.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 transition-all">
                            <i class="fas fa-door-closed mr-2 text-lg"></i>Konfirmasi Siswa Kembali
                        </button>
                    </form>
                @elseif($dispensasi->status === 'selesai')
                    <div class="p-4 bg-emerald-50 border-2 border-emerald-200 rounded-xl text-center">
                        <i class="fas fa-check-circle text-4xl text-emerald-500 mb-2"></i>
                        <p class="text-emerald-700 font-bold">Dispensasi Selesai</p>
                        <p class="text-emerald-600 text-xs mt-1">Siswa sudah kembali ke sekolah</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN: Data Siswa & Guru --}}
    <div class="space-y-4">
        
        {{-- Data Siswa --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h3>
            
            <div class="space-y-3">
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Nama Lengkap</p>
                    <p class="font-bold text-gray-800">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">NIS / NISN</p>
                    <p class="font-mono font-bold text-gray-800">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Kelas</p>
                    <p class="font-bold text-gray-800">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Jurusan</p>
                    <p class="text-gray-700">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                @if(!empty($dispensasi->siswa->no_telepon))
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">No. Telepon</p>
                    <p class="font-mono font-bold text-gray-800">{{ $dispensasi->siswa->no_telepon }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Guru Piket --}}
        @if($dispensasi->guru)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-tie text-amber-600 mr-2"></i>Guru Piket Penanggung Jawab
            </h3>
            
            <div class="space-y-3">
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Nama Guru</p>
                    <p class="font-bold text-gray-800">{{ $dispensasi->guru->nama_lengkap }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-bold uppercase mb-1">Tanggal Persetujuan</p>
                    <p class="text-gray-700">{{ $dispensasi->updated_at->isoFormat('D MMMM Y') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline Status --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-history text-purple-600 mr-2"></i>Riwayat Status
            </h3>
            
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-800">Pengajuan Dibuat</p>
                        <p class="text-[10px] text-gray-500">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
                
                @if($dispensasi->guru)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2"></div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-800">Disetujui Guru Piket</p>
                        <p class="text-[10px] text-gray-500">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
                @endif
                
                @if($dispensasi->waktu_keluar_aktual)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-sky-500 mt-2"></div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-800">Konfirmasi Keluar (Scan QR)</p>
                        <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
                @endif
                
                @if($dispensasi->waktu_kembali_aktual)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2"></div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-800">Konfirmasi Kembali</p>
                        <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <a href="{{ route('satpam.dashboard') }}" 
           class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

@push('scripts')
<script>
// Konfirmasi untuk tombol konfirmasi kembali
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = this.closest('form');
        
        Swal.fire({
            title: 'Konfirmasi',
            text: this.dataset.confirm,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
@endsection