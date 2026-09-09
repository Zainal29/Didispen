@extends('satpam.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')

{{-- Header dengan Nomor Surat & Status --}}
<div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 mb-5 text-white shadow-lg shadow-blue-500/20">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-1">Surat Dispensasi</p>
            <h1 class="text-2xl font-black font-mono tracking-tight">{{ $dispensasi->nomor_surat }}</h1>
            <p class="text-blue-100 text-xs mt-2 font-medium">
                <i class="far fa-calendar-alt mr-1.5"></i>
                Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
            </p>
        </div>
        <div class="text-right">
            @php
                $statusBadges = [
                    'menunggu' => ['bg-amber-100', 'text-amber-800', 'Menunggu'],
                    'disetujui' => ['bg-emerald-100', 'text-emerald-800', 'Disetujui'],
                    'ditolak' => ['bg-red-100', 'text-red-800', 'Ditolak'],
                    'keluar' => ['bg-sky-100', 'text-sky-800', 'Sedang Keluar'],
                    'selesai' => ['bg-gray-100', 'text-gray-800', 'Selesai'],
                ];
                $badge = $statusBadges[$dispensasi->status] ?? $statusBadges['menunggu'];
                $isOverdue = $dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            @endphp
            <span class="inline-block px-4 py-2 rounded-xl text-xs font-black {{ $badge[0] }} {{ $badge[1] }} mb-2">
                {{ $badge[2] }}
            </span>
            @if($isOverdue)
                <span class="inline-block px-4 py-2 rounded-xl text-xs font-black bg-red-500 text-white animate-pulse shadow-lg shadow-red-500/30">
                    <i class="fas fa-exclamation-triangle mr-1"></i>TERLAMBAT
                </span>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- KOLOM KIRI: Detail Dispensasi --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Informasi Utama --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>Informasi Dispensasi
            </h3>

            @if($dispensasi->foto_verifikasi)
            <div class="bg-blue-50 border-2 border-blue-100 rounded-xl p-4 mb-5 flex flex-col sm:flex-row items-center gap-4">
                <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}"
                     alt="Foto {{ $dispensasi->siswa->nama_lengkap }}"
                     class="w-24 h-24 object-cover rounded-xl border-4 border-white shadow-md flex-shrink-0">
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-black text-blue-900 mb-1">
                        <i class="fas fa-camera mr-1"></i> Foto Verifikasi
                    </h4>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                    </p>
                </div>
            </div>
            @endif

            {{-- <i class="fas fa-check-circle"></i> BARU: Foto Bukti Siswa (Ditambahkan setelah siswa kembali) --}}
            @if($dispensasi->foto_bukti)
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border-2 border-emerald-300 flex-shrink-0">
                        <img src="{{ Storage::url($dispensasi->foto_bukti) }}"
                             alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900 mb-1">
                            <i class="fas fa-image mr-1"></i> Foto Bukti Kedatangan
                        </h4>
                        <p class="text-xs text-emerald-700 mb-2">
                            Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                        </p>
                        @if($dispensasi->foto_bukti_uploaded_at)
                        <p class="text-[10px] text-emerald-600">
                            <i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Kategori</p>
                    <p class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Lokasi / Tujuan</p>
                    <p class="font-bold text-gray-800">{{ $dispensasi->lokasi ?? $dispensasi->tujuan ?? '-' }}</p9>
                </div>
            </div>

            <div class="mt-4 bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Alasan</p>
                <p class="font-medium text-gray-800 leading-relaxed">{{ $dispensasi->alasan }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="bg-blue-50 rounded-xl p-3.5 border border-blue-100">
                    <p class="text-blue-400 text-[10px] font-black uppercase mb-1 tracking-wider">Jam Keluar</p>
                    <p class="font-bold text-blue-800 text-lg">{{ $dispensasi->jam_keluar }}</p>
                    @if($dispensasi->waktu_keluar_aktual)
                        <p class="text-blue-600 text-xs mt-1.5 font-medium bg-blue-100 w-fit px-2 py-0.5 rounded-md">
                            <i class="fas fa-check-circle mr-1"></i>Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
                <div class="bg-amber-50 rounded-xl p-3.5 border border-amber-100">
                    <p class="text-amber-400 text-[10px] font-black uppercase mb-1 tracking-wider">Batas Kembali</p>
                    <p class="font-bold text-amber-800 text-lg">{{ $dispensasi->jam_kembali }}</p>
                    @if($dispensasi->waktu_kembali_aktual)
                        <p class="text-emerald-600 text-xs mt-1.5 font-medium bg-emerald-100 w-fit px-2 py-0.5 rounded-md">
                            <i class="fas fa-check-circle mr-1"></i>Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
            </div>

            @if($dispensasi->batas_waktu_kembali)
                <div class="mt-4 bg-{{ $isOverdue ? 'red' : 'amber' }}-50 rounded-xl p-4 border border-{{ $isOverdue ? 'red' : 'amber' }}-100">
                    <p class="text-{{ $isOverdue ? 'red' : 'amber' }}-500 text-[10px] font-black uppercase mb-2 tracking-wider">
                        <i class="fas fa-clock mr-1"></i>Batas Waktu Kembali
                    </p>
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-black text-{{ $isOverdue ? 'red' : 'amber' }}-800 text-xl">
                            {{ $dispensasi->batas_waktu_kembali->format('H:i') }} <span class="text-sm font-bold">WIB</span>
                        </p>
                        @if($dispensasi->status === 'keluar')
                            <span id="live-countdown"
                                  class="px-3 py-1.5 rounded-lg text-xs font-black {{ $isOverdue ? 'bg-red-100 text-red-700 animate-pulse border border-red-200' : 'bg-white text-amber-700 border border-amber-200 shadow-sm' }}"
                                  data-deadline="{{ $dispensasi->batas_waktu_kembali->toISOString() }}">...</span>
                        @endif
                    </div>
                    @if($isOverdue)
                        @php
                            $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
                        @endphp
                        <p class="text-red-600 text-xs mt-3 font-bold bg-red-100 w-fit px-3 py-1.5 rounded-lg border border-red-200">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Terlambat <span id="late-minutes" class="text-sm">{{ $lateMinutes }}</span> menit
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- KONTAK DARURAT & WHATSAPP --}}
        @if(!empty($dispensasi->siswa->no_telepon))
            @php
                $hp = preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon);
                if (str_starts_with($hp, '0')) $hp = '62' . substr($hp, 1);

                $namaKelas = $dispensasi->siswa->kelas?->nama_kelas ?? 'Siswa';
                $pesan = "Halo *{$dispensasi->siswa->nama_lengkap}* ({$namaKelas}),\n\n";

                $isOverdue = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);

                if ($isOverdue) {
                    $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali);
                    $lateText = \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat($lateMinutes, short: true);

                    $pesan .= "*PERINGATAN KETERLAMBATAN DISPENSASI*\n";
                    $pesan .= "Batas waktu kembali Anda telah LEWAT sejak *{$lateText}* yang lalu.\n\n";
                } else {
                    $pesan .= "Anda tercatat sedang dispensasi keluar sekolah.\n\n";
                }

                $pesan .= " No. Surat: {$dispensasi->nomor_surat}\n";
                $pesan .= " Tujuan: {$dispensasi->tujuan}\n";
                $pesan .= "⏰ Batas Kembali: {$dispensasi->jam_kembali}\n\n";
                $pesan .= "Mohon segera kembali ke sekolah atau lapor ke Pos Satpam. Terima kasih.";

                $waLink = "https://wa.me/{$hp}?text=" . urlencode($pesan);
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                    <i class="fas fa-phone-alt text-green-600 mr-2"></i>Kontak Darurat
                </h3>

                @if($dispensasi->is_warned)
                    {{-- <i class="fas fa-check-circle"></i> SUDAH DIHUBUNGI: Tampilkan badge saja, tanpa tombol WA --}}
                    <div class="p-4 bg-purple-50 border-2 border-purple-200 rounded-xl flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-purple-800 font-black text-sm mb-1">Sudah Dihubungi</p>
                            <p class="text-purple-600 text-xs font-medium">
                                <i class="far fa-clock mr-1"></i>
                                {{ $dispensasi->warned_at ? $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') : '-' }} WIB
                            </p>
                            <p class="text-purple-500 text-[10px] mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                No. Telepon: <span class="font-mono font-bold">{{ $dispensasi->siswa->no_telepon }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    {{-- <i class="fas fa-check-circle"></i> BELUM DIHUBUNGI: Tampilkan tombol WA --}}
                    <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-green-700 text-[10px] font-black uppercase mb-1 tracking-wider">No. Telepon / WhatsApp</p>
                                <p class="text-lg font-black text-gray-800 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                                <p class="text-green-600 text-xs mt-1 font-medium">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            </div>

                            <a href="{{ $waLink }}"
                               target="_blank"
                               rel="noopener"
                               onclick="handleDetailWaContacted(event, {{ $dispensasi->id }}, '{{ $waLink }}')"
                               class="inline-flex flex-col items-center justify-center w-16 h-16 bg-green-500 hover:bg-green-600 text-white rounded-2xl transition-all shadow-lg shadow-green-500/30 hover:scale-105 active:scale-95 flex-shrink-0"
                               title="Hubungi via WhatsApp">
                                <i class="fab fa-whatsapp text-3xl"></i>
                                <span class="text-[9px] font-black mt-0.5 tracking-wider">CHAT</span>
                            </a>
                        </div>
                        <p class="text-green-700 text-xs font-medium bg-green-100/50 p-2 rounded-lg">
                            <i class="fas fa-info-circle mr-1"></i>
                            Klik ikon WhatsApp untuk menghubungi. Status siswa akan otomatis ditandai "Sudah Dihubungi".
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- <i class="fas fa-check-circle"></i> AKSI UNTUK SATPAM (DIPERBAIKI: SCAN QR CODE) --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                <i class="fas fa-qrcode text-red-600 mr-2"></i>Aksi Satpam
            </h3>

            <div class="space-y-3">
                @if($dispensasi->status === 'disetujui')
                    <a href="{{ route('satpam.scan', ['dispensasi' => $dispensasi->id]) }}"
                       class="w-full inline-flex justify-center items-center px-5 py-4 rounded-xl text-sm font-black text-white bg-gradient-to-r from-red-600 to-rose-500 shadow-lg shadow-red-500/30 hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                        <i class="fas fa-camera mr-2 text-lg"></i>Scan QR Code untuk Keluar
                    </a>
                    <p class="text-xs text-gray-500 text-center font-medium">
                        <i class="fas fa-info-circle mr-1"></i>Arahkan kamera ke QR Code siswa
                    </p>

                @elseif($dispensasi->status === 'keluar')
                    <a href="{{ route('satpam.scan') }}"
                       class="w-full inline-flex justify-center items-center px-5 py-4 rounded-xl text-sm font-black text-white bg-gradient-to-r from-emerald-600 to-emerald-700 shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                        <i class="fas fa-camera mr-2 text-lg"></i>Scan QR Code untuk Kembali
                    </a>
                    <p class="text-xs text-gray-500 text-center font-medium">
                        <i class="fas fa-info-circle mr-1"></i>Scan QR Code yang sama saat siswa kembali
                    </p>

                @elseif($dispensasi->status === 'selesai')
                    <div class="p-5 bg-emerald-50 border-2 border-emerald-100 rounded-xl text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                        <p class="text-emerald-800 font-black text-sm">Dispensasi Selesai</p>
                        <p class="text-emerald-600 text-xs mt-1 font-medium">Siswa sudah kembali ke sekolah dengan selamat.</p>
                        @if($dispensasi->waktu_kembali_aktual)
                            <p class="text-emerald-700 text-[10px] mt-2 font-mono bg-emerald-100 w-fit mx-auto px-2 py-1 rounded">
                                <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB
                            </p>
                        @endif
                    </div>
                @endif
            </div
        </div>
    </div>

    {{-- KOLOM KANAN: Data Siswa & Guru --}}
    <div class="space-y-5">

        {{-- Data Siswa --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Nama Lengkap</p>
                    <p class="font-bold text-gray-900">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">NIS / NISN</p>
                        <p class="font-mono font-bold text-gray-800 text-sm">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Kelas</p>
                        <p class="font-bold text-gray-800 text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Jurusan</p>
                    <p class="text-gray-700 text-sm font-medium">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                @if(!empty($dispensasi->siswa->no_telepon))
                <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">No. Telepon</p>
                    <p class="font-mono font-bold text-gray-800 text-sm">{{ $dispensasi->siswa->no_telepon }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Guru Piket --}}
        @if($dispensasi->guru)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                <i class="fas fa-user-tie text-amber-600 mr-2"></i>Guru Piket
            </h3>
            <div class="space-y-3">
                <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Nama Guru</p>
                    <p class="font-bold text-gray-900">{{ $dispensasi->guru->nama_lengkap }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase mb-1 tracking-wider">Disetujui Pada</p>
                    <p class="text-gray-700 text-sm font-medium">{{ $dispensasi->updated_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline Status --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center uppercase tracking-wide">
                <i class="fas fa-history text-purple-600 mr-2"></i>Riwayat
            </h3>
            <div class="relative border-l-2 border-gray-200 ml-2.5 space-y-6 pb-2">
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Pengajuan Dibuat</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>

                @if($dispensasi->guru)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Disetujui Guru Piket</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $dispensasi->updated_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif

                @if($dispensasi->waktu_keluar_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-sky-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Konfirmasi Keluar (Scan)</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif

                @if($dispensasi->waktu_kembali_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                    <p class="text-xs font-bold text-gray-800">Konfirmasi Kembali</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <a href="{{ route('satpam.dashboard') }}"
           class="w-full inline-flex items-center justify-center px-4 py-3.5 rounded-xl text-sm font-black text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 transition-all active:scale-[0.98]">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>

@push('scripts')
<script>
function handleDetailWaContacted(event, dispensasiId, waLink) {
    @if($dispensasi->status === 'keluar' && !$dispensasi->is_warned)
        event.preventDefault();
        fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            keepalive: true
        })
        .catch(error => console.error('Error:', error))
        .finally(() => window.open(waLink, '_blank'));
    @endif
}

const detailDeadlineEl = document.getElementById('live-countdown');
@if($dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali)
    let overdueAlerted = {{ $isOverdue ? 'true' : 'false' }};

    function tickDetailCountdown() {
        if (!detailDeadlineEl) return;
        const deadline = new Date(detailDeadlineEl.dataset.deadline);
        const diffMs = deadline - new Date();
        const lateEl = document.getElementById('late-minutes');

        if (diffMs <= 0) {
            const lateMin = Math.floor(-diffMs / 60000);
            detailDeadlineEl.textContent = `TERLAMBAT ${lateMin}m`;
            detailDeadlineEl.classList.remove('bg-white', 'text-amber-700', 'border-amber-200');
            detailDeadlineEl.classList.add('bg-red-100', 'text-red-700', 'animate-pulse', 'border', 'border-red-200');
            if (lateEl) lateEl.textContent = lateMin;

            if (!overdueAlerted) {
                overdueAlerted = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Melewati Batas Waktu!',
                    text: 'Siswa telah melewati batas waktu kembali. Segera hubungi via WhatsApp.',
                    confirmButtonColor: '#dc2626'
                });
            }
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            detailDeadlineEl.textContent = hrs > 0
                ? `Sisa ${hrs}j ${mins}m ${secs}s`
                : `Sisa ${mins}m ${secs}s`;
            if (totalMin <= 5) {
                detailDeadlineEl.classList.remove('bg-white', 'text-amber-700', 'border-amber-200');
                detailDeadlineEl.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
            }
        }
    }
    tickDetailCountdown();
    setInterval(tickDetailCountdown, 1000);
@endif
</script>
@endpush
@endsection
