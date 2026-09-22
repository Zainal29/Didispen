@extends('satpam.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')

{{-- Header dengan Nomor Surat & Status --}}
<div class="bg-blue-600 rounded-xl p-5 mb-5 text-white">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-blue-100 text-[10px] font-semibold uppercase tracking-wider mb-1">Surat Dispensasi</p>
            <h1 class="text-xl font-bold font-mono tracking-tight">{{ $dispensasi->nomor_surat }}</h1>
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
            <span class="inline-block px-3 py-1.5 rounded-md text-xs font-semibold {{ $badge[0] }} {{ $badge[1] }} mb-2">
                {{ $badge[2] }}
            </span>
            @if($isOverdue)
                <span class="inline-block px-3 py-1.5 rounded-md text-xs font-semibold bg-red-600 text-white">
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
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>Informasi Dispensasi
            </h3>

            <!--@if($dispensasi->foto_verifikasi)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5 flex flex-col sm:flex-row items-center gap-4">
                <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-20 h-20 object-cover rounded-lg border border-white shadow-sm flex-shrink-0">
                <div class="text-center sm:text-left">
                    <h4 class="text-sm font-bold text-blue-900 mb-1"><i class="fas fa-camera mr-1"></i> Foto Verifikasi</h4>
                    <p class="text-xs text-blue-700 leading-relaxed">Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.</p>
                </div>
            </div>
            @endif

            @if($dispensasi->foto_bukti)
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white">
                        <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900 mb-1"><i class="fas fa-image mr-1"></i> Foto Bukti Kedatangan</h4>
                        <p class="text-xs text-emerald-700 mb-2">Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.</p>
                        @if($dispensasi->foto_bukti_uploaded_at)
                        <p class="text-[10px] text-emerald-600"><i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif-->

            {{-- Foto Verifikasi (Jika Ada) --}}
            @if($dispensasi->foto_verifikasi)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-blue-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa->nama_lengkap }}')">
                        <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-blue-900 mb-1 flex flex-wrap items-center gap-2">
                            <i class="fas fa-camera mr-1.5"></i>
                            <span class="break-words">Foto Verifikasi Siswa</span>
                            <span class="text-[10px] font-normal text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                                <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                            </span>
                        </h4>
                        <p class="text-xs text-blue-700 mb-2 break-words">
                            Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                        </p>
                        <p class="text-[10px] text-blue-600 break-words">
                            <i class="fas fa-info-circle mr-1"></i> Foto akan dihapus otomatis setelah siswa kembali.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Foto Bukti Siswa --}}
            @if($dispensasi->foto_bukti)
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-lg transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa->nama_lengkap }}')">
                        <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-emerald-900 mb-1 flex flex-wrap items-center gap-2">
                            <i class="fas fa-image mr-1.5"></i>
                            <span class="break-words">Foto Bukti Kedatangan</span>
                            <span class="text-[10px] font-normal text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                                <i class="fas fa-expand mr-1"></i>Klik untuk memperbesar
                            </span>
                        </h4>
                        <p class="text-xs text-emerald-700 mb-2 break-words">
                            Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                        </p>
                        @if($dispensasi->foto_bukti_uploaded_at)
                        <p class="text-[10px] text-emerald-600 break-words">
                            <i class="far fa-clock mr-1"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- MODAL PREVIEW FOTO --}}
            <div id="photoModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm" onclick="closePhotoModal(event)">
                <div class="relative flex h-[calc(100dvh-1.5rem)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:h-[calc(100dvh-3rem)]" onclick="event.stopPropagation()">
                    {{-- Header Modal --}}
                    <div class="w-full bg-white rounded-t-xl px-4 py-3 flex items-center justify-between border-b border-gray-200">
                        <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-image text-blue-600"></i>
                            <span class="truncate">Preview Foto</span>
                        </h3>
                        <button onclick="closePhotoModal()" class="ml-3 w-9 h-9 flex-shrink-0 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors" aria-label="Tutup preview foto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="min-h-0 flex-1 w-full bg-gray-950 p-3 sm:p-5 flex items-center justify-center overflow-hidden">
                        <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-lg">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Kategori</p>
                    <p class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Lokasi / Tujuan</p>
                    <p class="font-semibold text-gray-900">{{ $dispensasi->lokasi ?? $dispensasi->tujuan ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-4 bg-gray-50 rounded-lg p-3.5 border border-gray-200">
                <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Alasan</p>
                <p class="font-medium text-gray-800 leading-relaxed">{{ $dispensasi->alasan }}</p>
            </div>

            @if($dispensasi->dibuat_manual_oleh_guru)
                <div class="mt-4 bg-violet-50 border border-violet-200 rounded-lg p-3.5">
                    <p class="text-violet-700 text-[10px] font-semibold uppercase tracking-wider mb-1"><i class="fas fa-user-tie mr-1"></i>Dibuat Manual oleh Guru Piket</p>
                    <p class="text-violet-900 text-sm font-medium">Dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="bg-blue-50 rounded-lg p-3.5 border border-blue-200">
                    <p class="text-blue-600 text-[10px] font-semibold uppercase mb-1 tracking-wider">Jam Keluar</p>
                    <p class="font-bold text-blue-900 text-lg">{{ $dispensasi->jam_keluar }}</p>
                    @if($dispensasi->waktu_keluar_aktual)
                        <p class="text-blue-700 text-xs mt-1.5 font-medium bg-blue-100 w-fit px-2 py-0.5 rounded-md">
                            <i class="fas fa-check-circle mr-1"></i>Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
                <div class="bg-amber-50 rounded-lg p-3.5 border border-amber-200">
                    <p class="text-amber-600 text-[10px] font-semibold uppercase mb-1 tracking-wider">Batas Kembali</p>
                    <p class="font-bold text-amber-900 text-lg">{{ $dispensasi->jam_kembali }}</p>
                    @if($dispensasi->waktu_kembali_aktual)
                        <p class="text-emerald-700 text-xs mt-1.5 font-medium bg-emerald-100 w-fit px-2 py-0.5 rounded-md">
                            <i class="fas fa-check-circle mr-1"></i>Aktual: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
            </div>

            @if($dispensasi->batas_waktu_kembali)
                <div class="mt-4 bg-{{ $isOverdue ? 'red' : 'amber' }}-50 rounded-lg p-4 border border-{{ $isOverdue ? 'red' : 'amber' }}-200">
                    <p class="text-{{ $isOverdue ? 'red' : 'amber' }}-600 text-[10px] font-semibold uppercase mb-2 tracking-wider">
                        <i class="fas fa-clock mr-1"></i>Batas Waktu Kembali
                    </p>
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-{{ $isOverdue ? 'red' : 'amber' }}-900 text-xl">
                            {{ $dispensasi->batas_waktu_kembali->format('H:i') }} <span class="text-sm font-semibold">WIB</span>
                        </p>
                        @if($dispensasi->status === 'keluar')
                            <span id="live-countdown" class="px-3 py-1.5 rounded-md text-xs font-bold {{ $isOverdue ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-white text-amber-700 border border-amber-200 shadow-sm' }}" data-deadline="{{ $dispensasi->batas_waktu_kembali->toISOString() }}">...</span>
                        @endif
                    </div>
                    @if($isOverdue)
                        @php $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali); @endphp
                        <p class="text-red-700 text-xs mt-3 font-semibold bg-red-100 w-fit px-3 py-1.5 rounded-md border border-red-200">
                            <i class="fas fa-exclamation-circle mr-1"></i> Terlambat <span id="late-minutes" class="text-sm">{{ $lateMinutes }}</span> menit
                        </p>
                    @endif
                </div>
            @endif
        </div>


        {{-- KONTAK DARURAT & WHATSAPP (100% dari Database) --}}
        @if($waLink)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                    <i class="fas fa-phone-alt text-green-600 mr-2"></i>Kontak Darurat
                </h3>

                @if($dispensasi->is_warned)
                    <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-purple-800 font-bold text-sm mb-1">Sudah Dihubungi</p>
                            <p class="text-purple-600 text-xs font-medium">
                                <i class="far fa-clock mr-1"></i>
                                {{ $dispensasi->warned_at ? $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') : '-' }} WIB
                            </p>
                            <p class="text-purple-500 text-[10px] mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                No. Telepon: <span class="font-mono font-semibold">{{ $dispensasi->siswa->no_telepon }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-green-700 text-[10px] font-semibold uppercase mb-1 tracking-wider">
                                    No. Telepon / WhatsApp
                                </p>
                                <p class="text-lg font-bold text-gray-900 font-mono">{{ $dispensasi->siswa->no_telepon }}</p>
                                <p class="text-green-600 text-xs mt-1 font-medium">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            </div>
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                               onclick="handleDetailWaContacted(event, {{ $dispensasi->id }}, '{{ $waLink }}')"
                               class="inline-flex flex-col items-center justify-center w-14 h-14 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex-shrink-0"
                               title="Hubungi via WhatsApp">
                                <i class="fab fa-whatsapp text-2xl"></i>
                                <span class="text-[9px] font-bold mt-0.5 tracking-wider">CHAT</span>
                            </a>
                        </div>
                        <p class="text-green-700 text-xs font-medium bg-green-100/50 p-2 rounded-md">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pesan diambil dari <strong>Template Database</strong>. Klik ikon WhatsApp untuk menghubungi.
                        </p>
                    </div>
                @endif
            </div>
        @elseif(!empty($dispensasi->siswa->no_telepon))
            {{-- Fallback: Nomor ada tapi template/link gagal --}}
            <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-amber-800 mb-3 flex items-center uppercase tracking-wider">
                    <i class="fas fa-exclamation-triangle text-amber-600 mr-2"></i>Template Belum Tersedia
                </h3>
                <p class="text-xs text-amber-700 mb-3">
                    Nomor telepon siswa tersedia (<strong>{{ $dispensasi->siswa->no_telepon }}</strong>),
                    namun template WhatsApp untuk context ini belum diaktifkan di database.
                </p>
                <a href="{{ route('admin.whatsapp-templates.index') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold">
                    <i class="fas fa-cog mr-1.5"></i>Ke Menu Template WhatsApp
                </a>
            </div>
        @endif

        {{-- AKSI UNTUK SATPAM --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="fas fa-qrcode text-red-600 mr-2"></i>Aksi Satpam
            </h3>
            <div class="space-y-3">
                @if($dispensasi->status === 'disetujui')
                    <a href="{{ route('satpam.scan', ['dispensasi' => $dispensasi->id]) }}" class="w-full inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-camera mr-2 text-base"></i>Scan QR Code untuk Keluar
                    </a>
                    <p class="text-xs text-gray-500 text-center font-medium"><i class="fas fa-info-circle mr-1"></i>Arahkan kamera ke QR Code siswa</p>
                @elseif($dispensasi->status === 'keluar')
                    <a href="{{ route('satpam.scan') }}" class="w-full inline-flex justify-center items-center px-5 py-3 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-camera mr-2 text-base"></i>Scan QR Code untuk Kembali
                    </a>
                    <p class="text-xs text-gray-500 text-center font-medium"><i class="fas fa-info-circle mr-1"></i>Scan QR Code yang sama saat siswa kembali</p>
                @elseif($dispensasi->status === 'selesai')
                    <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-lg text-center">
                        <div class="w-14 h-14 mx-auto rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <p class="text-emerald-800 font-bold text-sm">Dispensasi Selesai</p>
                        <p class="text-emerald-600 text-xs mt-1 font-medium">Siswa sudah kembali ke sekolah dengan selamat.</p>
                        @if($dispensasi->waktu_kembali_aktual)
                            <p class="text-emerald-700 text-[10px] mt-2 font-mono bg-emerald-100 w-fit mx-auto px-2 py-1 rounded-md">
                                <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: Data Siswa & Guru --}}
    <div class="space-y-5">
        {{-- Data Siswa --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="fas fa-user-graduate text-blue-600 mr-2"></i>Data Siswa
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Nama Lengkap</p>
                    <p class="font-bold text-gray-900">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">NIS / NISN</p>
                        <p class="font-mono font-semibold text-gray-800 text-sm">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Kelas</p>
                        <p class="font-bold text-gray-800 text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Jurusan</p>
                    <p class="text-gray-700 text-sm font-medium">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                @if(!empty($dispensasi->siswa->no_telepon))
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">No. Telepon</p>
                    <p class="font-mono font-semibold text-gray-800 text-sm">{{ $dispensasi->siswa->no_telepon }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Guru Piket --}}
        @if($dispensasi->guru)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="fas fa-user-tie text-amber-600 mr-2"></i>Guru Piket
            </h3>
            <div class="space-y-3">
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Nama Guru</p>
                    <p class="font-bold text-gray-900">{{ $dispensasi->guru->nama_lengkap }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-[10px] font-semibold uppercase mb-1 tracking-wider">Disetujui Pada</p>
                    <p class="text-gray-700 text-sm font-medium">{{ $dispensasi->updated_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline Status --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center uppercase tracking-wider">
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
        <a href="{{ route('satpam.dashboard') }}" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>

@push('styles')
<style>
    /* Mencegah teks panjang keluar dari kolom */
    .break-words {
        word-break: break-word;
        overflow-wrap: break-word;
    }
</style>
@endpush

@push('scripts')
<script>

function openPhotoModal(imageUrl, title) {
    const modal = document.getElementById('photoModal');
    const image = document.getElementById('photoModalImage');
    const titleEl = document.getElementById('photoModalTitle');

    image.src = imageUrl;
    titleEl.querySelector('span').textContent = title;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closePhotoModal(event) {
    if (event && event.target !== event.currentTarget && !event.target.closest('button')) return;

    const modal = document.getElementById('photoModal');
    document.getElementById('photoModalImage').removeAttribute('src');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scroll
}

// Close modal dengan tombol ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
    }
});


function handleDetailWaContacted(event, dispensasiId, waLink) {
    @if($dispensasi->status === 'keluar' && !$dispensasi->is_warned)
        event.preventDefault();
        fetch(`/satpam/dispensasi/${dispensasiId}/wa-contacted`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
            detailDeadlineEl.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-200');
            if (lateEl) lateEl.textContent = lateMin;
            if (!overdueAlerted) {
                overdueAlerted = true;
                Swal.fire({ icon: 'warning', title: 'Melewati Batas Waktu!', text: 'Siswa telah melewati batas waktu kembali. Segera hubungi via WhatsApp.', confirmButtonColor: '#dc2626' });
            }
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            detailDeadlineEl.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m ${secs}s` : `Sisa ${mins}m ${secs}s`;
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
