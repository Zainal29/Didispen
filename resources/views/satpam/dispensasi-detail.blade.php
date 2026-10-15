@extends('satpam.layouts.app')

@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Pengajuan')

@section('content')

{{-- Header dengan Nomor Surat & Status --}}
<div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 mb-5 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">Surat Dispensasi</span>
                @if($dispensasi->isOrganisasi())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="ti ti-users text-xs"></i>
                        <span>Dispensasi Organisasi: {{ $dispensasi->nama_organisasi ?? 'Organisasi' }}</span>
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        Rombongan ({{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="ti ti-user text-xs"></i>
                        <span>Dispensasi Individu</span>
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-bold font-mono tracking-tight text-slate-900">{{ $dispensasi->nomor_surat }}</h1>
            <p class="text-slate-500 text-xs mt-1.5 font-medium flex items-center">
                <i class="ti ti-calendar mr-1.5 text-rose-600"></i>
                Diajukan: {{ $dispensasi->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
            </p>
        </div>
        <div class="flex sm:flex-col items-center sm:items-end gap-2">
            @php
                $statusBadges = [
                    'menunggu' => ['bg-amber-50 text-amber-700 border-amber-200', 'Menunggu'],
                    'disetujui' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Disetujui'],
                    'ditolak' => ['bg-rose-50 text-rose-700 border-rose-200', 'Ditolak'],
                    'keluar' => ['bg-sky-50 text-sky-700 border-sky-200', 'Sedang Keluar'],
                    'selesai' => ['bg-slate-100 text-slate-700 border-slate-200', 'Selesai'],
                ];
                $badge = $statusBadges[$dispensasi->status] ?? $statusBadges['menunggu'];
                $isOverdue = $dispensasi->status === 'keluar' && $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            @endphp
            <span class="inline-block px-3 py-1 rounded-xl text-xs font-bold border {{ $badge[0] }}">
                {{ $badge[1] }}
            </span>
            @if($isOverdue)
                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-rose-600 text-white shadow-xs">
                    <i class="ti ti-alert-triangle mr-1"></i>TERLAMBAT
                </span>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- KOLOM KIRI: Detail Dispensasi --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Informasi Utama --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="ti ti-file-text text-rose-600 mr-2 text-lg"></i>Informasi Dispensasi
            </h3>

            {{-- Foto Verifikasi & Bukti --}}
            @php
                $hasFotoVerif = $dispensasi->foto_verifikasi && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_verifikasi);
                $hasFotoBukti = $dispensasi->foto_bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($dispensasi->foto_bukti);
            @endphp

            @if($dispensasi->status === 'selesai' && !$hasFotoVerif && !$hasFotoBukti)
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3 text-slate-600 text-xs">
                    <i class="ti ti-info-circle text-slate-400 text-base flex-shrink-0"></i>
                    <span>Foto verifikasi dan bukti kedatangan telah dihapus otomatis dari penyimpanan untuk efisiensi server karena dispensasi telah diselesaikan. Riwayat data tetap tersimpan aman.</span>
                </div>
            </div>
            @endif

            {{-- Foto Verifikasi (Jika Ada & File Fisik Tersedia) --}}
            @if($hasFotoVerif)
            <div class="bg-rose-50/40 border border-rose-100 rounded-xl p-4 mb-4">
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border border-rose-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_verifikasi) }}', 'Foto Verifikasi - {{ $dispensasi->siswa->nama_lengkap }}')">
                        <img src="{{ Storage::url($dispensasi->foto_verifikasi) }}" alt="Foto {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-rose-950 mb-1 flex flex-wrap items-center gap-2">
                            <i class="ti ti-camera mr-1 text-rose-600"></i>
                            <span class="break-words">Foto Verifikasi Siswa</span>
                            <span class="text-[10px] font-semibold text-rose-600 bg-rose-100/70 px-2 py-0.5 rounded-md whitespace-nowrap flex items-center gap-1">
                                <i class="ti ti-arrows-maximize"></i>Klik untuk memperbesar
                            </span>
                        </h4>
                        <p class="text-xs text-rose-800/80 mb-2 break-words">
                            Gunakan foto ini untuk memverifikasi identitas siswa secara visual sebelum melakukan scan QR Code.
                        </p>
                        <p class="text-[10px] text-slate-500 break-words flex items-center">
                            <i class="ti ti-info-circle mr-1 text-xs"></i> Foto akan dihapus otomatis setelah siswa kembali.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Foto Bukti Siswa (Jika Ada & File Fisik Tersedia) --}}
            @if($hasFotoBukti)
            <div class="bg-emerald-50/50 border border-emerald-200 rounded-xl p-4 mb-4">
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border border-emerald-200 flex-shrink-0 bg-white cursor-pointer hover:shadow-md transition-shadow" onclick="openPhotoModal('{{ Storage::url($dispensasi->foto_bukti) }}', 'Foto Bukti Kedatangan - {{ $dispensasi->siswa->nama_lengkap }}')">
                        <img src="{{ Storage::url($dispensasi->foto_bukti) }}" alt="Foto Bukti {{ $dispensasi->siswa->nama_lengkap }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-emerald-950 mb-1 flex flex-wrap items-center gap-2">
                            <i class="ti ti-photo mr-1 text-emerald-600"></i>
                            <span class="break-words">Foto Bukti Kedatangan</span>
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md whitespace-nowrap flex items-center gap-1">
                                <i class="ti ti-arrows-maximize"></i>Klik untuk memperbesar
                            </span>
                        </h4>
                        <p class="text-xs text-emerald-800/80 mb-2 break-words">
                            Foto ini diupload oleh siswa sebagai bukti kedatangan di lokasi tujuan.
                        </p>
                        @if($dispensasi->foto_bukti_uploaded_at)
                        <p class="text-[10px] text-emerald-600 break-words flex items-center">
                            <i class="ti ti-clock mr-1 text-xs"></i> Diupload: {{ $dispensasi->foto_bukti_uploaded_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- MODAL PREVIEW FOTO --}}
            <div id="photoModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-sm" onclick="closePhotoModal(event)">
                <div class="relative flex h-[calc(100dvh-1.5rem)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:h-[calc(100dvh-3rem)]" onclick="event.stopPropagation()">
                    {{-- Header Modal --}}
                    <div class="w-full bg-white rounded-t-2xl px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <h3 id="photoModalTitle" class="min-w-0 text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="ti ti-photo text-rose-600"></i>
                            <span class="truncate">Preview Foto</span>
                        </h3>
                        <button onclick="closePhotoModal()" class="w-10 h-10 min-h-[40px] flex-shrink-0 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors" aria-label="Tutup preview foto">
                            <i class="ti ti-x text-lg"></i>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="min-h-0 flex-1 w-full bg-slate-950 p-3 sm:p-5 flex items-center justify-center overflow-hidden">
                        <img id="photoModalImage" src="" alt="Preview" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-lg">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 shadow-2xs">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Kategori</p>
                    <p class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $dispensasi->kategori) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 shadow-2xs">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Lokasi / Tujuan</p>
                    <p class="font-bold text-slate-900">{{ $dispensasi->lokasi ?? $dispensasi->tujuan ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-3.5 bg-slate-50 rounded-xl p-3.5 border border-slate-200 shadow-2xs">
                <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Alasan</p>
                <p class="font-medium text-slate-800 leading-relaxed text-sm">{{ $dispensasi->alasan }}</p>
            </div>

            @if($dispensasi->dibuat_manual_oleh_guru)
                <div class="mt-3.5 bg-violet-50/60 border border-violet-200 rounded-xl p-3.5">
                    <p class="text-violet-700 text-[10px] font-bold uppercase tracking-wider mb-1 flex items-center"><i class="ti ti-user-shield mr-1"></i>Dibuat Manual oleh Guru Piket</p>
                    <p class="text-violet-900 text-sm font-medium">Dibuat langsung oleh {{ $dispensasi->guru?->nama_lengkap ?? 'Guru Piket' }} untuk siswa.</p>
                </div>
            @endif

            @php
                $dayOfWeek = $dispensasi->created_at ? $dispensasi->created_at->dayOfWeek : null;
                $waktuKeluar = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dayOfWeek);
                $jamMulai = str_contains($waktuKeluar, ' - ') ? explode(' - ', $waktuKeluar)[0] : ($waktuKeluar !== '-' ? $waktuKeluar : null);

                $waktuKembali = \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dayOfWeek);
                $jamSelesai = str_contains($waktuKembali, ' - ') ? explode(' - ', $waktuKembali)[1] : ($waktuKembali !== '-' ? $waktuKembali : null);
                if (!$jamSelesai && $dispensasi->batas_waktu_kembali) {
                    $jamSelesai = $dispensasi->batas_waktu_kembali->format('H:i');
                }
            @endphp

            {{-- Modul Waktu Terpadu (Jam Keluar & Batas Waktu Kembali) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mt-3.5">
                {{-- Jam Keluar --}}
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                    <div class="flex items-center justify-between text-slate-500 text-[10px] font-bold uppercase mb-1 tracking-wider">
                        <span>Jam Keluar</span>
                        <i class="ti ti-door-exit text-rose-500 text-sm"></i>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-slate-900 text-lg">{{ $jamMulai ? $jamMulai . ' WIB' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_keluar, $dayOfWeek) }}</p>
                        @if(str_contains((string)$dispensasi->jam_keluar, 'Jam Pelajaran'))
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-700">
                                {{ $dispensasi->jam_keluar }}
                            </span>
                        @endif
                    </div>
                    @if($dispensasi->waktu_keluar_aktual)
                        <p class="text-sky-700 text-xs mt-2 font-medium bg-sky-50 border border-sky-200 w-fit px-2 py-0.5 rounded-md flex items-center">
                            <i class="ti ti-circle-check mr-1 text-xs"></i>Aktual Keluar: {{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>

                {{-- Batas Kembali & Countdown Terpadu --}}
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 {{ $isOverdue ? 'border-rose-300 bg-rose-50/30' : '' }}">
                    <div class="flex items-center justify-between text-slate-500 text-[10px] font-bold uppercase mb-1 tracking-wider">
                        <span>Batas Waktu Kembali</span>
                        <i class="ti ti-door-enter {{ $isOverdue ? 'text-rose-500' : 'text-emerald-500' }} text-sm"></i>
                    </div>
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-slate-900 text-lg">{{ $jamSelesai ? $jamSelesai . ' WIB' : \App\Helpers\TimeHelper::getWaktuAktual($dispensasi->jam_kembali, $dayOfWeek) }}</p>
                            @if(str_contains((string)$dispensasi->jam_kembali, 'Jam Pelajaran'))
                                <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-700">
                                    {{ $dispensasi->jam_kembali }}
                                </span>
                            @endif
                        </div>
                        @if($dispensasi->batas_waktu_kembali && $dispensasi->status === 'keluar')
                            <span id="live-countdown" class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $isOverdue ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-white text-slate-700 border border-slate-200 shadow-2xs' }}" data-deadline="{{ $dispensasi->batas_waktu_kembali->toISOString() }}">...</span>
                        @endif
                    </div>
                    @if($dispensasi->waktu_kembali_aktual)
                        <p class="text-emerald-700 text-xs mt-2 font-medium bg-emerald-50 border border-emerald-200 w-fit px-2 py-0.5 rounded-md flex items-center">
                            <i class="ti ti-circle-check mr-1 text-xs"></i>Aktual Kembali: {{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->format('H:i') }} WIB
                        </p>
                    @elseif($isOverdue && $dispensasi->batas_waktu_kembali)
                        @php $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali); @endphp
                        <p class="text-rose-700 text-xs mt-2 font-semibold bg-rose-100/90 w-fit px-2.5 py-1 rounded-md border border-rose-200 flex items-center">
                            <i class="ti ti-alert-triangle mr-1 text-xs"></i> Terlambat <span id="late-minutes" class="text-xs font-bold ml-1 mr-1">{{ $lateMinutes }}</span> menit
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- KONTAK DARURAT & WHATSAPP --}}
        @if($waLink || $dispensasi->is_warned)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                    <i class="ti ti-brand-whatsapp text-emerald-600 mr-2 text-xl"></i>Kontak Darurat Siswa
                </h3>

                @if($dispensasi->is_warned)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                            <i class="ti ti-circle-check text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-slate-900 font-bold text-sm mb-0.5">Sudah Dihubungi Petugas</p>
                            <p class="text-slate-500 text-xs font-medium flex items-center">
                                <i class="ti ti-clock mr-1 text-xs text-purple-600"></i>
                                {{ $dispensasi->warned_at ? $dispensasi->warned_at->isoFormat('D MMMM Y, HH:mm') : '-' }} WIB
                            </p>
                            @if($dispensasi->siswa->no_telepon)
                            <p class="text-slate-600 text-[11px] mt-1 flex items-center">
                                <i class="ti ti-brand-whatsapp text-emerald-600 mr-1 text-sm"></i>
                                No. Telepon: <span class="font-mono font-semibold ml-1 text-slate-800">{{ $dispensasi->siswa->no_telepon }}</span>
                            </p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">
                                    No. Telepon / WhatsApp
                                </p>
                                <p class="text-lg font-bold text-slate-900 font-mono flex items-center">
                                    <i class="ti ti-brand-whatsapp text-emerald-600 text-xl mr-1.5 flex-shrink-0"></i>
                                    <span>{{ $dispensasi->siswa->no_telepon }}</span>
                                </p>
                                <p class="text-slate-500 text-xs mt-0.5 font-medium">{{ $dispensasi->siswa->nama_lengkap }}</p>
                            </div>
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                               onclick="handleDetailWaContacted(event, {{ $dispensasi->id }}, '{{ $waLink }}')"
                               class="inline-flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition-colors flex-shrink-0 font-bold text-xs"
                               title="Hubungi via WhatsApp">
                                <i class="ti ti-brand-whatsapp text-lg"></i>
                                <span>Hubungi WhatsApp</span>
                            </a>
                        </div>
                        <p class="text-slate-500 text-xs font-medium bg-white p-2.5 rounded-lg border border-slate-200/80 flex items-center">
                            <i class="ti ti-info-circle mr-1.5 text-xs text-slate-400"></i>
                            Pesan template darurat siap dikirimkan otomatis saat tombol di atas ditekan.
                        </p>
                    </div>
                @endif
            </div>
        @elseif(!empty($dispensasi->siswa->no_telepon))
            {{-- Fallback: Nomor ada tapi template/link gagal --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center uppercase tracking-wider">
                    <i class="ti ti-brand-whatsapp text-emerald-600 mr-2 text-base"></i>Kontak Siswa
                </h3>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-0.5">No. Telepon Siswa</p>
                        <p class="font-mono font-bold text-slate-900 text-sm flex items-center">
                            <i class="ti ti-brand-whatsapp text-emerald-600 text-base mr-1.5"></i>
                            {{ $dispensasi->siswa->no_telepon }}
                        </p>
                    </div>
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $dispensasi->siswa->no_telepon)) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors">
                        Chat WhatsApp
                    </a>
                </div>
            </div>
        @endif

        {{-- AKSI UNTUK SATPAM --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="ti ti-qrcode text-rose-600 mr-2 text-lg"></i>Aksi Satpam
            </h3>
            <div class="space-y-3">
                @if($dispensasi->status === 'disetujui')
                    <a href="{{ route('satpam.scan') }}" class="w-full inline-flex justify-center items-center px-5 py-3 min-h-[44px] rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-colors">
                        <i class="ti ti-camera mr-2 text-lg"></i>Scan QR Code untuk Keluar
                    </a>
                    <p class="text-xs text-slate-500 text-center font-medium flex items-center justify-center"><i class="ti ti-info-circle mr-1 text-xs"></i>Arahkan kamera ke QR Code siswa</p>
                @elseif($dispensasi->status === 'keluar')
                    <a href="{{ route('satpam.scan') }}" class="w-full inline-flex justify-center items-center px-5 py-3 min-h-[44px] rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-colors">
                        <i class="ti ti-camera mr-2 text-lg"></i>Scan QR Code untuk Kembali
                    </a>
                    <p class="text-xs text-slate-500 text-center font-medium flex items-center justify-center"><i class="ti ti-info-circle mr-1 text-xs"></i>Scan QR Code yang sama saat siswa kembali</p>
                @elseif($dispensasi->status === 'selesai')
                    <div class="p-5 bg-emerald-50/60 border border-emerald-200 rounded-xl text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3 shadow-2xs">
                            <i class="ti ti-circle-check text-3xl"></i>
                        </div>
                        <p class="text-emerald-900 font-bold text-sm">Dispensasi Selesai</p>
                        <p class="text-emerald-700 text-xs mt-1 font-medium">Siswa sudah kembali ke sekolah dengan selamat.</p>
                        @if($dispensasi->waktu_kembali_aktual)
                            <p class="text-emerald-700 text-[10px] mt-2 font-mono bg-emerald-100/70 w-fit mx-auto px-2 py-1 rounded-md">
                                <i class="ti ti-clock mr-1 text-xs"></i>{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB
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
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="ti ti-school text-rose-600 mr-2 text-lg"></i>Data Siswa
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Nama Lengkap</p>
                    <p class="font-bold text-slate-900">{{ $dispensasi->siswa->nama_lengkap }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">NIS / NISN</p>
                        <p class="font-mono font-semibold text-slate-800 text-sm">{{ $dispensasi->siswa->user->nis_nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Kelas</p>
                        <p class="font-bold text-slate-800 text-sm">{{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Jurusan</p>
                    <p class="text-slate-700 text-sm font-medium">{{ $dispensasi->siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</p>
                </div>
                @if(!empty($dispensasi->siswa->no_telepon))
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">No. Telepon / WhatsApp</p>
                    <div class="flex items-center justify-between gap-2 mt-0.5">
                        <p class="font-mono font-bold text-slate-800 text-sm flex items-center">
                            <i class="ti ti-brand-whatsapp text-emerald-600 text-base mr-1.5 flex-shrink-0"></i>
                            <span>{{ $dispensasi->siswa->no_telepon }}</span>
                        </p>
                        <!-- @if($waLink)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                               onclick="handleDetailWaContacted(event, {{ $dispensasi->id }}, '{{ $waLink }}')"
                               class="inline-flex items-center gap-1 px-2.5 py-1 min-h-[30px] rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition-colors"
                               title="Chat via WhatsApp">
                                <i class="ti ti-brand-whatsapp text-xs"></i>
                                <span>Chat</span>
                            </a>
                        @endif -->
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Jika Dispensasi Organisasi, Tampilkan Daftar Anggota Rombongan --}}
        @if($dispensasi->isOrganisasi())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center uppercase tracking-wider">
                    <i class="ti ti-users text-rose-600 mr-2 text-lg"></i>Anggota Rombongan
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $dispensasi->anggotaSiswa->count() + 1 }} Siswa
                </span>
            </div>
            <p class="text-xs text-slate-600 font-medium mb-3">
                Dispensasi untuk organisasi <strong class="text-slate-900">{{ $dispensasi->nama_organisasi }}</strong>
            </p>
            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-900 truncate">1. {{ $dispensasi->siswa->nama_lengkap }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $dispensasi->siswa->user?->nis_nip ?? '-' }} • {{ $dispensasi->siswa->kelas?->nama_kelas ?? '-' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex-shrink-0">
                        Ketua
                    </span>
                </div>
                @forelse($dispensasi->anggotaSiswa as $idx => $anggota)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 truncate">{{ $idx + 2 }}. {{ $anggota->nama_lengkap }}</p>
                            <p class="text-[10px] text-slate-500 font-mono">{{ $anggota->user?->nis_nip ?? '-' }} • {{ $anggota->kelas?->nama_kelas ?? '-' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-medium bg-white text-slate-600 border border-slate-200 flex-shrink-0">
                            Anggota
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 italic p-2">Tidak ada anggota tambahan.</p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Guru Pembina (Khusus Dispen Organisasi / jika data pembina tersedia) --}}
        @if($dispensasi->gurus && $dispensasi->gurus->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center uppercase tracking-wider">
                    <i class="ti ti-tie text-rose-600 mr-2 text-lg"></i>Guru Pembina
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $dispensasi->gurus->count() }} Guru
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mb-3">
                Guru Pembina penanggung jawab kegiatan:
            </p>
            <div class="space-y-2.5">
                @foreach($dispensasi->gurus as $pembina)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <p class="font-bold text-slate-900 text-sm leading-snug">{{ $pembina->nama_lengkap }}</p>
                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-1 flex-wrap">
                            @if($pembina->nip)
                                <span class="font-mono text-slate-600">NIP: {{ $pembina->nip }}</span>
                            @endif
                            @if($pembina->mata_pelajaran)
                                @if($pembina->nip)<span class="text-slate-300">•</span>@endif
                                <span>{{ $pembina->mata_pelajaran }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Guru Piket --}}
        @if($dispensasi->guru)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="ti ti-user-shield text-rose-600 mr-2 text-lg"></i>Guru Piket
            </h3>
            <div class="space-y-3">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Nama Guru</p>
                    <p class="font-bold text-slate-900">{{ $dispensasi->guru->nama_lengkap }}</p>
                </div>
                @if($dispensasi->status === 'ditolak')
                    @if($dispensasi->rejected_at)
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">Ditolak Pada</p>
                        <p class="text-rose-700 text-sm font-medium">{{ $dispensasi->rejected_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                    </div>
                    @endif
                @elseif(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                    @if($dispensasi->approved_at)
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mb-1 tracking-wider">{{ $dispensasi->dibuat_manual_oleh_guru ? 'Diterbitkan Pada' : 'Disetujui Pada' }}</p>
                        <p class="text-slate-700 text-sm font-medium">{{ $dispensasi->approved_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                    </div>
                    @endif
                @endif
            </div>
        </div>
        @endif

        {{-- Timeline Status --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center uppercase tracking-wider">
                <i class="ti ti-history text-rose-600 mr-2 text-lg"></i>Riwayat
            </h3>
            <div class="relative border-l-2 border-slate-200 ml-2.5 space-y-6 pb-2">
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-rose-500 border-2 border-white shadow-xs"></div>
                    <p class="text-xs font-bold text-slate-800">Pengajuan Dibuat</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->created_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @if($dispensasi->status === 'ditolak')
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-rose-500 border-2 border-white shadow-xs"></div>
                    <p class="text-xs font-bold text-slate-800">Ditolak Guru Piket</p>
                    @if($dispensasi->guru)
                        <p class="text-[11px] text-slate-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                    @endif
                    @if($dispensasi->rejected_at)
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->rejected_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    @endif
                    @if($dispensasi->catatan_admin)
                        <p class="text-xs text-rose-600 mt-0.5">Alasan: {{ $dispensasi->catatan_admin }}</p>
                    @endif
                </div>
                @elseif(in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai']))
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-xs"></div>
                    <p class="text-xs font-bold text-slate-800">{{ $dispensasi->dibuat_manual_oleh_guru ? 'Dibuat Manual & Disetujui' : 'Disetujui Guru Piket' }}</p>
                    @if($dispensasi->guru)
                        <p class="text-[11px] text-slate-600 mt-0.5">Oleh: {{ $dispensasi->guru->nama_lengkap }}</p>
                    @endif
                    @if($dispensasi->approved_at)
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $dispensasi->approved_at->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                    @endif
                </div>
                @endif
                @if($dispensasi->waktu_keluar_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-sky-500 border-2 border-white shadow-xs"></div>
                    <p class="text-xs font-bold text-slate-800">Konfirmasi Keluar (Scan)</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif
                @if($dispensasi->waktu_kembali_aktual)
                <div class="relative pl-6">
                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-xs"></div>
                    <p class="text-xs font-bold text-slate-800">Konfirmasi Kembali</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->isoFormat('D MMM Y, HH:mm') }} WIB</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <a href="{{ route('satpam.dashboard') }}" class="w-full inline-flex items-center justify-center px-4 py-3 min-h-[44px] rounded-xl text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-2xs transition-colors">
            <i class="ti ti-arrow-left mr-2 text-base"></i>Kembali ke Dashboard
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
            detailDeadlineEl.classList.add('bg-rose-100', 'text-rose-700', 'border', 'border-rose-200');
            if (lateEl) lateEl.textContent = lateMin;
            if (!overdueAlerted) {
                overdueAlerted = true;
                Swal.fire({ icon: 'warning', title: 'Melewati Batas Waktu!', text: 'Siswa telah melewati batas waktu kembali. Segera hubungi via WhatsApp.', confirmButtonColor: '#e11d48' });
            }
        } else {
            const totalMin = Math.floor(diffMs / 60000);
            const hrs = Math.floor(totalMin / 60);
            const mins = totalMin % 60;
            const secs = Math.floor((diffMs % 60000) / 1000);
            detailDeadlineEl.textContent = hrs > 0 ? `Sisa ${hrs}j ${mins}m ${secs}s` : `Sisa ${mins}m ${secs}s`;
            if (totalMin <= 5) {
                detailDeadlineEl.classList.remove('bg-white', 'text-amber-700', 'border-amber-200');
                detailDeadlineEl.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            }
        }
    }
    tickDetailCountdown();
    setInterval(tickDetailCountdown, 1000);
@endif
</script>
@endpush
@endsection
